<?php
namespace Czim\CmsModels\Repositories\Collectors;

use Czim\CmsModels\Contracts\Data\ModelInformationInterface;
use Czim\CmsModels\Contracts\Repositories\Collectors\ModelInformationEnricherInterface;
use Czim\CmsModels\Support\Data\ModelAttributeData;
use Czim\CmsModels\Support\Data\ModelInformation;
use Czim\CmsModels\Support\Data\ModelListColumnData;
use Czim\CmsModels\Support\Data\ModelListFilterData;
use Czim\CmsModels\Support\Enums\AttributeCast;
use Illuminate\Database\Eloquent\Model;
use UnexpectedValueException;

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

        $this->enrichListInformation();

        return $information;
    }

    /**
     * @return $this
     */
    protected function enrichListInformation()
    {
        /** @var Model $model */
        $class = $this->info->modelClass();
        $model = new $class;

        if ( ! count($this->info->list->columns)) {
            // Fill list references if they are empty
            $columns = [];

            foreach ($this->info->attributes as $attribute) {
                if ($attribute->hidden) {
                    continue;
                }

                $columns[ $attribute->name ] = $this->makeModelListColumnDataForAttributeData($attribute, $this->info);
            }

            $this->info->list->columns = $columns;

        } else {
            // Check filled columns and enrich them as required
            $columns = [];

            foreach ($this->info->list->columns as $key => $column) {

                if ( ! isset($this->info->attributes[ $key ])) {
                    throw new UnexpectedValueException(
                        "Unenriched list column set with non-attribute key; make sure full column data is provided"
                    );
                }

                $attributeColumnInfo = $this->makeModelListColumnDataForAttributeData($this->info->attributes[ $key ], $this->info);

                $attributeColumnInfo->merge($column);

                $columns[ $key ] = $attributeColumnInfo;
            }

            $this->info->list->columns = $columns;
        }



        // Default sorting order
        if ($this->info->timestamps) {
            $this->info->list->default_sort = $this->info->timestamp_created;
        } elseif ($this->info->incrementing) {
            $this->info->list->default_sort = $model->getKeyName();
        }


        if ( ! count($this->info->list->filters)) {
            // Set default filters if they are empty
            $filters = [];

            foreach ($this->info->attributes as $attribute) {
                if ($attribute->hidden) {
                    continue;
                }

                $filterData = $this->makeModelListFilterDataForAttributeData($attribute, $this->info);

                if ( ! $filterData) {
                    continue;
                }

                $filters[$attribute->name] = $filterData;
            }

            $this->info->list->filters = $filters;
        } else {
            // Check set filters and enrich them as required
            $filters = [];

            foreach ($this->info->list->filters as $key => $filter) {

                // If the filter information is fully provided, do not try to enrich
                if ($filter->strategy && $filter->target) {
                    continue;
                }

                if ( ! isset($this->info->attributes[ $key ])) {
                    throw new UnexpectedValueException(
                        "Unenriched list filter set with non-attribute key; make sure full filter data is provided ({$key})"
                    );
                }

                $attributeFilterInfo = $this->makeModelListFilterDataForAttributeData($this->info->attributes[ $key ], $this->info);

                if (false === $attributeFilterInfo) {
                    throw new UnexpectedValueException(
                        "Unenriched list filter set for uninterpretable attribute for filter data; make sure full filter data is provided ({$key})"
                    );
                }

                $attributeFilterInfo->merge($filter);

                $filters[ $key ] = $attributeFilterInfo;
            }

            $this->info->list->filters = $filters;
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

        $sortable = (
                ! $attribute->translated
            &&  ( $attribute->isNumeric() || in_array($attribute->cast, [
                    AttributeCast::BOOLEAN,
                    AttributeCast::DATE,
                    AttributeCast::STRING,
                ])
            )
        );

        $sortDirection = 'asc';
        if (    $primaryIncrementing
            ||  in_array($attribute->cast, [ AttributeCast::BOOLEAN, AttributeCast::DATE ])
        ) {
            $sortDirection = 'desc';
        }

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
     * @param ModelAttributeData                         $attribute
     * @param ModelInformationInterface|ModelInformation $info
     * @return ModelListFilterData|false
     */
    protected function makeModelListFilterDataForAttributeData(ModelAttributeData $attribute, ModelInformationInterface $info)
    {
        $strategy = false;
        $options  = [];

        if ($attribute->cast === AttributeCast::BOOLEAN) {

            $strategy = 'DropdownBoolean';

        } elseif ($attribute->type === 'enum') {

            $strategy = 'DropdownEnum';
            $options  = $attribute->values;

        } elseif ($attribute->cast === AttributeCast::STRING) {

            $strategy = 'BasicString';
        }

        if ( ! $strategy) {
            return false;
        }

        return new ModelListFilterData([
            'source'   => $attribute->name,
            'target'   => $attribute->name,
            'strategy' => $strategy,
            'values'   => $options,
        ]);
    }
}
