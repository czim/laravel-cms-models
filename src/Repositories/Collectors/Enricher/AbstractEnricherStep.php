<?php
namespace Czim\CmsModels\Repositories\Collectors\Enricher;

use Czim\CmsModels\Contracts\Data\ModelInformationInterface;
use Czim\CmsModels\Contracts\Repositories\Collectors\EnricherStepInterface;
use Czim\CmsModels\Support\Data\ModelAttributeData;
use Czim\CmsModels\Support\Data\ModelInformation;
use Czim\CmsModels\Support\Enums\AttributeFormStrategy;
use Illuminate\Database\Eloquent\Model;

abstract class AbstractEnricherStep implements EnricherStepInterface
{

    /**
     * @var ModelInformationInterface|ModelInformation
     */
    protected $info;

    /**
     * @var Model
     */
    protected $model;

    /**
     * Performs enrichment on model information.
     *
     * @param ModelInformationInterface|ModelInformation $info
     * @return ModelInformationInterface|ModelInformation
     */
    public function enrich(ModelInformationInterface $info)
    {
        $this->info = $info;
        $class = $this->info->modelClass();
        $this->model = new $class;

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

        // Hide stapler fields other than the main field
        if (preg_match('#^(?<field>[^_]+)_(file_name|file_size|content_type|updated_at)$#', $attribute->name, $matches)) {
            if (array_has($info->attributes, $matches['field'])) {
                $strategy = $info->attributes[ $matches['field'] ]->strategy_list ?: $info->attributes[ $matches['field'] ]->strategy;
                return ! in_array($strategy, $this->getStaplerStrategies());
            }
        }

        return true;
    }

    /**
     * Returns (list) strategies that are associated with stapler fields.
     *
     * @return string[]
     */
    protected function getStaplerStrategies()
    {
        return [
            AttributeFormStrategy::ATTACHMENT_STAPLER_IMAGE,
            AttributeFormStrategy::ATTACHMENT_STAPLER_FILE,
        ];
    }

}
