<?php
namespace Czim\CmsModels\Test\Integration\Controllers\Web;

use Czim\CmsModels\Test\Integration\Controllers\AbstractControllerIntegrationTest;

/**
 * Class DefaultShow
 *
 * Tests for simple model show page. Note that browser/javascript functionality is deliberately not tested here.
 *
 * @group integration
 * @group controllers
 */
class DefaultShow extends AbstractControllerIntegrationTest
{
    const ROUTE_BASE = 'cms::models.model.czim-cmsmodels-test-helpers-models-testpost';


    /**
     * @test
     */
    function it_shows_model_data()
    {
        $this->visitRoute(static::ROUTE_BASE . '.show', [1])->assertStatus(200);

        $groups = $this->crawler()->filter('div.form-group.row');

        static::assertCount(9, $groups, 'Number of fields shown does not match');

        $group = $groups->first();

        static::assertEquals('Id', trim($group->filter('label')->html()), 'Field label for ID mismatch');
        static::assertEquals('1', trim($group->filter('div div')->html()), 'Field value for ID mismatch');
    }

}
