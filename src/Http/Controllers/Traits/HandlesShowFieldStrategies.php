<?php
namespace Czim\CmsModels\Http\Controllers\Traits;

use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsModels\Contracts\Data\ModelInformationInterface;
use Czim\CmsModels\Contracts\Data\ModelShowFieldDataInterface;
use Czim\CmsModels\Contracts\Support\Factories\ShowFieldStrategyFactoryInterface;
use Czim\CmsModels\Exceptions\StrategyRenderException;
use Czim\CmsModels\Support\Data\ModelInformation;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;

trait HandlesShowFieldStrategies
{

    /**
     * Renders Vies or HTML for show field strategies.
     *
     * @param Model $model
     * @return View[]|\string[]
     * @throws StrategyRenderException
     */
    protected function renderedShowFieldStrategies(Model $model)
    {
        $views = [];

        foreach ($this->getModelInformation()->show->fields as $key => $data) {

            if ( ! $this->allowedToUseShowFieldData($data)) {
                continue;
            }

            try {
                $instance = $this->getShowFieldFactory()->make($data->strategy);

                // Feed any extra information we can gather to the instance
                $instance->setOptions($data->options());

                if ($data->source) {
                    if (isset($this->getModelInformation()->attributes[ $data->source ])) {
                        $instance->setAttributeInformation(
                            $this->getModelInformation()->attributes[ $data->source ]
                        );
                    }
                }

            } catch (\Exception $e) {

                $message = "Failed to make show field strategy for '{$key}': \n{$e->getMessage()}";

                throw new StrategyRenderException($message, $e->getCode(), $e);
            }

            try {
                $views[ $key ] = $instance->render($model, $data->source);

            } catch (\Exception $e) {

                $message = "Failed to render show field '{$key}' for strategy " . get_class($instance)
                         . ": \n{$e->getMessage()}";

                throw new StrategyRenderException($message, $e->getCode(), $e);
            }
        }

        return $views;
    }

    /**
     * Returns whether current user has permission to use the form field.
     *
     * @param ModelShowFieldDataInterface $field
     * @return bool
     */
    protected function allowedToUseShowFieldData(ModelShowFieldDataInterface $field)
    {
        if ( ! $field->adminOnly() && ! count($field->permissions())) {
            return true;
        }

        $user = $this->getCore()->auth()->user();

        if ( ! $user) {
            return false;
        }

        if ($field->adminOnly() && ! $user->isAdmin()) {
            return false;
        }

        if ( ! count($field->permissions())) {
            return true;
        }

        return $user->can($field->permissions());
    }

    /**
     * @return ShowFieldStrategyFactoryInterface
     */
    protected function getShowFieldFactory()
    {
        return app(ShowFieldStrategyFactoryInterface::class);
    }

    /**
     * @return ModelInformation|ModelInformationInterface
     * @see BaseModelController::getModelInformation()
     */
    abstract protected function getModelInformation();

    /**
     * @return CoreInterface
     */
    abstract protected function getCore();

}
