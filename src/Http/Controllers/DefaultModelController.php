<?php
namespace Czim\CmsModels\Http\Controllers;

use Czim\CmsCore\Support\Enums\FlashLevel;
use Czim\CmsModels\Contracts\Repositories\ModelReferenceRepositoryInterface;
use Czim\CmsModels\Contracts\Support\Session\ModelListMemoryInterface;
use Czim\CmsModels\Events;
use Czim\CmsModels\Http\Requests\ActivateRequest;
use Czim\CmsModels\Http\Requests\ModelCreateRequest;
use Czim\CmsModels\Http\Requests\ModelUpdateRequest;
use Czim\CmsModels\Http\Requests\OrderUpdateRequest;
use Czim\CmsModels\Support\Data\ListParentData;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;

class DefaultModelController extends BaseModelController
{
    use Traits\ChecksModelDeletable,
        Traits\DefaultModelFiltering,
        Traits\DefaultModelListParentHandling,
        Traits\DefaultModelPagination,
        Traits\DefaultModelScoping,
        Traits\DefaultModelSorting,
        Traits\DeletesModel,
        Traits\HandlesActionStrategies,
        Traits\HandlesExporting,
        Traits\HandlesFormFields,
        Traits\HandlesFilterStrategies,
        Traits\HandlesFormFieldStrategies,
        Traits\HandlesListColumnStrategies,
        Traits\HandlesShowFieldStrategies,
        Traits\SetsModelActivateState,
        Traits\SetsModelOrderablePosition;

    /**
     * The key under which all general (not-field specific) errors are stored.
     *
     * @var string
     */
    const GENERAL_ERRORS_KEY = '__general__';

    /**
     * The form data key for differentiating between saving and saving & closing.
     *
     * @var string
     */
    const SAVE_AND_CLOSE_KEY = '__save_and_close__';

    /**
     * The form data key for the currently active tab on submit.
     *
     * @var string
     */
    const ACTIVE_TAB_PANE_KEY = '__active_tab__';

    /**
     * The form data key for the currently active translation locale.
     *
     * @var string
     */
    const ACTIVE_TRANSLATION_LOCALE_KEY = '__active_translation_locale__';

    /**
     * The session key postfix that stores the active tab pane.
     *
     * @var string
     */
    const ACTIVE_TAB_SESSION_KEY = 'active-tab';


    /**
     * @var ModelListMemoryInterface
     */
    protected $listMemory;


    /**
     * Returns listing of filtered, sorted records.
     *
     * @return mixed
     */
    public function index()
    {
        // For single-item display only, redirect to the relevant show/edit page
        if ($this->modelInformation->single) {
            return $this->returnViewForSingleDisplay();
        }

        $this->resetStateBeforeIndexAction()
             ->checkListParents()
             ->applyListParentContext()
             ->checkActiveSort()
             ->checkScope()
             ->checkFilters()
             ->checkActivePage();

        $totalCount  = $this->getTotalCount();
        $scopeCounts = $this->getScopeCounts();

        $this->applySort()
             ->applyScope($this->modelRepository);

        $query = $this->getModelRepository()->query();

        $this->applyFilter($query)
             ->applyListParentToQuery($query);

        $records = $query->paginate($this->getActualPageSize(), ['*'], 'page', $this->getActualPage());

        // Check and whether page is out of bounds and adjust
        if ($this->getActualPage() > $records->lastPage()) {
            $records = $query->paginate($this->getActualPageSize(), ['*'], 'page', $records->lastPage());
        }

        $currentCount = method_exists($records, 'total') ? $records->total() : 0;

        return view($this->getIndexView(), [
            'moduleKey'           => $this->moduleKey,
            'routePrefix'         => $this->routePrefix,
            'permissionPrefix'    => $this->permissionPrefix,
            'model'               => $this->modelInformation,
            'records'             => $records,
            'recordReferences'    => $this->getReferenceRepository()->getReferencesForModels($records->items()),
            'totalCount'          => $totalCount,
            'currentCount'        => $currentCount,
            'listStrategies'      => $this->getListColumnStrategyInstances(),
            'sortColumn'          => $this->getActualSort(),
            'sortDirection'       => $this->getActualSortDirection(),
            'pageSize'            => $this->getActualPageSize(),
            'pageSizeOptions'     => $this->getPageSizeOptions(),
            'filterStrategies'    => $this->renderedListFilterStrategies($this->getActiveFilters()),
            'activeScope'         => $this->getActiveScope(),
            'scopeCounts'         => $scopeCounts,
            'unconditionalDelete' => $this->isUnconditionallyDeletable(),
            'defaultRowAction'    => $this->getDefaultRowActionInstance(),
            'hasActiveListParent' => (bool) $this->listParentRelation,
            'listParents'         => $this->listParents,
            'topListParentOnly'   => $this->showsTopParentsOnly(),
            'draggableOrderable'  => $this->isListOrderDraggable($totalCount, $currentCount),
            'availableExportKeys' => $this->getAvailableExportStrategyKeys(),
        ]);
    }

    /**
     * Displays a single record.
     *
     * @param mixed $id
     * @return mixed
     */
    public function show($id)
    {
        $record = $this->modelRepository->findOrFail($id);

        $this->checkListParents(false);

        return view($this->getShowView(), [
            'moduleKey'           => $this->moduleKey,
            'routePrefix'         => $this->routePrefix,
            'permissionPrefix'    => $this->permissionPrefix,
            'model'               => $this->modelInformation,
            'record'              => $record,
            'recordReference'     => $this->getReferenceRepository()->getReferenceForModel($record),
            'fieldStrategies'     => $this->renderedShowFieldStrategies($record),
            'hasActiveListParent' => (bool) $this->listParentRelation,
            'topListParentOnly'   => $this->showsTopParentsOnly(),
            'listParents'         => $this->listParents,
        ]);
    }

    /**
     * Displays form to create a new record.
     *
     * @return mixed
     */
    public function create()
    {
        $record = $this->getNewModelInstance();

        $fields = array_only(
            $this->modelInformation->form->fields,
            $this->getRelevantFormFieldKeys(true)
        );

        $this->checkListParents(false);

        $values = $this->getFormFieldValuesFromModel($record, array_keys($fields));
        $errors = $this->getNormalizedFormFieldErrors();

        $renderedFields = $this->renderedFormFieldStrategies($record, $fields, $values, $errors);

        return view($this->getCreateView(), [
            'moduleKey'           => $this->moduleKey,
            'routePrefix'         => $this->routePrefix,
            'permissionPrefix'    => $this->permissionPrefix,
            'model'               => $this->modelInformation,
            'record'              => $record,
            'recordReference'     => null,
            'creating'            => true,
            'fields'              => $fields,
            'fieldStrategies'     => $renderedFields,
            'values'              => $values,
            'fieldErrors'         => $errors,
            'activeTab'           => $this->getActiveTab(),
            'errorsPerTab'        => $this->getErrorCountsPerTabPane(),
            'hasActiveListParent' => (bool) $this->listParentRelation,
            'topListParentOnly'   => $this->showsTopParentsOnly(),
            'listParents'         => $this->listParents,
        ]);
    }

    /**
     * Processes submitted form to create a new record.
     *
     * @return mixed
     * @event ModelCreatedInCms
     */
    public function store()
    {
        /** @var FormRequest $request */
        $request = app($this->getCreateRequestClass());
        $record  = $this->getNewModelInstance();

        $data = $request->only($this->getRelevantFormFieldKeys(true));

        if ( ! $this->storeFormFieldValuesForModel($record, $data)) {
            return redirect()->back()
                ->withInput()
                ->withErrors([
                    static::GENERAL_ERRORS_KEY => [ $this->getGeneralStoreFailureError() ],
                ]);
        }

        $this->storeActiveTab($request->input(static::ACTIVE_TAB_PANE_KEY));

        cms_flash(
            cms_trans(
                'models.store.success-message-create',
                [ 'record' => $this->getSimpleRecordReference($record->getKey(), $record) ]
            ),
            FlashLevel::SUCCESS
        );

        event( new Events\ModelCreatedInCms($record) );

        if ($request->input(static::SAVE_AND_CLOSE_KEY)) {
            return redirect()->route("{$this->routePrefix}.index");
        }

        return redirect()->route("{$this->routePrefix}.edit", [ $record->getKey() ]);
    }

    /**
     * Displays form to edit an existing record.
     *
     * @param mixed $id
     * @return mixed
     */
    public function edit($id)
    {
        $record = $this->modelRepository->findOrFail($id);

        $fields = array_only(
            $this->modelInformation->form->fields,
            $this->getRelevantFormFieldKeys()
        );

        $this->checkListParents(false);

        $values = $this->getFormFieldValuesFromModel($record, array_keys($fields));
        $errors = $this->getNormalizedFormFieldErrors();

        $renderedFields = $this->renderedFormFieldStrategies($record, $fields, $values, $errors);

        return view($this->getEditView(), [
            'moduleKey'           => $this->moduleKey,
            'routePrefix'         => $this->routePrefix,
            'permissionPrefix'    => $this->permissionPrefix,
            'model'               => $this->modelInformation,
            'record'              => $record,
            'recordReference'     => $this->getReferenceRepository()->getReferenceForModel($record),
            'creating'            => false,
            'fields'              => $fields,
            'fieldStrategies'     => $renderedFields,
            'values'              => $values,
            'fieldErrors'         => $errors,
            'activeTab'           => $this->getActiveTab(),
            'errorsPerTab'        => $this->getErrorCountsPerTabPane(),
            'hasActiveListParent' => (bool) $this->listParentRelation,
            'topListParentOnly'   => $this->showsTopParentsOnly(),
            'listParents'         => $this->listParents,
        ]);
    }

    /**
     * Processes a submitted for to edit an existing record.
     *
     * @param mixed $id
     * @return mixed
     * @event ModelUpdatedInCms
     */
    public function update($id)
    {
        /** @var FormRequest $request */
        $request = app($this->getUpdateRequestClass());
        $record  = $this->modelRepository->findOrFail($id);

        $data = $request->only($this->getRelevantFormFieldKeys());

        if ( ! $this->storeFormFieldValuesForModel($record, $data)) {
            return redirect()->back()
                ->withInput()
                ->withErrors([
                    static::GENERAL_ERRORS_KEY => [ $this->getGeneralStoreFailureError() ],
                ]);
        }

        $this->storeActiveTab($request->input(static::ACTIVE_TAB_PANE_KEY));

        cms_flash(
            cms_trans(
                'models.store.success-message-edit',
                [ 'record' => $this->getSimpleRecordReference($id, $record) ]
            ),
            FlashLevel::SUCCESS
        );

        event( new Events\ModelUpdatedInCms($record) );

        if ($request->input(static::SAVE_AND_CLOSE_KEY)) {
            return redirect()->route("{$this->routePrefix}.index");
        }

        return redirect()->route("{$this->routePrefix}.edit", [ $record->getKey() ]);
    }

    /**
     * Deletes a record, if allowed.
     *
     * @param mixed $id
     * @return mixed
     * @event DeletingModelInCms
     * @event ModelDeletedInCms
     */
    public function destroy($id)
    {
        $record = $this->modelRepository->findOrFail($id);

        if ( ! $this->isModelDeletable($record)) {
            return $this->failureResponse(
                $this->getLastUnmetDeleteConditionMessage()
            );
        }

        event( new Events\DeletingModelInCms($record) );

        if ( ! $this->deleteModel($record)) {
            return $this->failureResponse(
                cms_trans('models.delete.failure.unknown')
            );
        }

        event( new Events\ModelDeletedInCms($this->getModelInformation()->modelClass(), $id) );

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
            ]);
        }

        cms_flash(
            cms_trans(
                'models.delete.success-message',
                [ 'record' => $this->getSimpleRecordReference($id, $record) ]
            ),
            FlashLevel::SUCCESS
        );

        return redirect()->back();
    }

    /**
     * Checks whether a model is deletable.
     *
     * @param mixed $id
     * @return mixed
     */
    public function deletable($id)
    {
        $record = $this->modelRepository->findOrFail($id);

        if ( ! $this->isModelDeletable($record)) {
            return $this->failureResponse(
                $this->getLastUnmetDeleteConditionMessage()
            );
        }

        return $this->successResponse();
    }

    /**
     * Applies posted filters.
     *
     * @return mixed
     */
    public function filter()
    {
        $this->updateFilters()
             ->checkActivePage();

        $previousUrl = app('url')->previous();
        $previousUrl = $this->removePageQueryFromUrl($previousUrl);

        return redirect()->to($previousUrl);
    }

    /**
     * Activates/enables a record.
     *
     * @param ActivateRequest $request
     * @param int             $id
     * @return mixed
     * @event ModelActivatedInCms
     * @event ModelDeactivatedInCms
     */
    public function activate(ActivateRequest $request, $id)
    {
        $record    = $this->modelRepository->findOrFail($id);
        $success   = false;
        $result    = null;
        $activated = $request->get('activate');

        if ($this->getModelInformation()->list->activatable) {
            $success = true;
            $result  = $this->changeModelActiveState($record, $activated);
        }

        if ($activated) {
            event( new Events\ModelActivatedInCms($record) );
        } else {
            event( new Events\ModelDeactivatedInCms($record) );
        }

        if (request()->ajax()) {
            return response()->json([ 'success' => $success, 'active' => $result ]);
        }

        if ($success && config('cms-models.notifications.flash.activate')) {
            cms_flash(
                cms_trans(
                    'models.store.success-message-' . ($result ? 'activate' : 'deactivate'),
                    [ 'record' => $this->getSimpleRecordReference($id, $record) ]
                ),
                FlashLevel::SUCCESS
            );
        }

        return redirect()->back();
    }

    /**
     * Changes orderable position for a record.
     *
     * @param OrderUpdateRequest $request
     * @param int                $id
     * @return mixed
     * @event ModelPositionUpdatedInCms
     */
    public function position(OrderUpdateRequest $request, $id)
    {
        $record  = $this->modelRepository->findOrFail($id);
        $success = false;
        $result  = null;

        if ($this->getModelInformation()->list->orderable) {
            $success = true;
            $result  = $this->changeModelOrderablePosition($record, $request->get('position'));
        }

        if ($success && config('cms-models.notifications.flash.position')) {
            cms_flash(
                cms_trans(
                    'models.store.success-message-position',
                    [ 'record' => $this->getSimpleRecordReference($id, $record) ]
                ),
                FlashLevel::SUCCESS
            );
        }

        event( new Events\ModelPositionUpdatedInCms($record) );

        if (request()->ajax()) {
            return response()->json([ 'success' => $success, 'position' => $result ]);
        }

        return redirect()->back();
    }

    /**
     * Exports the current listing with a given strategy.
     *
     * @param string $strategy
     * @return false|mixed
     */
    public function export($strategy)
    {
        // Check if the strategy is available and allowed to be used
        if ( ! $this->isExportStrategyAvailable($strategy)) {
            abort(403, "Not possible or allowed to perform export '{$strategy}'");
        }

        // Prepare the strategy instance
        $exporter = $this->getExportStrategyInstance($strategy);
        $filename = $this->getExportDownloadFilename($strategy, $exporter->extension());

        $this->checkListParents()
             ->checkActiveSort(false)
             ->checkScope(false)
             ->checkFilters()
             ->checkActivePage(false)
             ->applySort()
             ->applyScope($this->modelRepository);

        $query = $this->getModelRepository()->query();

        $this->applyFilter($query)
             ->applyListParentToQuery($query);

        $download = $exporter->download($query, $filename);

        if (false === $download) {
            abort(500, "Fail to export model listing for strategy '{$strategy}'");
        }

        event( new Events\ModelListExportedInCms($this->modelInformation->modelClass(), $strategy) );

        return $download;
    }


    /**
     * Resets the controller's state before handling the index action.
     *
     * @return $this
     */
    protected function resetStateBeforeIndexAction()
    {
        $this->activeSort           = null;
        $this->activeSortDescending = null;
        $this->activeScope          = null;
        $this->filters              = [];
        $this->activePage           = null;
        $this->activePageSize       = null;
        $this->resetActivePage      = false;

        return $this;
    }

    /**
     * Removes the page query parameter from a full URL.
     *
     * @param string $url
     * @return string
     */
    protected function removePageQueryFromUrl($url)
    {
        $parts = parse_url($url);

        $query = preg_replace('#page=\d+&?#i', '', array_get($parts, 'query', ''));

        return array_get($parts, 'path') . ($query ? '?' . $query : null);
    }

    /**
     * Returns total count of all models.
     *
     * @return int
     */
    protected function getTotalCount()
    {
        $query = $this->modelRepository->query();

        $this->applyListParentToQuery($query);

        return $query->count();
    }

    /**
     * Returns new, unpersisted model instance.
     *
     * @return Model
     */
    protected function getNewModelInstance()
    {
        $modelClass = $this->modelInformation->modelClass();

        return new $modelClass;
    }

    /**
     * Returns whether, given the current circumstances, the list may be ordered by dragging rows.
     *
     * @param int  $totalCount
     * @param int  $currentCount
     * @return bool
     */
    protected function isListOrderDraggable($totalCount, $currentCount)
    {
        $info = $this->getModelInformation();

        if ( ! $info->list->orderable) {
            return false;
        }

        $showTopParentsOnly = $this->showsTopParentsOnly();
        $scopedRelation     = $info->list->order_scope_relation;

        if ($showTopParentsOnly || $this->hasActiveListParent()) {

            // Check if the listify scope relation matches on the actively scoped list parent relation

            if ( ! $scopedRelation) {
                return false;
            }

            if ($showTopParentsOnly) {

                if ($scopedRelation != $info->list->default_top_relation) {
                    return false;
                }

            } else {
                /** @var ListParentData $parent */
                $parent = head($this->listParents);

                if ($scopedRelation != $parent->relation) {
                    return false;
                }
            }

        } elseif ($scopedRelation) {
            // If there is a listify relation scope and the list is not scoped at all, never draggable
            return false;
        }

        $hasScope   = (bool) $this->getActiveScope();
        $hasFilters = ! empty($this->getActiveFilters());

        return  $info->list->getOrderableColumn() === $this->getActualSort()
            &&  (   $totalCount == $currentCount
                ||  ( ! $hasScope  && ! $hasFilters)
                );
    }

    /**
     * Returns the form request FQN for model updating.
     *
     * @return mixed
     */
    protected function getUpdateRequestClass()
    {
        return array_get($this->modelInformation->meta->form_requests, 'update', ModelUpdateRequest::class);
    }

    /**
     * Returns the form request FQN for model creation.
     *
     * @return string
     */
    protected function getCreateRequestClass()
    {
        return array_get($this->modelInformation->meta->form_requests, 'create', ModelCreateRequest::class);
    }

    /**
     * Returns a create/edit view for a single-item only model record setup.
     *
     * @return mixed
     */
    protected function returnViewForSingleDisplay()
    {
        $record = $this->modelRepository->first();

        if ($record) {
            return $this->edit($record->getKey());
        }

        return $this->create();
    }

    /**
     * @return string
     */
    protected function getGeneralStoreFailureError()
    {
        return cms_trans('models.store.general-error');
    }

    /**
     * Returns the view key for the index page.
     *
     * @return string
     */
    protected function getIndexView()
    {
        return array_get($this->modelInformation->meta->views, 'index', config('cms-models.views.index'));
    }

    /**
     * Returns the view key for the show page.
     *
     * @return string
     */
    protected function getShowView()
    {
        return array_get($this->modelInformation->meta->views, 'show', config('cms-models.views.show'));
    }

    /**
     * Returns the view key for the create page.
     *
     * @return string
     */
    protected function getCreateView()
    {
        return array_get($this->modelInformation->meta->views, 'create', config('cms-models.views.create'));
    }

    /**
     * Returns the view key for the edit page.
     *
     * @return string
     */
    protected function getEditView()
    {
        return array_get($this->modelInformation->meta->views, 'edit', config('cms-models.views.edit'));
    }

    /**
     * Returns standard failure response.
     *
     * @param string|null $error
     * @return mixed
     */
    protected function failureResponse($error = null)
    {
        if (request()->ajax()) {
            return response()->json([
                'success' => false,
                'error'   => $error,
            ]);
        }

        return redirect()->back()->withErrors([
            'general' => $error,
        ]);
    }
    /**
     * Returns standard simple success response.
     *
     * Redirects back if not ajax.
     *
     * @return mixed
     */
    protected function successResponse()
    {
        if (request()->ajax()) {
            return response()->json([
                'success' => true,
            ]);
        }

        return redirect()->back();
    }

    /**
     * Returns a simple reference.
     *
     * @param mixed      $key
     * @param Model|null $record
     * @return string
     */
    protected function getSimpleRecordReference($key, Model $record = null)
    {
        return ucfirst($this->modelInformation->label())
             . ' #' . $key;
    }

    /**
     * @return ModelListMemoryInterface
     */
    protected function getListMemory()
    {
        if (null !== $this->listMemory) {
            return $this->listMemory;
        }

        /** @var ModelListMemoryInterface $memory */
        $memory = app(ModelListMemoryInterface::class);

        $memory->setContext($this->getModelSessionKey());

        return $memory;
    }

    /**
     * Returns session key to use for storing information about the model (listing).
     *
     * This is used as the 'context' by the list memory.
     *
     * @param string|null $modelSlug    defaults to current model
     * @return string
     */
    protected function getModelSessionKey($modelSlug = null)
    {
        $modelSlug = (null === $modelSlug) ? $this->getModelSlug() : $modelSlug;

        return $this->core->config('session.prefix')
             . 'model:' . $modelSlug;
    }

    /**
     * Returns the active edit form tab pane key, if it is set.
     *
     * @param bool $pull    if true, pulls the value from the session
     * @return null|string
     */
    protected function getActiveTab($pull = true)
    {
        $key = $this->getModelSessionKey() . ':' . static::ACTIVE_TAB_SESSION_KEY;

        if ($pull) {
            return session()->pull($key);
        }

        return session()->get($key);
    }

    /**
     * Stores the currently active edit form tab pane key.
     *
     * @param string|null $tab
     * @return $this
     */
    protected function storeActiveTab($tab)
    {
        $key = $this->getModelSessionKey() . ':' . static::ACTIVE_TAB_SESSION_KEY;

        if (null === $tab) {
            session()->forget($key);
        } else {
            session()->put($key, $tab);
        }


        return $this;
    }

    /**
     * @return ModelReferenceRepositoryInterface
     */
    protected function getReferenceRepository()
    {
        return app(ModelReferenceRepositoryInterface::class);
    }

}
