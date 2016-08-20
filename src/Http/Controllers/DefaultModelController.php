<?php
namespace Czim\CmsModels\Http\Controllers;

use Czim\CmsModels\Http\Controllers\Traits\DefaultModelPagination;
use Czim\CmsModels\Http\Controllers\Traits\DefaultModelSorting;

class DefaultModelController extends BaseModelController
{
    use DefaultModelSorting,
        DefaultModelPagination;

    public function index()
    {
        $this->applySort()
             ->checkActivePage();

        // todo: filtering (session stored)


        $records = $this->modelRepository->paginate(
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
            'sortColumn'       => $this->getActualSort(),
            'sortDirection'    => $this->getActualSortDirection(),
            'pageSize'         => $this->getActualPageSize(),
            'pageSizeOptions'  => $this->getPageSizeOptions(),
        ]);
    }

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

    public function destroy($id)
    {

    }


    /**
     * Applies active sorting to model repository.
     *
     * @return $this
     */
    protected function applySort()
    {
        $this->checkActiveSort();

        $sort = $this->getActualSort();

        if ($sort) {
            $criteria = $this->getModelSortCriteria();
            if ($criteria) {
                $this->modelRepository->pushCriteria($criteria);
            }
        }

        return $this;
    }

}
