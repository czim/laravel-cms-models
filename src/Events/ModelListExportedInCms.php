<?php
namespace Czim\CmsModels\Events;

/**
 * Class ModelListExportedInCms
 *
 * Whenever a model listing was exported by a CMS user.
 */
class ModelListExportedInCms
{

    /**
     * The model class for the exported data.
     *
     * @var string
     */
    public $modelClass;

    /**
     * The export strategy used.
     *
     * @var string
     */
    public $strategy;

    /**
     * @param string $modelClass
     * @param string $strategy
     */
    public function __construct($modelClass, $strategy)
    {
        $this->modelClass = $modelClass;
        $this->strategy   = $strategy;
    }

}
