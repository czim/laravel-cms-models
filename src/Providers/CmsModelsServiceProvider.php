<?php
namespace Czim\CmsModels\Providers;

use Czim\CmsModels\Analyzer\Database\SimpleDatabaseAnalyzer;
use Czim\CmsModels\Analyzer\Processor\ModelAnalyzer;
use Czim\CmsModels\Console\Commands\ClearModelInformationCache;
use Czim\CmsModels\Console\Commands\ShowModelInformation;
use Czim\CmsModels\Contracts\Analyzer\DatabaseAnalyzerInterface;
use Czim\CmsModels\Contracts\Analyzer\ModelAnalyzerInterface;
use Czim\CmsModels\Contracts\ModelInformation as ModelInfoContracts;
use Czim\CmsModels\Contracts\Repositories as RepositoriesContracts;
use Czim\CmsModels\Contracts\Routing\RouteHelperInterface;
use Czim\CmsModels\Contracts\Support\Factories as FactoriesContracts;
use Czim\CmsModels\Contracts\Support\MetaReferenceDataProviderInterface;
use Czim\CmsModels\Contracts\Support\ModuleHelperInterface;
use Czim\CmsModels\Contracts\Support\Session\ModelListMemoryInterface;
use Czim\CmsModels\Contracts\Support\Translation\TranslationLocaleHelperInterface;
use Czim\CmsModels\Events;
use Czim\CmsModels\Listeners\ModelLogListener;
use Czim\CmsModels\ModelInformation;
use Czim\CmsModels\Repositories;
use Czim\CmsModels\Support\Factories;
use Czim\CmsModels\Support\ModuleHelper;
use Czim\CmsModels\Support\Routing\RouteHelper;
use Czim\CmsModels\Support\Session\ModelListMemory;
use Czim\CmsModels\Support\Strategies\MetaReferenceDataProvider;
use Czim\CmsModels\Support\Translation\TranslationLocaleHelper;
use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Support\Enums\Component;
use Event;
use Illuminate\Support\ServiceProvider;

class CmsModelsServiceProvider extends ServiceProvider
{

    /**
     * @var CoreInterface
     */
    protected $core;

    /**
     * Event bindings to set up.
     *
     * @var array
     */
    protected $events = [
        Events\ModelCreatedInCms::class         => ModelLogListener::class . '@modelCreated',
        Events\ModelUpdatedInCms::class         => ModelLogListener::class . '@modelUpdated',
        Events\DeletingModelInCms::class        => ModelLogListener::class . '@deletingModel',
        Events\ModelDeletedInCms::class         => ModelLogListener::class . '@modelDeleted',
        Events\ModelActivatedInCms::class       => ModelLogListener::class . '@modelActivated',
        Events\ModelDeactivatedInCms::class     => ModelLogListener::class . '@modelDeactivated',
        Events\ModelPositionUpdatedInCms::class => ModelLogListener::class . '@modelPositionUpdated',
        Events\ModelListExportedInCms::class    => ModelLogListener::class . '@modelListExported',
    ];


    public function boot()
    {
        $this->bootConfig();
    }


    public function register()
    {
        $this->core = app(Component::CORE);

        $this->registerConfig()
             ->registerCommands()
             ->loadViews()
             ->registerInterfaceBindings()
             ->registerConfiguredCollector()
             ->registerEventListeners();
    }

    /**
     * @return $this
     */
    protected function registerConfig()
    {
        $this->mergeConfigFrom(
            realpath(dirname(__DIR__) . '/../config/cms-models.php'),
            'cms-models'
        );

        return $this;
    }

    /**
     * Register Model related CMS commands
     *
     * @return $this
     */
    protected function registerCommands()
    {
        $this->app->singleton('cms.commands.models.clear-information-cache', ClearModelInformationCache::class);
        $this->app->singleton('cms.commands.models.show-information', ShowModelInformation::class);

        $this->commands([
            'cms.commands.models.clear-information-cache',
            'cms.commands.models.show-information',
        ]);

        return $this;
    }

    /**
     * Loads basic CMS views.
     *
     * @return $this
     */
    protected function loadViews()
    {
        $this->loadViewsFrom(
            realpath(dirname(__DIR__) . '/../resources/views'),
            'cms-models'
        );

        return $this;
    }

    /**
     * Registers interface bindings for various components.
     *
     * @return $this
     */
    protected function registerInterfaceBindings()
    {
        $this->app->bind(RepositoriesContracts\ModelRepositoryInterface::class, Repositories\ModelRepository::class);
        $this->app->bind(FactoriesContracts\ModelRepositoryFactoryInterface::class, Factories\ModelRepositoryFactory::class);

        $this->app->singleton(RepositoriesContracts\ModelReferenceRepositoryInterface::class, Repositories\ModelReferenceRepository::class);

        $this->registerHelperInterfaceBindings()
             ->registerModelInformationInterfaceBindings()
             ->registerStrategyInterfaceBindings()
             ->registerFacadeBindings();

        return $this;
    }

    /**
     * Registers interface bindings for helpers classes.
     *
     * @return $this
     */
    protected function registerHelperInterfaceBindings()
    {
        $this->app->singleton(RouteHelperInterface::class, RouteHelper::class);
        $this->app->singleton(ModuleHelperInterface::class, ModuleHelper::class);
        $this->app->singleton(MetaReferenceDataProviderInterface::class, MetaReferenceDataProvider::class);
        $this->app->singleton(TranslationLocaleHelperInterface::class, TranslationLocaleHelper::class);
        $this->app->singleton(ModelListMemoryInterface::class, ModelListMemory::class);

        return $this;
    }

    /**
     * Registers interface bindings for model information handling.
     *
     * @return $this
     */
    protected function registerModelInformationInterfaceBindings()
    {
        $this->app->singleton(RepositoriesContracts\ModelInformationRepositoryInterface::class, Repositories\ModelInformationRepository::class);
        $this->app->singleton(ModelInfoContracts\Collector\ModelInformationFileReaderInterface::class, ModelInformation\Collector\ModelInformationFileReader::class);
        $this->app->singleton(ModelInfoContracts\ModelInformationEnricherInterface::class, ModelInformation\Enricher\ModelInformationEnricher::class);
        $this->app->singleton(ModelInfoContracts\ModelInformationInterpreterInterface::class, ModelInformation\Interpreter\CmsModelInformationInterpreter::class);

        $this->app->singleton(ModelAnalyzerInterface::class, ModelAnalyzer::class);
        $this->app->singleton(DatabaseAnalyzerInterface::class, SimpleDatabaseAnalyzer::class);

        $this->app->singleton(RepositoriesContracts\CurrentModelInformationInterface::class, Repositories\CurrentModelInformation::class);

        return $this;
    }

    /**
     * Registers interface bindings for various strategies.
     *
     * @return $this
     */
    protected function registerStrategyInterfaceBindings()
    {
        $this->app->singleton(RepositoriesContracts\ActivateStrategyResolverInterface::class, Repositories\ActivateStrategies\ActivateStrategyResolver::class);
        $this->app->singleton(RepositoriesContracts\OrderableStrategyResolverInterface::class, Repositories\OrderableStrategies\OrderableStrategyResolver::class);
        $this->app->singleton(FactoriesContracts\FilterStrategyFactoryInterface::class, Factories\FilterStrategyFactory::class);
        $this->app->singleton(FactoriesContracts\ListDisplayStrategyFactoryInterface::class, Factories\ListDisplayStrategyFactory::class);
        $this->app->singleton(FactoriesContracts\ShowFieldStrategyFactoryInterface::class, Factories\ShowFieldStrategyFactory::class);
        $this->app->singleton(FactoriesContracts\FormFieldStrategyFactoryInterface::class, Factories\FormFieldStrategyFactory::class);
        $this->app->singleton(FactoriesContracts\ActionStrategyFactoryInterface::class, Factories\ActionStrategyFactory::class);
        $this->app->singleton(FactoriesContracts\ExportColumnStrategyFactoryInterface::class, Factories\ExportColumnStrategyFactory::class);
        $this->app->singleton(FactoriesContracts\ExportStrategyFactoryInterface::class, Factories\ExportStrategyFactory::class);

        return $this;
    }

    /**
     * Registers bindings for facade service names.
     *
     * @return $this
     */
    protected function registerFacadeBindings()
    {
        $this->app->bind('cms-models-modelinfo', RepositoriesContracts\CurrentModelInformationInterface::class);
        $this->app->bind('cms-translation-locale-helper', TranslationLocaleHelperInterface::class);

        return $this;
    }

    /**
     * Binds the model information collector.
     *
     * @return $this
     */
    protected function registerConfiguredCollector()
    {
        $this->app->singleton(ModelInfoContracts\ModelInformationCollectorInterface::class, config('cms-models.collector.class'));

        return $this;
    }

    /**
     * Registers listeners for events.
     *
     * @return $this
     */
    protected function registerEventListeners()
    {
        foreach ($this->events as $event => $listener) {
            Event::listen($event, $listener);
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function bootConfig()
    {
        $this->publishes([
            realpath(dirname(__DIR__) . '/../config/cms-models.php') => config_path('cms-models.php'),
        ]);

        return $this;
    }

}
