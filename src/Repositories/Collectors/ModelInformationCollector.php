<?php
namespace Czim\CmsModels\Repositories\Collectors;

use Czim\CmsModels\Analyzer\ModelAnalyzer;
use Czim\CmsModels\Contracts\Data\ModelInformationInterface;
use Czim\CmsModels\Contracts\Repositories\Collectors\ModelInformationCollectorInterface;
use Czim\CmsModels\Contracts\Support\ModuleHelperInterface;
use Czim\CmsModels\Support\Data\ModelAttributeData;
use Czim\CmsModels\Support\Data\ModelInformation;
use Czim\CmsModels\Support\Data\ModelListColumnData;
use Czim\CmsModels\Support\Enums\AttributeCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class ModelInformationCollector implements ModelInformationCollectorInterface
{

    /**
     * @var ModuleHelperInterface
     */
    protected $moduleHelper;

    /**
     * @var ModelAnalyzer
     */
    protected $modelAnalyzer;

    /**
     * @var Collection|ModelInformationInterface[]|ModelInformation[]
     */
    protected $information;

    /**
     * @var string[]
     */
    protected $modelClasses;


    /**
     * @param ModuleHelperInterface $moduleHelper
     * @param ModelAnalyzer         $modelAnalyzer
     */
    public function __construct(ModuleHelperInterface $moduleHelper, ModelAnalyzer $modelAnalyzer)
    {
        $this->moduleHelper  = $moduleHelper;
        $this->modelAnalyzer = $modelAnalyzer;
    }


    /**
     * Collects and returns information about models.
     *
     * @return Collection|ModelInformationInterface[]
     */
    public function collect()
    {
        $this->information = new Collection;

        $this->modelClasses = $this->getModelsToCollect();

        $this->collectRawModels()
             ->collectCmsModels()
             ->enrichModelInformation();

        return $this->information;
    }

    /**
     * Collects information about config-defined app model classes.
     *
     * @return $this
     */
    protected function collectRawModels()
    {
        foreach ($this->modelClasses as $class) {

            $key = $this->moduleHelper->moduleKeyForModel($class);

            $this->information->put($key, $this->modelAnalyzer->analyze($class));
        }

        return $this;
    }

    /**
     * Collects information from dedicated CMS model information classes.
     *
     * @return $this
     */
    protected function collectCmsModels()
    {
        // todo
        // read information from special CMS model sources

        return $this;
    }

    /**
     * Enriches collected model information, extrapolating from available data.
     *
     * @return $this
     */
    protected function enrichModelInformation()
    {
        foreach ($this->information as $key => $info) {
            $this->enrichSingleModelInformationSet($info);
        }

        return $this;
    }

    /**
     * Enriches the information for a single collected model.
     *
     * @param ModelInformationInterface|ModelInformation $info
     */
    protected function enrichSingleModelInformationSet(ModelInformationInterface $info)
    {
        /** @var Model $model */
        $class = $info->modelClass();
        $model = new $class;

        // Fill list references if they are empty
        if ( ! count($info->list->columns)) {
            $columns = [];

            foreach ($info->attributes as $attribute) {
                if ($attribute->hidden) {
                    continue;
                }

                $columns[$attribute->name] = $this->makeModelListColumnDataForAttributeData($attribute, $info);
            }

            $info->list->columns = $columns;
        }

        // Default sorting order
        if ($info->timestamps) {
            $info->list->default_sort = $info->timestamp_created;
        } elseif ($info->incrementing) {
            $info->list->default_sort = $model->getKeyName();
        }
    }

    /**
     * @param ModelAttributeData                         $attribute
     * @param ModelInformationInterface|ModelInformation $info
     * @return ModelListColumnData
     */
    protected function makeModelListColumnDataForAttributeData(ModelAttributeData $attribute, ModelInformationInterface $info)
    {
        $primaryIncrementing = $attribute->name === 'id' && $info->incrementing;

        $sortable = (   ! $attribute->translated
                    &&  ( $attribute->isNumeric() || in_array($attribute->cast, [
                                AttributeCast::BOOLEAN,
                                AttributeCast::DATE,
                                AttributeCast::STRING,
                            ])
                        )
                    );

        $sortDirection = (  $primaryIncrementing
                        ||  in_array($attribute->cast, [ AttributeCast::BOOLEAN, AttributeCast::DATE ])
                        )
            ? 'desc' : 'asc';


        return new ModelListColumnData([
            'source'         => $attribute->name,
            'strategy'       => $attribute->strategy_list ?: $attribute->strategy,
            'label'          => snake_case($attribute->name, ' '),
            'style'          => $primaryIncrementing ? 'primary-id' : null,
            'editable'       => $attribute->fillable,
            'sortable'       => $sortable,
            'sort_direction' => $sortDirection,
        ]);
    }


    /**
     * Returns a list of model FQNs for which to collect information.
     *
     * @return string[]
     */
    protected function getModelsToCollect()
    {
        return config('cms-models.models', []);
    }

    /**
     * Returns module key for a given model FQN.
     *
     * @param string $class
     * @return string
     */
    protected function getModuleKeyForModelClass($class)
    {
        return app('cms-models-modelinfo')->moduleKey($class);
    }

}
