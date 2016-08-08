<?php
namespace Czim\CmsModels\Repositories\Collectors;

use Czim\CmsModels\Contracts\Data\ModelInformationInterface;
use Czim\CmsModels\Contracts\Repositories\Collectors\ModelInformationCollectorInterface;
use Illuminate\Support\Collection;

class ModelInformationCollector implements ModelInformationCollectorInterface
{

    /**
     * Collects and returns information about models.
     *
     * @return Collection|ModelInformationInterface[]
     */
    public function collect()
    {
        // todo - replace test content
        return new Collection([
            new \Czim\CmsModels\Support\Data\ModelInformation([
                'original_model'      => 'Test\\Model\\Here',
                'verbose_name'        => 'Test',
                'verbose_name_plural' => 'Tests',
            ])
        ]);
    }

}
