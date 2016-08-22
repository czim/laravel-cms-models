<?php
namespace Czim\CmsModels\Providers;

use Czim\CmsModels\Analyzer\DatabaseAnalyzer;
use Czim\CmsModels\Console\Commands\ClearModelInformationCache;
use Czim\CmsModels\Contracts\Analyzer\DatabaseAnalyzerInterface;
use Czim\CmsModels\Contracts\Data\ModelInformationInterface;
use Czim\CmsModels\Contracts\Repositories\Collectors\ModelInformationCollectorInterface;
use Czim\CmsModels\Contracts\Repositories\Collectors\ModelInformationEnricherInterface;
use Czim\CmsModels\Contracts\Repositories\CurrentModelInformationInterface;
use Czim\CmsModels\Contracts\Repositories\ModelInformationRepositoryInterface;
use Czim\CmsModels\Contracts\Repositories\ModelRepositoryInterface;
use Czim\CmsModels\Contracts\Routing\RouteHelperInterface;
use Czim\CmsModels\Contracts\Support\ModuleHelperInterface;
use Czim\CmsModels\Contracts\View\FilterStrategyInterface;
use Czim\CmsModels\Contracts\View\ListStrategyInterface;
use Czim\CmsModels\Contracts\View\ListStrategyResolverInterface;
use Czim\CmsModels\Repositories\Collectors\ModelInformationEnricher;
use Czim\CmsModels\Repositories\CurrentModelInformation;
use Czim\CmsModels\Repositories\ModelInformationRepository;
use Czim\CmsModels\Repositories\ModelRepository;
use Czim\CmsModels\Support\ModuleHelper;
use Czim\CmsModels\Support\Routing\RouteHelper;
use Czim\CmsModels\View\FilterStrategy;
use Czim\CmsModels\View\ListStrategy;
use Czim\CmsModels\View\ListStrategyResolver;
use Illuminate\Support\ServiceProvider;
use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Support\Enums\Component;

class CmsModelsServiceProvider extends ServiceProvider
{

    /**
     * @var CoreInterface
     */
    protected $core;


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
             ->registerConfiguredCollector();
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
     * Register Authorization CMS commands
     *
     * @return $this
     */
    protected function registerCommands()
    {
        $this->app->singleton('cms.commands.models.clear-information-cache', ClearModelInformationCache::class);

        $this->commands([
            'cms.commands.models.clear-information-cache',
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
        $this->app->singleton(ModelInformationRepositoryInterface::class, ModelInformationRepository::class);
        $this->app->singleton(CurrentModelInformationInterface::class, CurrentModelInformation::class);
        $this->app->singleton(ModelRepositoryInterface::class, ModelRepository::class);
        $this->app->singleton(ModelInformationEnricherInterface::class, ModelInformationEnricher::class);
        $this->app->singleton(RouteHelperInterface::class, RouteHelper::class);
        $this->app->singleton(ModuleHelperInterface::class, ModuleHelper::class);
        $this->app->singleton(DatabaseAnalyzerInterface::class, DatabaseAnalyzer::class);
        $this->app->singleton(ListStrategyInterface::class, ListStrategy::class);
        $this->app->singleton(ListStrategyResolverInterface::class, ListStrategyResolver::class);
        $this->app->singleton(FilterStrategyInterface::class, FilterStrategy::class);


        // Register facade names
        $this->app->bind('cms-models-modelinfo', CurrentModelInformationInterface::class);

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
     * @return $this
     */
    protected function bootConfig()
    {
        $this->publishes([
            realpath(dirname(__DIR__) . '/../config/cms-models.php') => config_path('cms-models.php'),
        ]);

        return $this;
    }

    /**
     * Initializes the repository with collected model information.
     *
     * @return $this
     */
    protected function initializeModelInformationRepository()
    {
        /** @var ModelInformationRepositoryInterface $repository */
        $repository = app(ModelInformationRepositoryInterface::class);

        $repository->initialize();

        return $this;
    }

}
