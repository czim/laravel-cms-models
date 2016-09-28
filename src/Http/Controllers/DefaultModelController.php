<?php
namespace Czim\CmsModels\Http\Controllers;

use Czim\CmsModels\Http\Controllers\Traits\ChecksModelDeletable;
use Czim\CmsModels\Http\Controllers\Traits\SetsModelActivateState;
use Czim\CmsModels\Http\Controllers\Traits\DefaultModelFiltering;
use Czim\CmsModels\Http\Controllers\Traits\DefaultModelPagination;
use Czim\CmsModels\Http\Controllers\Traits\DefaultModelScoping;
use Czim\CmsModels\Http\Controllers\Traits\DefaultModelSorting;
use Czim\CmsModels\Http\Controllers\Traits\SetsModelOrderablePosition;
use Czim\CmsModels\Http\Requests\ActivateRequest;
use Czim\CmsModels\Http\Requests\OrderUpdateRequest;

class DefaultModelController extends BaseModelController
{
    use ChecksModelDeletable,
        DefaultModelFiltering,
        DefaultModelPagination,
        DefaultModelScoping,
        DefaultModelSorting,
        SetsModelActivateState,
        SetsModelOrderablePosition;


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
            'moduleKey'        => $this->moduleKey,
            'routePrefix'      => $this->routePrefix,
            'permissionPrefix' => $this->permissionPrefix,
            'model'            => $this->modelInformation,
            'records'          => $records,
            'totalCount'       => $totalCount,
            'sortColumn'       => $this->getActualSort(),
            'sortDirection'    => $this->getActualSortDirection(),
            'pageSize'         => $this->getActualPageSize(),
            'pageSizeOptions'  => $this->getPageSizeOptions(),
            'filters'          => $this->getActiveFilters(),
            'activeScope'      => $this->getActiveScope(),
            'scopeCounts'      => $scopeCounts,
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

    }

    public function store()
    {

    }

    public function edit($id)
    {

    }

    public function update($id)
    {

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

        if ( ! $record->delete()) {
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
        $record = $this->modelRepository->findOrFail($id);
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
        $record = $this->modelRepository->findOrFail($id);
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
     * @param $error
     * @return mixed
     */
    protected function failureResponse($error)
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
}
