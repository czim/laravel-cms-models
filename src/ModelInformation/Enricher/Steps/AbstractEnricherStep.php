<?php
namespace Czim\CmsModels\ModelInformation\Enricher\Steps;

use Czim\CmsModels\Contracts\ModelInformation\Data\ModelInformationInterface;
use Czim\CmsModels\Contracts\ModelInformation\Enricher\EnricherStepInterface;
use Czim\CmsModels\Contracts\ModelInformation\ModelInformationEnricherInterface;
use Czim\CmsModels\ModelInformation\Data\ModelAttributeData;
use Czim\CmsModels\ModelInformation\Data\ModelInformation;
use Czim\CmsModels\Support\Enums\AttributeCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

abstract class AbstractEnricherStep implements EnricherStepInterface
{

    /**
     * Parent enricher for this step.
     *
     * @var ModelInformationEnricherInterface
     */
    protected $enricher;

    /**
     * @var ModelInformationInterface|ModelInformation
     */
    protected $info;

    /**
     * All model information known (so far), before enrichment.
     *
     * @var Collection|ModelInformationInterface[]|ModelInformation[]|null
     */
    protected $allInfo;

    /**
     * @var Model
     */
    protected $model;

    /**
     * @param ModelInformationEnricherInterface $enricher
     */
    public function __construct(ModelInformationEnricherInterface $enricher)
    {
        $this->enricher = $enricher;
    }

    /**
     * Performs enrichment on model information.
     *
     * Optionally takes all model information known as context.
     *
     * @param ModelInformationInterface|ModelInformation                     $info
     * @param Collection|ModelInformationInterface[]|ModelInformation[]|null $allInformation
     * @return ModelInformationInterface|ModelInformation
     */
    public function enrich(ModelInformationInterface $info, $allInformation = null)
    {
        $this->info = $info;
        $class = $this->info->modelClass();
        $this->model = new $class;

        $this->allInfo = $allInformation;

        $this->performEnrichment();

        return $this->info;
    }

    /**
     * Performs enrichment.
     */
    abstract protected function performEnrichment();

    /**
     * Returns whether an attribute should be displayed if no user-defined list columns are configured.
     *
     * @param ModelAttributeData                         $attribute
     * @param ModelInformationInterface|ModelInformation $info
     * @return bool
     */
    protected function shouldAttributeBeDisplayedByDefault(ModelAttributeData $attribute, ModelInformationInterface $info)
    {
        if (in_array($attribute->type, [
            'text', 'longtext', 'mediumtext',
            'blob', 'longblob', 'mediumblob',
        ])) {
            return false;
        }

        // Hide active column if the model if activatable
        if ($info->list->activatable && $info->list->active_column == $attribute->name) {
            return false;
        }

        // Hide orderable position column if the model if orderable
        if ($info->list->orderable && $info->list->order_column == $attribute->name) {
            return false;
        }

        // Hide stapler fields other than the main field
        if (preg_match('#^(?<field>[^_]+)_(file_name|file_size|content_type|updated_at)$#', $attribute->name, $matches)) {
            if (array_has($info->attributes, $matches['field'])) {
                return $info->attributes[ $matches['field'] ]->cast !== AttributeCast::STAPLER_ATTACHMENT;
            }
        }

        return true;
    }

    /**
     * Normalizes a string representation for a relation method to the expected key name.
     *
     * @param string $key   key or relation method
     * @return string
     */
    protected function normalizeRelationName($key)
    {
        return camel_case($key);
    }

}
