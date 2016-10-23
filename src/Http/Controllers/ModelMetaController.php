<?php
namespace Czim\CmsModels\Http\Controllers;

use Czim\CmsModels\Contracts\Data\ModelInformationInterface;
use Czim\CmsModels\Contracts\Repositories\ModelRepositoryInterface;
use Czim\CmsModels\Http\Requests\ModelMetaReferenceRequest;
use Czim\CmsModels\Support\Data\ModelInformation;
use Czim\CmsModels\View\Traits\ModifiesQueryForContext;
use Czim\CmsModels\View\Traits\ResolvesModelReference;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

/**
 * Class ModelMetaController
 *
 * Controller for meta functionality for CMS models pacakage.
 */
class ModelMetaController extends Controller
{
    use ResolvesModelReference,
        ModifiesQueryForContext;

    /**
     * Looks up model references by an optional search term.
     *
     * Useful for relation (autocomplete) select strategies.
     *
     * @param ModelMetaReferenceRequest $request
     * @return mixed
     */
    public function references(ModelMetaReferenceRequest $request)
    {
        $modelClass = $request->input('model');

        // Find out of the model is part of the CMS
        $info = $this->getCmsModelInformation($modelClass);

        $references = [];

        if ( ! $info) {
            // If not, read model information directly, applying reference strategy (if given)
            $model = $this->getModelInstance($modelClass);

            $records = $model->query()->get();

        } else {
            // If so, apply the default strategies and use the fallback strategy

            // Get model records, filtered as necessary
            $repository = $this->getModelRepository($info);

            /** @var Model[] $models */
            $models = $repository->query()->get();

            // todo: apply filters/searching

            // Get references for models, keyed by primary key
            foreach ($models as $model) {

                // todo: use request-determined reference strategy if given

                $references[ $model->getKey() ] = $this->getReferenceValue($model);
            }

        }

        $records = [];

        if ($request->ajax()) {
            return response()->json($references);
        }

        // todo figure out what to return for non-ajax requests
        // consider using optional response strategy (for both ajax and non-ajax..)
    }


    /**
     * Returns whether a given model class is part of the CMS.
     *
     * @param string $class
     * @return bool
     */
    protected function isCmsModel($class)
    {
        return false !== $this->getCmsModelInformation($class);
    }

    /**
     * Returns model information for model class, if available.
     *
     * @param string $class
     * @return ModelInformation|false
     */
    protected function getCmsModelInformation($class)
    {
        return $this->infoRepository->getByModelClass($class);
    }

    /**
     * Returns an Eloquent model instance for a model class.
     *
     * @param string $class
     * @return Model
     */
    protected function getModelInstance($class)
    {
        if ( ! class_exists($class) || ! is_a($class, Model::class, true)) {
            throw new InvalidArgumentException("{$class} is not an Eloquent model");
        }

        return new $class;
    }

    /**
     * Returns instance of a model repository for given model information.
     *
     * @param ModelInformationInterface $information
     * @return ModelRepositoryInterface
     */
    protected function getModelRepository(ModelInformationInterface $information)
    {
        $modelRepository = app(ModelRepositoryInterface::class, [ $information->modelClass() ]);

        // todo: apply repository context

        return $modelRepository;
    }

}
