<?php
namespace Czim\CmsModels\Console\Commands;

use Czim\CmsModels\Contracts\Repositories\ModelInformationRepositoryInterface;
use Illuminate\Console\Command;

class CacheModelInformation extends Command
{

    protected $signature = 'cms:models:cache';

    protected $description = 'Caches the model information';


    /**
     * Execute the console command.
     *
     * @param ModelInformationRepositoryInterface $repository
     */
    public function handle(ModelInformationRepositoryInterface $repository)
    {
        $repository
            ->clearCache()
            ->writeCache();

        $this->info('Cached model information.');
    }

}
