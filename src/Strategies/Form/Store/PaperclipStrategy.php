<?php
namespace Czim\CmsModels\Strategies\Form\Store;

use Czim\CmsModels\Contracts\ModelInformation\Data\Form\ModelFormFieldDataInterface;
use Czim\CmsModels\Exceptions\InvalidFileUploadedException;
use Czim\CmsModels\ModelInformation\Data\Form\ModelFormFieldData;
use Czim\CmsModels\Support\Strategies\Traits\UsesUploadModule;
use Czim\Paperclip\Attachment\Attachment;
use File;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;
use Symfony\Component\HttpFoundation\File\File as SymfonyFile;
use Validator;

class PaperclipStrategy extends DefaultStrategy
{
    use UsesUploadModule;

    /**
     * If file uploader was used, the uploaded record ID.
     *
     * @var int|null
     */
    protected $uploadedFileRecordId;

    /**
     * Adjusts or normalizes a value before storing it.
     *
     * @param mixed $value
     * @return mixed
     */
    protected function adjustValue($value)
    {
        $useUpload = $this->useFileUploader();

        // Normalize to an array if required
        if ( ! is_array($value)) {
            $value = [
                'keep'      => 0,
                'upload'    => $useUpload ? null : $value,
                'upload_id' => $useUpload ? $value : null,
            ];
        }

        // If the value is empty, use the stapler null value instead
        if (empty($value['upload'])) {
            // @codeCoverageIgnoreStart
            $value['upload'] = $this->getNullAttachmentHash();
            // @codeCoverageIgnoreEnd
        }

        return $value;
    }

    /**
     * @param Model  $model
     * @param string $source
     * @param mixed  $value
     * @throws InvalidFileUploadedException
     */
    protected function performStore(Model $model, $source, $value)
    {
        $value = $this->adjustValue($value);

        // If the keep flag is set, we don't touch the model
        if (array_get($value, 'keep')) {
            return;
        }

        // If we don't use the file uploader, we should trust the validation
        // performed on the field['upload'] input itself.
        if ( ! $this->useFileUploader()) {
            $this->uploadedFileRecordId = null;
            $model->{$source} = array_get($value, 'upload');
            return;
        }


        // If no ID is given, this should be treated as nullifying the field.
        // We can use the stapler null value from the upload field.
        if ( ! ($fileRecordId = array_get($value, 'upload_id'))) {
            $model->{$source} = array_get($value, 'upload');
            return;
        }

        $this->uploadedFileRecordId = $fileRecordId;

        // It should be verified that the uploaded record belongs to this user.
        // This is done using the upload module's session guard.
        if ( ! $this->checkFileUploadWithSessionGuard($fileRecordId)) {
            throw new RuntimeException(
                "Not allowed to use file record with ID #{$fileRecordId} for field '{$this->formFieldData->key}'"
            );
        }

        if ( ! ($fileRecord = $this->getUploadedFileRecordById($fileRecordId))) {
            throw new RuntimeException(
                "Failed to find file record with ID #{$fileRecordId} for field '{$this->formFieldData->key}'"
            );
        }

        if ( ! File::exists($fileRecord->path) || ! File::isReadable($fileRecord->path)) {
            throw new RuntimeException(
                "Failed to read file for record with ID #{$fileRecordId} for field '{$this->formFieldData->key}'"
                . " (path: '{$fileRecord->path}')"
            );
        }

        $file = new SymfonyFile($fileRecord->path);

        // Perform validation. This is required because validation may have been
        // spoofed or omitted during the AJAX upload of the file.
        $rules = $this->getFileValidationRules();

        if ( ! is_array($rules) && ! empty($rules) || count($rules)) {

            $validator = Validator::make(['file' => $file], ['file' => $rules]);

            if ($validator->fails()) {
                $messages = implode("\n", array_get($validator->getMessageBag()->toArray(), 'file', []));
                throw new InvalidFileUploadedException(
                    "File record with ID #{$fileRecordId} for field '{$this->formFieldData->key}'"
                    . " does not pass validation:\n " . $messages
                );
            }
        }

        $model->{$source} = $file;
    }

    /**
     * Performs finalizing/cleanup handling.
     *
     * After the model has been successfully stored, the uploaded file may be cleaned up.
     */
    public function finish()
    {
        if (null !== $this->uploadedFileRecordId) {
            $this->deleteUploadedFileRecordById($this->uploadedFileRecordId);
        }
    }

    /**
     * Returns the validation rules to apply to file uploads.
     *
     * These are relevant here for AJAX uploads, since they may not have
     * passed validation for the actual asynchronous upload.
     *
     * @return array|string
     */
    protected function getFileValidationRules()
    {
        return array_get($this->formFieldData->options, 'validation', []);
    }

    /**
     * Returns validation rules specific for the strategy.
     *
     * @param ModelFormFieldDataInterface|ModelFormFieldData $field
     * @return array|false|null     null to fall back to default rules.
     */
    protected function getStrategySpecificRules(ModelFormFieldDataInterface $field = null)
    {
        // Build up validation rules
        $fileRules = $this->getFileValidationRules();

        if ( ! is_array($fileRules)) {
            $fileRules = explode('|', $fileRules);
        }

        if ( ! in_array('file', $fileRules)) {
            $fileRules[] = 'file';
        }

        $keepRules   = ['boolean'];
        $fileIdRules = ['integer'];

        // Modify rules for required fields that may be either uploaded directly or asynchronously.
        if ($this->formFieldData->required && ! $this->formFieldData->translated) {
            $fileRules[]   = 'required_without_all:<field>.upload_id,<field>.keep';
            $keepRules[]   = 'required_without_all:<field>.upload,<field>.upload_id';
            $fileIdRules[] = 'required_without_all:<field>.upload,<field>.keep';
        } else {
            if ( ! in_array('nullable', $fileRules)) {
                $fileRules[] = 'nullable';
            }
            $keepRules[]   = 'nullable';
            $fileIdRules[] = 'nullable';
        }

        return [
            'keep'      => $keepRules,
            'upload'    => $fileRules,
            'upload_id' => $fileIdRules,
        ];
    }

    /**
     * Returns whether the file uploader model can and should be used.
     *
     * @return bool
     */
    protected function useFileUploader()
    {
        return ! array_get($this->formFieldData->options, 'no_ajax') && $this->isUploadModuleAvailable();
    }

    /**
     * Returns the hash value that clears the attachment.
     *
     * @return string
     */
    protected function getNullAttachmentHash()
    {
        return Attachment::NULL_ATTACHMENT;
    }

}

