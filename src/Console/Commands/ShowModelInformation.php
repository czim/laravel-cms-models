<?php
namespace Czim\CmsModels\Console\Commands;

use Czim\CmsModels\Contracts\Repositories\ModelInformationRepositoryInterface;
use Illuminate\Console\Command;

class ShowModelInformation extends Command
{

    protected $signature = 'cms:models:show {model? : Model information key or class name} 
        {--keys : Show only a list of model keys}';

    protected $description = 'Shows model information (for debugging purposes)';


    /**
     * Execute the console command.
     *
     * @param ModelInformationRepositoryInterface $repository
     */
    public function handle(ModelInformationRepositoryInterface $repository)
    {
        $model = $this->argument('model');

        if ($this->option('keys')) {
            $this->displayKeys($repository->getAll()->keys());
            return;
        }

        if ( ! $model) {
            // Show all models
            $this->display($repository->getAll()->toArray());
            return;
        }

        // Try by key first
        $info = $repository->getByKey($model);

        if ($info) {
            $this->display($info->toArray());
            return;
        }

        // Try by class
        $info = $repository->getByModelClass($model);

        if ($info) {
            $this->display($info->toArray());
            return;
        }

        $this->error("Unable to find information for model by key or class '{$model}'");
    }

    /**
     * Display model keys in console.
     *
     * @param \Iterator|\IteratorAggregate|string[] $keys
     */
    protected function displayKeys($keys)
    {
        $this->info('Model keys:');

        foreach ($keys as $key) {
            $this->comment('  ' . $key);
        }
    }

    /**
     * Displays data in the console for debugging.
     *
     * @param mixed $data
     */
    protected function display($data)
    {
        dd($data);
    }

}
