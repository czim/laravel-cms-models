<?php
namespace Czim\CmsModels\Test\Integration\Controllers\Web;

use Czim\CmsModels\Test\Helpers\Strategies\Delete\MockDeleteSpy;
use Czim\CmsModels\Test\Helpers\Strategies\DeleteCondition\OnlyIfIdIsTwo;
use Czim\CmsModels\Test\Helpers\Strategies\DeleteCondition\PassesOnParameter;
use Czim\CmsModels\Test\Integration\Controllers\AbstractControllerIntegrationTest;

/**
 * Class DeleteTest
 *
 * Tests for simple activatable model functions.
 *
 * @group integration
 * @group controllers
 */
class DeleteTest extends AbstractControllerIntegrationTest
{
    const ROUTE_BASE = 'cms::models.model.czim-cmsmodels-test-helpers-models-testpost';

    protected $customModelConfiguration = [
        'it_does_not_allow_deleting_when_explicitly_disallowed' => [
            'test-post' => [
                'allow_delete' => false,
            ],
        ],
        'it_uses_a_delete_condition_if_configured' => [
            'test-post' => [
                'delete_condition' => OnlyIfIdIsTwo::class . '|' . PassesOnParameter::class . ':1',
            ],
        ],
        'it_uses_a_delete_strategy_if_configured' => [
            'test-post' => [
                'delete_strategy' => MockDeleteSpy::class,
            ],
        ],
    ];


    /**
     * @test
     */
    function it_deletes_a_model()
    {
        $this->visitRoute(static::ROUTE_BASE . '.index')->seeStatusCode(200);

        static::assertHtmlElementInResponse('tr.records-row[data-id=1]', 'Record #1 should be present');

        $token = $this->getCsrfTokenFromResponse();

        // Check the deletable response
        $this->route('GET', static::ROUTE_BASE . '.deletable', [1], [], [], [], $this->getAjaxHeaders());
        $this->seeJson(['success' => true]);

        // Delete the record
        $this->route('POST', static::ROUTE_BASE . '.destroy', [1], [
            '_method'  => 'delete',
            '_token'   => $token,
        ], [], [], $this->getAjaxHeaders());
        $this->seeJson(['success' => true]);

        $this->visitRoute(static::ROUTE_BASE . '.index')->seeStatusCode(200);

        static::assertNotHtmlElementInResponse('tr.records-row[data-id=1]', 'Record #1 should no longer be present');
    }
    
    /**
     * @test
     * @depends it_deletes_a_model
     */
    function it_does_not_allow_deleting_when_explicitly_disallowed()
    {
        $this->visitRoute(static::ROUTE_BASE . '.index')->seeStatusCode(200);

        $token = $this->getCsrfTokenFromResponse();

        // Check the deletable response
        $this->route('GET', static::ROUTE_BASE . '.deletable', [1], [], [], [], $this->getAjaxHeaders());
        $this->seeJson(['success' => false]);

        // Attempt to delete the record
        $this->route('POST', static::ROUTE_BASE . '.destroy', [1], [
            '_method'  => 'delete',
            '_token'   => $token,
        ], [], [], $this->getAjaxHeaders());
        $this->seeJson(['success' => false]);

        // Check if the model is still present
        $this->visitRoute(static::ROUTE_BASE . '.index')->seeStatusCode(200);

        static::assertHtmlElementInResponse('tr.records-row[data-id=1]', 'Record #1 should still be present');
    }

    /**
     * @test
     * @depends it_does_not_allow_deleting_when_explicitly_disallowed
     */
    function it_does_not_allow_deletion_if_user_does_not_have_permission()
    {
        $this->visitRoute(static::ROUTE_BASE . '.index')->seeStatusCode(200);

        $token = $this->getCsrfTokenFromResponse();

        // Remove rights
        $this->mockSuperAdmin = false;

        // Check the deletable response
        $this->route('GET', static::ROUTE_BASE . '.deletable', [1], [], [], [], $this->getAjaxHeaders());
        $this->seeJson(['success' => false]);

        // Attempt to delete the record
        $this->route('POST', static::ROUTE_BASE . '.destroy', [1], [
            '_method'  => 'delete',
            '_token'   => $token,
        ], [], [], $this->getAjaxHeaders());

        $this->seeJson(['success' => false]);
    }

    /**
     * @test
     */
    function it_uses_a_delete_condition_if_configured()
    {
        $this->visitRoute(static::ROUTE_BASE . '.index')->seeStatusCode(200);

        $token = $this->getCsrfTokenFromResponse();

        // Deletion of #1 should not be allowed

        // Check the deletable response
        $this->route('GET', static::ROUTE_BASE . '.deletable', [1], [], [], [], $this->getAjaxHeaders());
        $this->seeJson(['success' => false]);

        // Attempt to delete the record
        $this->route('POST', static::ROUTE_BASE . '.destroy', [1], [
            '_method'  => 'delete',
            '_token'   => $token,
        ], [], [], $this->getAjaxHeaders());
        $this->seeJson(['success' => false]);

        // Deletion of #2 should be allowed

        // Check the deletable response
        $this->route('GET', static::ROUTE_BASE . '.deletable', [2], [], [], [], $this->getAjaxHeaders());
        $this->seeJson(['success' => true]);

        // Attempt to delete the record
        $this->route('POST', static::ROUTE_BASE . '.destroy', [2], [
            '_method'  => 'delete',
            '_token'   => $token,
        ], [], [], $this->getAjaxHeaders());
        $this->seeJson(['success' => true]);
    }

    /**
     * @test
     */
    function it_uses_a_delete_strategy_if_configured()
    {
        static::assertFalse($this->app->bound('mock-delete-spy-triggered'), 'Spy flag setup failed');

        $this->visitRoute(static::ROUTE_BASE . '.index')->seeStatusCode(200);
        $token = $this->getCsrfTokenFromResponse();

        // Attempt to delete the record
        $this->route('POST', static::ROUTE_BASE . '.destroy', [2], [
            '_method'  => 'delete',
            '_token'   => $token,
        ], [], [], $this->getAjaxHeaders());
        $this->seeJson(['success' => true]);

        // Check if the mock delete spy was triggered
        static::assertTrue($this->app->bound('mock-delete-spy-triggered'), 'Spy flag was not set by mock strategy');
    }

}
