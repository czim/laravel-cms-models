<?php
namespace Czim\CmsModels\Contracts\ModelInformation;

use Czim\CmsModels\Contracts\ModelInformation\Data\ModelInformationInterface;
use Illuminate\Support\Collection;

interface ModelInformationCollectorInterface
{

    /**
     * Collects and returns information about models.
     *
     * @return Collection|ModelInformationInterface[]
     */
    public function collect();

}
