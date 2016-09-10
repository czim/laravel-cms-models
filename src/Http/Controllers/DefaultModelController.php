<?php
namespace Czim\CmsModels\Http\Controllers;

use Czim\CmsModels\Http\Controllers\Traits\DefaultModelFiltering;
use Czim\CmsModels\Http\Controllers\Traits\DefaultModelPagination;
use Czim\CmsModels\Http\Controllers\Traits\DefaultModelScoping;
use Czim\CmsModels\Http\Controllers\Traits\DefaultModelSorting;

class DefaultModelController extends BaseModelController
{
    use DefaultModelFiltering,
        DefaultModelPagination,
        DefaultModelScoping,
        DefaultModelSorting;

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

        $query = $this->modelRepository->query();

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

}
