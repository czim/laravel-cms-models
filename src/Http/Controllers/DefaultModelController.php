<?php
namespace Czim\CmsModels\Http\Controllers;

use Czim\CmsModels\Http\Requests\ActivateRequest;
use Czim\CmsModels\Http\Requests\OrderUpdateRequest;

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

    public function create()
    {
        $class = $this->getModelInformation()->modelClass();

        $fieldKeys = $this->getRelevantFormFieldKeys();

        return view(config('cms-models.views.edit'), [
            'moduleKey'        => $this->moduleKey,
            'routePrefix'      => $this->routePrefix,
            'permissionPrefix' => $this->permissionPrefix,
            'model'            => $this->modelInformation,
            'record'           => new $class,
            'creating'         => true,
        ]);
    }

    public function store()
    {

    }

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

    public function update($id)
    {
        $record = $this->modelRepository->findOrFail($id);

        // todo: redirect back to list or to edit page?
        return redirect()->back();
    }

    /**
     * Deletes a model, if allowed.
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

        // todo flash

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

        if ($success) {
            // todo flash
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

        if (request()->ajax()) {
            return response()->json([ 'success' => $success, 'position' => $result ]);
        }

        if ($success) {
            // todo flash
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

}
