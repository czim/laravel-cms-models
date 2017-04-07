<?php
namespace Czim\CmsModels\Test\Repositories\ActivateStrategies;

use Czim\CmsModels\Repositories\ActivateStrategies\ActiveColumn;
use Czim\CmsModels\Test\AbstractPostCommentSeededTestCase;
use Czim\CmsModels\Test\Helpers\Models\TestPost;

/**
 * Class ActiveColumnTest
 *
 * @group repository
 * @group repository-strategy
 */
class ActiveColumnTest extends AbstractPostCommentSeededTestCase
{

    protected function seedDatabase()
    {
        TestPost::create([
            'title' => 'Some Basic Title',
            'body'  => 'Lorem ipsum dolor sit amet, egg beater batter pan consectetur adipiscing elit. Cras nec erat a turpis iaculis viverra sed in dolor. Morbi nec magna eleifend, condimentum metus in, mollis orci. Aliquam bibendum est in velit semper lacinia. In ornare maximus odio eu ultrices. Nullam pulvinar nisi tempus dictum vestibulum. Morbi et felis metus. Mauris vestibulum, orci non venenatis faucibus, libero sem ultrices tellus, a faucibus dui tellus ut tellus. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Cras ac est vitae est sodales sollicitudin sed sit amet orci. Integer porttitor faucibus libero, vitae rhoncus enim faucibus convallis. Phasellus quis orci sed odio fringilla congue nec eu elit. Mauris turpis lacus, rutrum quis turpis at, rutrum dapibus dolor. Proin placerat turpis sed lorem ultrices, vitae mattis tortor ornare.',
            'type' => 'notice',
            'checked' => true,
            'description' => 'the best possible post for testing',
        ]);
    }

    /**
     * @test
     */
    function it_updates_the_active_state_for_a_model()
    {
        $model = TestPost::first();

        $strategy = new ActiveColumn;
        $strategy->setColumn('checked');

        // Deactivate
        $strategy->update($model, false);

        static::assertFalse($model->fresh()->checked);

        // Activate
        $strategy->update($model);

        static::assertTrue($model->fresh()->checked);
    }

}
