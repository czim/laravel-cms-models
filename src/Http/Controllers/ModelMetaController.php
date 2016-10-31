<?php
namespace Czim\CmsModels\Http\Controllers;

use Czim\CmsCore\Contracts\Auth\AuthenticatorInterface;
use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsModels\Contracts\Data\ModelInformationInterface;
use Czim\CmsModels\Contracts\Repositories\ModelInformationRepositoryInterface;
use Czim\CmsModels\Contracts\Repositories\ModelReferenceRepositoryInterface;
use Czim\CmsModels\Contracts\Routing\RouteHelperInterface;
use Czim\CmsModels\Http\Requests\ModelMetaReferenceRequest;
use Czim\CmsModels\Support\Data\ModelInformation;
use Czim\CmsModels\Support\Data\Strategies\ModelMetaReference;
use Czim\CmsModels\View\Traits\GetsNestedRelations;
use Czim\CmsModels\View\Traits\ResolvesSourceStrategies;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use UnexpectedValueException;

/**
 * Class ModelMetaController
 *
 * Controller for meta functionality for CMS models pacakage.
 */
class ModelMetaController extends Controller
{
    use ResolvesSourceStrategies,
        GetsNestedRelations;


    /**
     * @var ModelReferenceRepositoryInterface
     */
    protected $referenceRepository;


    /**
     * @param CoreInterface                       $core
     * @param AuthenticatorInterface              $auth
     * @param RouteHelperInterface                $routeHelper
     * @param ModelInformationRepositoryInterface $infoRepository
     * @param ModelReferenceRepositoryInterface   $referenceRepository
     */
    public function __construct(
        CoreInterface $core,
        AuthenticatorInterface $auth,
        RouteHelperInterface $routeHelper,
        ModelInformationRepositoryInterface $infoRepository,
        ModelReferenceRepositoryInterface $referenceRepository
    ) {
        parent::__construct($core, $auth, $routeHelper, $infoRepository);

        $this->referenceRepository = $referenceRepository;
    }


    /**
     * Looks up model references by an optional search term.
     *
     * Targets model information (such as a form field strategy definition) to
     * get the details for looking up references.
     *
     * Useful for relation (autocomplete) select strategies.
     *
     * @param ModelMetaReferenceRequest $request
     * @return mixed
     */
    public function references(ModelMetaReferenceRequest $request)
    {
        $modelClass = $request->input('model');
        $type       = $request->input('type');
        $key        = $request->input('key');

        $referenceData = $this->getModelMetaReferenceData($modelClass, $type, $key);

        if ( ! $referenceData) {
            abort(404, "Could not determine reference for {$modelClass} (type: {$type}, key: {$key})");
        }

        $references = $this->referenceRepository->getReferencesForModelMetaReference(
            $referenceData,
            $request->input('search')
        );

        $references = $this->formatReferenceOutput($references);

        if ($request->ajax()) {
            return response()->json($references);
        }

        // todo figure out what to return for non-ajax requests
        // consider using optional response strategy (for both ajax and non-ajax..)
    }

    /**
     * Formats the references key-value pairs for output.
     *
     * @param array $references
     * @return array    not associative, with key, reference pair arrays for each model
     */
    protected function formatReferenceOutput(array $references)
    {
        return array_map(
            function($key, $reference) {
                return compact('key', 'reference');
            },
            array_keys($references),
            $references
        );
    }


    /**
     * @param $modelClass
     * @param $type
     * @param $key
     * @return ModelMetaReference|false
     */
    protected function getModelMetaReferenceData($modelClass, $type, $key)
    {
        // Find out of the model is part of the CMS
        $info = $this->getCmsModelInformation($modelClass);

        // Model information is required, since it is used to determine the returned
        // reference strategy and source to use.
        if ( ! $info) {
            abort(404, "{$modelClass} is not a CMS model");
        }

        // Find the reference information for type and key specified
        switch ($type) {

            case 'form.field':
                return $this->getInformationReferenceDataForFormField($info, $key);

            // Default omitted on purpose
        }

        return abort(404, "Unknown reference type {$type}");
    }

    /**
     * @param ModelInformationInterface|ModelInformation $info
     * @param string                                     $key
     * @return ModelMetaReference|false
     */
    protected function getInformationReferenceDataForFormField(ModelInformationInterface $info, $key)
    {
        if ( ! array_key_exists($key, $info->form->fields)) {
            return false;
        }

        $data = $info->form->fields[ $key ];

        // Determine the target class, if not set in options
        if ( ! ($targetModelClass = array_get($data->options(), 'model'))) {
            $targetModelClass = get_class(
                $this->determineTargetModelFromSource($info->modelClass(), $data->source())
            );
        }

        return new ModelMetaReference([
            'model'            => $targetModelClass,
            'strategy'         => array_get($data->options(), 'strategy'),
            'source'           => array_get($data->options(), 'source'),
            'target'           => array_get($data->options(), 'target'),
            'context_strategy' => array_get($data->options(), 'context_strategy'),
            'parameters'       => array_get($data->options(), 'parameters', []),
            'sort_direction'   => array_get($data->options(), 'sort_direction'),
        ]);
    }

    /**
     * Resolves and returns model instance for a given source on a (CMS) model.
     *
     * @param string $modelClass
     * @param string $source
     * @return Model
     */
    protected function determineTargetModelFromSource($modelClass, $source)
    {
        if ( ! is_a($modelClass, Model::class, true)) {
            throw new UnexpectedValueException("{$modelClass} is not an Eloquent model");
        }

        $model = new $modelClass;

        if ( ! ($relation = $this->getNestedRelation($model, $source))) {
            $relation = $this->resolveModelSource($model, $source);
        }

        if ( ! ($relation instanceof Relation)) {
            throw new UnexpectedValueException(
                "Source {$source} on {$modelClass} does not resolve to an Eloquent relation instance"
            );
        }

        /** @var Relation $relation */
        return $relation->getRelated();
    }

    /**
     * Returns model information for model class, if available.
     *
     * @param string $class
     * @return ModelInformationInterface|ModelInformation|false
     */
    protected function getCmsModelInformation($class)
    {
        return $this->infoRepository->getByModelClass($class);
    }

}
