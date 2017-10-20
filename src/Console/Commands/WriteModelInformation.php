<?php
namespace Czim\CmsModels\Console\Commands;

use Czim\CmsModels\Contracts\ModelInformation\Data\ModelInformationInterface;
use Czim\CmsModels\Contracts\ModelInformation\Writer\ModelInformationWriterInterface;
use Czim\CmsModels\Contracts\Repositories\ModelInformationRepositoryInterface;
use Czim\CmsModels\Exceptions\ModelInformationFileAlreadyExistsException;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class WriteModelInformation extends Command
{

    protected $signature = 'cms:models:write {model? : Model information key or class name}
        {--path= : The path to write the information to, if not the default location }
        {--keys= : Specific section keys in dot-notation, comma-separated, to include }';

    protected $description = 'Writes model information defaults to file (will not overwrite existing)';


    /**
     * Execute the console command.
     *
     * @param ModelInformationRepositoryInterface $repository
     */
    public function handle(ModelInformationRepositoryInterface $repository)
    {
        $model = $this->argument('model');

        if ( ! $model) {

            $infos = $repository->getAll();

        } else {

            $info = $repository->getByKey($model);

            if ( ! $info) {
                $info = $repository->getByModelClass($model);
            }

            if ( ! $info) {
                $this->error("Unable to find information for model by key or class '{$model}'");
                return;
            }

            $infos = new Collection([ $info ]);
        }


        $config = $this->getBaseConfiguration();

        foreach ($infos as $info) {
            $this->writeInformation($info, $config);
        }
    }

    protected function getBaseConfiguration()
    {
        return [];
    }

    /**
     * @param ModelInformationInterface $information
     * @param array                     $config
     * @throws Exception
     */
    protected function writeInformation(ModelInformationInterface $information, array $config)
    {
        $writer = $this->getWriterInstance();

        try {
            $path = $writer->write($information, $config);

            $this->info('Wrote model information to ' . pathinfo($path, PATHINFO_BASENAME));

        } catch (ModelInformationFileAlreadyExistsException $e) {

            $this->warn("Not overwriting existing model information file for {$information->modelClass()}.");

        } catch (Exception $e) {

            $this->error("Exception thrown while attempting to write information for {$information->modelClass()}!");

            throw $e;
        }
    }

    /**
     * @return ModelInformationWriterInterface
     */
    protected function getWriterInstance()
    {
        return app(ModelInformationWriterInterface::class);
    }

}
