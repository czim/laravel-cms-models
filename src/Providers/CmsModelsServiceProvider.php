<?php
namespace Czim\CmsModels\Providers;

use Czim\CmsModels\Analyzer\DatabaseAnalyzer;
use Czim\CmsModels\Console\Commands\ClearModelInformationCache;
use Czim\CmsModels\Console\Commands\ShowModelInformation;
use Czim\CmsModels\Contracts\Analyzer\DatabaseAnalyzerInterface;
use Czim\CmsModels\Contracts\Repositories\ActivateStrategyResolverInterface;
use Czim\CmsModels\Contracts\Repositories\Collectors\ModelInformationCollectorInterface;
use Czim\CmsModels\Contracts\Repositories\Collectors\ModelInformationEnricherInterface;
use Czim\CmsModels\Contracts\Repositories\Collectors\ModelInformationFileReaderInterface;
use Czim\CmsModels\Contracts\Repositories\Collectors\ModelInformationInterpreterInterface;
use Czim\CmsModels\Contracts\Repositories\CurrentModelInformationInterface;
use Czim\CmsModels\Contracts\Repositories\ModelInformationRepositoryInterface;
use Czim\CmsModels\Contracts\Repositories\ModelReferenceRepositoryInterface;
use Czim\CmsModels\Contracts\Repositories\ModelRepositoryInterface;
use Czim\CmsModels\Contracts\Repositories\OrderableStrategyResolverInterface;
use Czim\CmsModels\Contracts\Routing\RouteHelperInterface;
use Czim\CmsModels\Contracts\Support\Factories\ActionStrategyFactoryInterface;
use Czim\CmsModels\Contracts\Support\Factories\ExportColumnStrategyFactoryInterface;
use Czim\CmsModels\Contracts\Support\Factories\ExportStrategyFactoryInterface;
use Czim\CmsModels\Contracts\Support\Factories\FilterStrategyFactoryInterface;
use Czim\CmsModels\Contracts\Support\Factories\FormFieldStrategyFactoryInterface;
use Czim\CmsModels\Contracts\Support\Factories\ListDisplayStrategyFactoryInterface;
use Czim\CmsModels\Contracts\Support\Factories\ShowFieldStrategyFactoryInterface;
use Czim\CmsModels\Contracts\Support\MetaReferenceDataProviderInterface;
use Czim\CmsModels\Contracts\Support\ModuleHelperInterface;
use Czim\CmsModels\Contracts\Support\Session\ModelListMemoryInterface;
use Czim\CmsModels\Contracts\Support\Translation\TranslationLocaleHelperInterface;
use Czim\CmsModels\Events;
use Czim\CmsModels\Listeners\ModelLogListener;
use Czim\CmsModels\Repositories\Collectors\CmsModelInformationInterpreter;
use Czim\CmsModels\Repositories\Collectors\ModelInformationEnricher;
use Czim\CmsModels\Repositories\Collectors\ModelInformationFileReader;
use Czim\CmsModels\Repositories\CurrentModelInformation;
use Czim\CmsModels\Repositories\ModelInformationRepository;
use Czim\CmsModels\Repositories\ModelReferenceRepository;
use Czim\CmsModels\Repositories\ModelRepository;
use Czim\CmsModels\Repositories\ActivateStrategies\ActivateStrategyResolver;
use Czim\CmsModels\Repositories\OrderableStrategies\OrderableStrategyResolver;
use Czim\CmsModels\Support\Factories\ActionStrategyFactory;
use Czim\CmsModels\Support\Factories\ExportColumnStrategyFactory;
use Czim\CmsModels\Support\Factories\ExportStrategyFactory;
use Czim\CmsModels\Support\Factories\FilterStrategyFactory;
use Czim\CmsModels\Support\Factories\FormFieldStrategyFactory;
use Czim\CmsModels\Support\Factories\ListDisplayStrategyFactory;
use Czim\CmsModels\Support\Factories\ShowFieldStrategyFactory;
use Czim\CmsModels\Support\ModuleHelper;
use Czim\CmsModels\Support\Routing\RouteHelper;
use Czim\CmsModels\Support\Session\ModelListMemory;
use Czim\CmsModels\Support\Strategies\MetaReferenceDataProvider;
use Czim\CmsModels\Support\Translation\TranslationLocaleHelper;
use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Support\Enums\Component;
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
        $this->app->bind(ModelRepositoryInterface::class, ModelRepository::class);

        $this->app->singleton(ModelReferenceRepositoryInterface::class, ModelReferenceRepository::class);

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
        $this->app->singleton(ModelInformationRepositoryInterface::class, ModelInformationRepository::class);
        $this->app->singleton(ModelInformationFileReaderInterface::class, ModelInformationFileReader::class);
        $this->app->singleton(ModelInformationEnricherInterface::class, ModelInformationEnricher::class);
        $this->app->singleton(ModelInformationInterpreterInterface::class, CmsModelInformationInterpreter::class);
        $this->app->singleton(DatabaseAnalyzerInterface::class, DatabaseAnalyzer::class);

        $this->app->singleton(CurrentModelInformationInterface::class, CurrentModelInformation::class);

        return $this;
    }

    /**
     * Registers interface bindings for various strategies.
     *
     * @return $this
     */
    protected function registerStrategyInterfaceBindings()
    {
        $this->app->singleton(FilterStrategyFactoryInterface::class, FilterStrategyFactory::class);
        $this->app->singleton(ActivateStrategyResolverInterface::class, ActivateStrategyResolver::class);
        $this->app->singleton(OrderableStrategyResolverInterface::class, OrderableStrategyResolver::class);
        $this->app->singleton(ListDisplayStrategyFactoryInterface::class, ListDisplayStrategyFactory::class);
        $this->app->singleton(ShowFieldStrategyFactoryInterface::class, ShowFieldStrategyFactory::class);
        $this->app->singleton(FormFieldStrategyFactoryInterface::class, FormFieldStrategyFactory::class);
        $this->app->singleton(ActionStrategyFactoryInterface::class, ActionStrategyFactory::class);
        $this->app->singleton(ExportColumnStrategyFactoryInterface::class, ExportColumnStrategyFactory::class);
        $this->app->singleton(ExportStrategyFactoryInterface::class, ExportStrategyFactory::class);

        return $this;
    }

    /**
     * Registers bindings for facade service names.
     *
     * @return $this
     */
    protected function registerFacadeBindings()
    {
        $this->app->bind('cms-models-modelinfo', CurrentModelInformationInterface::class);
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
        $this->app->singleton(ModelInformationCollectorInterface::class, config('cms-models.collector.class'));

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
            \Event::listen($event, $listener);
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
