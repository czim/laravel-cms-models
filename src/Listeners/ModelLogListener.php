<?php
namespace Czim\CmsModels\Listeners;

use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsModels\Events;
use Psr\Log\LogLevel;

/**
 * Class ModelLogListener
 *
 * Listener for model update events for writing log entries.
 */
class ModelLogListener
{

    /**
     * @var CoreInterface
     */
    protected $core;

    /**
     * @param CoreInterface $core
     */
    public function __construct(CoreInterface $core)
    {
        $this->core = $core;
    }


    public function modelCreated(Events\ModelCreatedInCms $event)
    {
        $this->core->log(
            LogLevel::INFO,
            "Model was created: "
            . get_class($event->model)
            . " #{$event->model->getKey()}"
            . $this->getUserPostfix()
            . $this->getIpPostfix()
        );
    }

    public function modelUpdated(Events\ModelUpdatedInCms $event)
    {
        $this->core->log(
            LogLevel::INFO,
            "Model was updated: "
            . get_class($event->model)
            . " #{$event->model->getKey()}"
            . $this->getUserPostfix()
            . $this->getIpPostfix()
        );
    }

    public function deletingModel(Events\DeletingModelInCms $event)
    {
        // Ignore for now.
    }

    public function modelDeleted(Events\ModelDeletedInCms $event)
    {
        $this->core->log(
            LogLevel::INFO,
            "Model was deleted: {$event->class} #{$event->key}"
            . $this->getUserPostfix()
            . $this->getIpPostfix()
        );
    }

    public function modelActivated(Events\ModelActivatedInCms $event)
    {
        $this->core->log(
            LogLevel::INFO,
            "Model was activated: "
            . get_class($event->model)
            . " #{$event->model->getKey()}"
            . $this->getUserPostfix()
            . $this->getIpPostfix()
        );
    }

    public function modelDeactivated(Events\ModelDeactivatedInCms $event)
    {
        $this->core->log(
            LogLevel::INFO,
            "Model was deactivated: "
            . get_class($event->model)
            . " #{$event->model->getKey()}"
            . $this->getUserPostfix()
            . $this->getIpPostfix()
        );
    }

    public function modelPositionUpdated(Events\ModelPositionUpdatedInCms $event)
    {
        $this->core->log(
            LogLevel::INFO,
            "Model was repositioned: "
            . get_class($event->model)
            . " #{$event->model->getKey()}"
            . $this->getUserPostfix()
            . $this->getIpPostfix()
        );
    }

    /**
     * Returns user name for currently logged in user, if known.
     *
     * @return null|string
     */
    protected function getUserString()
    {
        $user = $this->core->auth()->user();

        if ( ! $user) return null;

        return $user->getUsername();
    }

    /**
     * Returns request IP, if known.
     *
     * @return null|string
     */
    protected function getIpString()
    {
        $ip = request()->ip();

        if ( ! $ip) return null;

        return $ip;
    }

    /**
     * Returns postfix with user name, if known.
     *
     * @return null|string
     */
    protected function getUserPostfix()
    {
        $user = $this->getUserString();

        if ( ! $user) return null;

        return " by user: '{$user}'";
    }

    /**
     * Returns postfix with request IP, if known.
     *
     * @return null|string
     */
    protected function getIpPostfix()
    {
        $ip = $this->getIpString();

        if ( ! $ip) return null;

        return " (IP: '{$ip}')";
    }

}
