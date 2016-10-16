<?php
namespace Czim\CmsModels\Http\Controllers;

use Czim\CmsCore\Support\Enums\FlashLevel;
use Czim\CmsModels\Http\Requests\ActivateRequest;
use Czim\CmsModels\Http\Requests\ModelCreateRequest;
use Czim\CmsModels\Http\Requests\ModelUpdateRequest;
use Czim\CmsModels\Http\Requests\OrderUpdateRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;

class DefaultModelController extends BaseModelController
{
    use Traits\ChecksModelDeletable,
        Traits\DefaultModelFiltering,
        Traits\DefaultModelPagination,
        Traits\DefaultModelScoping,
        Traits\DefaultModelSorting,
        Traits\DeletesModel,
        Traits\HandlesFormFields,
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
     * Returns listing of filtered, sorted records.
     *
     * @return mixed
     */
    public function index()
    {
        $this->checkActiveSort()
             ->checkScope()
             ->checkFilters()
             ->checkActivePage();

        $totalCount  = $this->getTotalCount();
        $scopeCounts = $this->getScopeCounts();

        $this->applySort()
             ->applyScope($this->modelRepository);

        $query = $this->getModelRepository()->query();

        $this->applyFilter($query);

        $records = $query->paginate(
            $this->getActualPageSize(),
            ['*'],
            'page',
            $this->getActualPage()
        );

        return view(config('cms-models.views.index'), [
            'moduleKey'           => $this->moduleKey,
            'routePrefix'         => $this->routePrefix,
            'permissionPrefix'    => $this->permissionPrefix,
            'model'               => $this->modelInformation,
            'records'             => $records,
            'totalCount'          => $totalCount,
            'sortColumn'          => $this->getActualSort(),
            'sortDirection'       => $this->getActualSortDirection(),
            'pageSize'            => $this->getActualPageSize(),
            'pageSizeOptions'     => $this->getPageSizeOptions(),
            'filters'             => $this->getActiveFilters(),
            'activeScope'         => $this->getActiveScope(),
            'scopeCounts'         => $scopeCounts,
            'unconditionalDelete' => $this->isUnconditionallyDeletable(),
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

        return view('cms::blank.index', [
            'record' => $record,
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
            $this->getRelevantFormFieldKeys()
        );

        $values = $this->getFormFieldValuesFromModel($record, array_keys($fields));

        return view(config('cms-models.views.edit'), [
            'moduleKey'        => $this->moduleKey,
            'routePrefix'      => $this->routePrefix,
            'permissionPrefix' => $this->permissionPrefix,
            'model'            => $this->modelInformation,
            'record'           => $record,
            'creating'         => true,
            'fields'           => $fields,
            'values'           => $values,
        ]);
    }

    /**
     * Processes submitted form to create a new record.
     *
     * @return mixed
     */
    public function store()
    {
        /** @var FormRequest $request */
        $request = app($this->getCreateRequestClass());

        $record = $this->getNewModelInstance();

        $data = $request->only($this->getRelevantFormFieldKeys(true));

        if ( ! $this->storeFormFieldValuesForModel($record, $data)) {
            return redirect()->back()
                ->withInput()
                ->withErrors([
                    static::GENERAL_ERRORS_KEY => [ $this->getGeneralStoreFailureError() ],
                ]);
        }

        cms_flash(
            cms_trans(
                'models.store.success-message-create',
                [ 'record' => $this->getSimpleRecordReference($record->getKey(), $record) ]
            ),
            FlashLevel::SUCCESS
        );

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

        $values = $this->getFormFieldValuesFromModel($record, array_keys($fields));

        return view(config('cms-models.views.edit'), [
            'moduleKey'        => $this->moduleKey,
            'routePrefix'      => $this->routePrefix,
            'permissionPrefix' => $this->permissionPrefix,
            'model'            => $this->modelInformation,
            'record'           => $record,
            'creating'         => false,
            'fields'           => $fields,
            'values'           => $values,
        ]);
    }

    /**
     * Processes a submitted for to edit an existing record.
     *
     * @param mixed $id
     * @return mixed
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

        cms_flash(
            cms_trans(
                'models.store.success-message-edit',
                [ 'record' => $this->getSimpleRecordReference($id, $record) ]
            ),
            FlashLevel::SUCCESS
        );

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
     */
    public function destroy($id)
    {
        $record = $this->modelRepository->findOrFail($id);

        if ( ! $this->isModelDeletable($record)) {
            return $this->failureResponse(
                $this->getLastUnmetDeleteConditionMessage()
            );
        }

        if ( ! $this->deleteModel($record)) {
            return $this->failureResponse(
                cms_trans('models.delete.failure.unknown')
            );
        }

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
     */
    public function activate(ActivateRequest $request, $id)
    {
        $record  = $this->modelRepository->findOrFail($id);
        $success = false;
        $result  = null;

        if ($this->getModelInformation()->list->activatable) {
            $success = true;
            $result  = $this->changeModelActiveState($record, $request->get('activate'));
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

        if (request()->ajax()) {
            return response()->json([ 'success' => $success, 'position' => $result ]);
        }

        return redirect()->back();
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
        return $this->modelRepository->count();
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
     * @return string
     */
    protected function getGeneralStoreFailureError()
    {
        return cms_trans('models.store.general-error');
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

}
