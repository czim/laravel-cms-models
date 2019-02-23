<?php
namespace Czim\CmsModels\Console\Commands;

use Czim\CmsModels\Contracts\Repositories\ModelInformationRepositoryInterface;
use Illuminate\Console\Command;
use Symfony\Component\VarDumper\VarDumper;

class ShowModelInformation extends Command
{

    protected $signature = 'cms:models:show {model? : Model information key or class name}
        {--pluck= : Only shows data for this dot-notation key (f.e. form.fields)}
        {--keys : Show only a list of model keys, or when plucking, only the keys at the plucked level}';

    protected $description = 'Shows model information (for debugging purposes)';


    /**
     * Execute the console command.
     *
     * @param ModelInformationRepositoryInterface $repository
     */
    public function handle(ModelInformationRepositoryInterface $repository)
    {
        $model = $this->argument('model');

        if ($this->option('keys') && ! $this->option('pluck')) {
            $this->displayKeys($repository->getAll()->keys());
            return;
        }

        if ( ! $model) {
            // Show all models
            $this->displayAll($repository->getAll()->toArray());
            return;
        }

        // Try by key first
        $info = $repository->getByKey($model);

        if ($info) {
            $this->display($model, $info->toArray());
            return;
        }

        // Try by class
        $info = $repository->getByModelClass($model);

        if ($info) {
            $this->display($model, $info->toArray());
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

        $this->info('');
    }

    /**
     * Displays data in console for a list of model information arrays.
     *
     * @param array $data
     */
    protected function displayAll(array $data)
    {
        foreach ($data as $key => $single) {

            $this->display($key, $single);
        }
    }

    /**
     * Displays data in the console for a single information array.
     *
     * @param string $key   model key or class for display only
     * @param array  $data
     */
    protected function display($key, array $data)
    {
        if ($pluck = $this->option('pluck')) {

            if ( ! array_has($data, $pluck)) {
                $this->warn("Nothing to pluck for '{$pluck}'.");
                return;
            }

            $data = array_get($data, $pluck);
        }

        $this->comment($key);
        
        if ($this->option('keys') && is_array($data)) {
            $data = array_keys($data);
        }

        $this->getDumper()->dump($data);

        $this->info('');
    }

    /**
     * @return VarDumper
     */
    protected function getDumper()
    {
        return app(VarDumper::class);
    }

}
