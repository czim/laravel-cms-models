<?php
namespace Czim\CmsModels\Repositories\OrderableStrategies;

use Czim\CmsModels\Contracts\Repositories\OrderableStrategyInterface;
use Czim\CmsModels\Support\Enums\OrderablePosition;
use Illuminate\Database\Eloquent\Model;

class ListifyStrategy implements OrderableStrategyInterface
{

    /**
     * Sets a new orderable position for a model.
     *
     * @param Model|\Czim\Listify\Contracts\ListifyInterface $model
     * @param mixed $position
     * @return mixed|false
     */
    public function setPosition(Model $model, $position)
    {
        if ($position === $model->getListifyPosition()) {
            return $position;
        }

        switch ($position) {

            case null:
            case OrderablePosition::REMOVE:
                $model->removeFromList();
                break;

            case OrderablePosition::UP:
                $model->moveHigher();
                break;

            case OrderablePosition::DOWN:
                $model->moveLower();
                break;

            case OrderablePosition::TOP:
                if ($model->isInList()) {
                    $model->moveToTop();
                } else {
                    $model->insertAt();
                }
                break;

            case OrderablePosition::BOTTOM:
                if ($model->isInList()) {
                    $model->moveToBottom();
                } else {
                    $model->insertAt();
                    $model->moveToBottom();
                }
                break;

            default:
                $position = (int) $position;

                $model->insertAt($position);
        }

        return $model->getListifyPosition();
    }

}
