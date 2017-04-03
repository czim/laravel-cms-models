<?php
namespace Czim\CmsModels\Test\ModelInformation\Data\Listing;

use Czim\CmsCore\Contracts\Auth\AuthenticatorInterface;
use Czim\CmsCore\Contracts\Auth\UserInterface;
use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Support\Enums\Component;
use Czim\CmsModels\ModelInformation\Data\Listing\ModelListData;
use Czim\CmsModels\Test\TestCase;
use Mockery;

/**
 * Class ModelListDataTest
 *
 * @group modelinformation-data
 */
class ModelListDataTest extends TestCase
{

    /**
     * @test
     */
    function it_returns_orderable_column()
    {
        $data = new ModelListData;

        static::assertEquals('position', $data->getOrderableColumn());

        $data->order_column = 'testing';

        static::assertEquals('testing', $data->getOrderableColumn());
    }

    /**
     * @test
     */
    function it_returns_null_if_default_action_is_falsy()
    {
        $data = new ModelListData;

        $data->default_action = false;

        static::assertNull($data->getDefaultAction());
    }

    /**
     * @test
     */
    function it_returns_first_allowed_default_action()
    {
        $data = new ModelListData;

        $data->default_action = [
            [
                'strategy'    => 'edit',
                'permissions' => 'testing.edit',
            ],
            [
                'strategy' => 'show',
            ],
        ];

        $mockUser = $this->getMockUser();
        $mockAuth = $this->getMockAuth();
        $mockCore = $this->getMockCore();
        $mockUser->shouldReceive('isAdmin')->andReturn(false);
        $mockUser->shouldReceive('can')->with(['testing.edit'])->andReturn(true, false);
        $mockAuth->shouldReceive('user')->andReturn($mockUser);
        $mockCore->shouldReceive('auth')->andReturn($mockAuth);

        $this->app->instance(Component::CORE, $mockCore);

        static::assertEquals('edit', $data->getDefaultAction()->strategy());
        static::assertEquals('show', $data->getDefaultAction()->strategy());
    }

    /**
     * @test
     */
    function it_returns_null_for_default_action_if_none_allowed()
    {
        $data = new ModelListData;

        $data->default_action = [
            [
                'strategy'    => 'edit',
                'permissions' => 'testing.edit',
            ],
        ];

        $mockUser = $this->getMockUser();
        $mockAuth = $this->getMockAuth();
        $mockCore = $this->getMockCore();
        $mockUser->shouldReceive('isAdmin')->andReturn(false);
        $mockUser->shouldReceive('can')->with(['testing.edit'])->andReturn(false);
        $mockAuth->shouldReceive('user')->andReturn($mockUser);
        $mockCore->shouldReceive('auth')->andReturn($mockAuth);

        $this->app->instance(Component::CORE, $mockCore);

        static::assertNull($data->getDefaultAction());
    }

    /**
     * @test
     */
    function it_returns_default_action_if_stored_as_string()
    {
        $data = new ModelListData;

        $data->default_action = 'show';

        $mockCore = $this->getMockCore();
        $this->app->instance(Component::CORE, $mockCore);

        static::assertEquals('show', $data->getDefaultAction()->strategy());
    }

    /**
     * @test
     */
    function it_merges_with_another_dataobject()
    {
        $data = new ModelListData;

        $data->page_size      = 10;
        $data->columns        = [
            'a' => [
                'source' => 'a',
            ],
            'b' => [
                'source' => 'b',
            ],
        ];
        $data->filters        = [
            'test' => [
                'strategy' => 'test',
            ],
        ];
        $data->scopes         = [
            'test' => [
                'method' => 'test',
            ],
        ];
        $data->default_action = [
            [
                'strategy' => 'a',
            ],
        ];

        $with = new ModelListData;

        $with->page_size      = 20;
        $with->columns        = [
            'a' => [
                'source' => 'x',
            ],
            'c' => [
                'source' => 'y',
            ],
        ];
        $with->filters        = [
            'test' => [
                'strategy' => 'replace',
            ],
        ];
        $with->scopes         = [
            'test' => [
                'method' => 'replace',
            ],
        ];
        $with->default_action = [
            [
                'strategy' => 'b',
            ],
        ];

        $data->merge($with);

        static::assertEquals(20, $data->page_size);
        static::assertEquals('replace', head($data->scopes)->method);
        static::assertEquals(['a', 'c'], array_keys($data->columns));
        static::assertEquals('x', $data->columns['a']->source);
        static::assertEquals('y', $data->columns['c']->source);
        static::assertEquals('b', head($data->default_action)->strategy);
    }

    /**
     * @return CoreInterface|Mockery\MockInterface|Mockery\Mock
     */
    protected function getMockCore()
    {
        return Mockery::mock(CoreInterface::class);
    }

    /**
     * @return AuthenticatorInterface|Mockery\MockInterface|Mockery\Mock
     */
    protected function getMockAuth()
    {
        return Mockery::mock(AuthenticatorInterface::class);
    }

    /**
     * @return UserInterface|Mockery\MockInterface|Mockery\Mock
     */
    protected function getMockUser()
    {
        return Mockery::mock(UserInterface::class);
    }

}
