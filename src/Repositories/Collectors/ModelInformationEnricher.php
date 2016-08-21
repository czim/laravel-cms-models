<?php
namespace Czim\CmsModels\Repositories\Collectors;

use Czim\CmsModels\Contracts\Data\ModelInformationInterface;
use Czim\CmsModels\Contracts\Repositories\Collectors\ModelInformationEnricherInterface;
use Czim\CmsModels\Support\Data\ModelAttributeData;
use Czim\CmsModels\Support\Data\ModelInformation;
use Czim\CmsModels\Support\Data\ModelListColumnData;
use Czim\CmsModels\Support\Enums\AttributeCast;
use Illuminate\Database\Eloquent\Model;

class ModelInformationEnricher implements ModelInformationEnricherInterface
{

    /**
     * @var ModelInformationInterface|ModelInformation
     */
    protected $info;

    /**
     * @param ModelInformationInterface|ModelInformation $information
     * @return ModelInformationInterface|ModelInformation
     */
    public function enrich(ModelInformationInterface $information)
    {
        $this->info = $information;

        $this->enrichtListInformation();

        return $information;
    }

    /**
     * @return $this
     */
    protected function enrichtListInformation()
    {
        /** @var Model $model */
        $class = $this->info->modelClass();
        $model = new $class;

        // Fill list references if they are empty
        if ( ! count($this->info->list->columns)) {
            $columns = [];

            foreach ($this->info->attributes as $attribute) {
                if ($attribute->hidden) {
                    continue;
                }

                $columns[$attribute->name] = $this->makeModelListColumnDataForAttributeData($attribute, $this->info);
            }

            $this->info->list->columns = $columns;
        }

        // Default sorting order
        if ($this->info->timestamps) {
            $this->info->list->default_sort = $this->info->timestamp_created;
        } elseif ($this->info->incrementing) {
            $this->info->list->default_sort = $model->getKeyName();
        }

        return $this;
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

}
