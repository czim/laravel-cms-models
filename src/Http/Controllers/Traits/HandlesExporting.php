<?php
namespace Czim\CmsModels\Http\Controllers\Traits;

use Carbon\Carbon;
use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsModels\Contracts\Data\ModelInformationInterface;
use Czim\CmsModels\Contracts\Support\Exporting\ModelExporterInterface;
use Czim\CmsModels\Contracts\Support\Factories\ExportStrategyFactoryInterface;
use Czim\CmsModels\Http\Controllers\BaseModelController;
use Czim\CmsModels\Support\Data\ModelExportStrategyData;
use Czim\CmsModels\Support\Data\ModelInformation;

trait HandlesExporting
{

    /**
     * Returns list of export strategy keys that are availabe for the current user.
     *
     * @return string[]
     */
    protected function getAvailableExportStrategyKeys()
    {
        if (empty($this->getModelInformation()->export->strategies)) {
            return [];
        }

        $keys = array_keys($this->getModelInformation()->export->strategies);

        return array_filter($keys, [ $this, 'isExportStrategyAvailable']);
    }

    /**
     * Returns filename for an export download.
     *
     * @param string $strategy
     * @param string $extension
     * @return string
     */
    protected function getExportDownloadFilename($strategy, $extension)
    {
        return Carbon::now()->format('Y-m-d_H-i')
             . ' - ' . $this->getModelSlug()
             . '.' . ltrim($extension, '.');
    }

    /**
     * Returns whether a given strategy key corresponds to a usable export strategy.
     *
     * @param string $strategy
     * @return bool
     */
    protected function isExportStrategyAvailable($strategy)
    {
        if ( ! array_key_exists($strategy, $this->getModelInformation()->export->strategies)) {
            return false;
        }

        $strategyInfo = $this->getModelInformation()->export->strategies[ $strategy ];

        if (count($strategyInfo->permissions()) && ! $this->getCore()->auth()->can($strategyInfo->permissions())) {
            return false;
        }

        return true;
    }

    /**
     * Returns prepared exporter strategy instance for a given strategy string.
     *
     * @param string $strategy
     * @return ModelExporterInterface
     */
    protected function getExportStrategyInstance($strategy)
    {
        /** @var ModelExportStrategyData $strategyData */
        $strategyData = array_get($this->getModelInformation()->export->strategies, $strategy);

        $instance = $this->getExportStrategyFactory()->make($strategyData->strategy);

        if ($strategyData) {
            $instance->setStrategyData($strategyData);
        }

        return $instance;
    }

    /**
     * @return ExportStrategyFactoryInterface
     */
    protected function getExportStrategyFactory()
    {
        return app(ExportStrategyFactoryInterface::class);
    }

    /**
     * @return ModelInformation|ModelInformationInterface
     * @see BaseModelController::getModelInformation()
     */
    abstract protected function getModelInformation();

    /**
     * @return CoreInterface
     */
    abstract protected function getCore();

    /**
     * @return string
     */
    abstract protected function getModelSlug();

}
