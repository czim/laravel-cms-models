<?php
namespace Czim\CmsModels\Console\Commands;

use Czim\CmsModels\Contracts\Repositories\ModelInformationRepositoryInterface;
use Illuminate\Console\Command;

class ClearModelInformationCache extends Command
{

    protected $signature = 'cms:models:clear';

    protected $description = 'Clears the model information cache';


    /**
     * Execute the console command.
     *
     * @param ModelInformationRepositoryInterface $repository
     */
    public function handle(ModelInformationRepositoryInterface $repository)
    {
        $repository->clearCache();

        $this->info('Cleared model information cache.');
    }

}
