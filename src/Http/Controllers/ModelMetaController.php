<?php
namespace Czim\CmsModels\Http\Controllers;

use Czim\CmsCore\Contracts\Auth\AuthenticatorInterface;
use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsModels\Contracts\Data\ModelInformationInterface;
use Czim\CmsModels\Contracts\Repositories\ModelInformationRepositoryInterface;
use Czim\CmsModels\Contracts\Repositories\ModelReferenceRepositoryInterface;
use Czim\CmsModels\Contracts\Routing\RouteHelperInterface;
use Czim\CmsModels\Contracts\Support\MetaReferenceDataProviderInterface;
use Czim\CmsModels\Http\Requests\ModelMetaReferenceRequest;
use Czim\CmsModels\Support\Data\ModelInformation;
use Czim\CmsModels\Support\Data\Strategies\ModelMetaReference;
use Czim\CmsModels\View\Traits\GetsNestedRelations;
use Czim\CmsModels\View\Traits\ResolvesSourceStrategies;

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
     * @var MetaReferenceDataProviderInterface
     */
    protected $referenceDataProvider;


    /**
     * @param CoreInterface                       $core
     * @param AuthenticatorInterface              $auth
     * @param RouteHelperInterface                $routeHelper
     * @param ModelInformationRepositoryInterface $infoRepository
     * @param ModelReferenceRepositoryInterface   $referenceRepository
     * @param MetaReferenceDataProviderInterface  $referenceDataProvider
     */
    public function __construct(
        CoreInterface $core,
        AuthenticatorInterface $auth,
        RouteHelperInterface $routeHelper,
        ModelInformationRepositoryInterface $infoRepository,
        ModelReferenceRepositoryInterface $referenceRepository,
        MetaReferenceDataProviderInterface $referenceDataProvider
    ) {
        parent::__construct($core, $auth, $routeHelper, $infoRepository);

        $this->referenceRepository   = $referenceRepository;
        $this->referenceDataProvider = $referenceDataProvider;
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

        $data = $this->referenceDataProvider->getForInformationByType($info, $type, $key);

        if ( ! $data) {
            abort(404, "Could not retrieve reference data for {$type}, key: {$key}");
        }

        return $data;
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
