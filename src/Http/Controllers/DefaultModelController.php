<?php
namespace Czim\CmsModels\Http\Controllers;

class DefaultModelController extends Controller
{

    public function index()
    {
        $records = new \Illuminate\Support\Collection;

        return view(config('cms-models.views.index'), [
            'moduleKey'        => $this->moduleKey,
            'routePrefix'      => $this->routePrefix,
            'permissionPrefix' => $this->permissionPrefix,
            'model'            => $this->modelInformation,
            'records'          => $records,
        ]);
    }

}
