<?php

namespace Tightenco\Tests\Unit;

use Tightenco\Tests\TestCase;
use Tightenco\Ziggy\RoutePayload;

class RoutePayloadTest extends TestCase
{
    protected $router;

    public function setUp(): void
    {
        parent::setUp();

        $this->router = app('router');
        $this->router->get('/home', function () {
            return '';
        })
            ->name('home');

        $this->router->get('/posts', function () {
            return '';
        })
            ->name('posts.index');

        $this->router->get('/posts/{post}', function () {
            return '';
        })
            ->name('posts.show');

        $this->router->get('/posts/{post}/comments', function () {
            return '';
        })
            ->name('postComments.index');

        $this->router->post('/posts', function () {
            return '';
        })
            ->name('posts.store')->middleware(['auth', 'role:admin']);

        $this->router->get('/admin/users', function () {
            return '';
        })
            ->name('admin.users.index')->middleware('role:admin');

        $this->router->getRoutes()->refreshNameLookups();
    }

    /** @test */
    public function only_matching_routes_included_with_include_enabled()
    {
        $routePayload = new RoutePayload($this->router);
        $filters = ['posts.s*', 'home'];
        $routes = $routePayload->filter($filters, true);

        $expected = [
            'home' => [
                'uri' => 'home',
                'methods' => ['GET', 'HEAD'],
                'domain' => null,
            ],
            'posts.show' => [
                'uri' => 'posts/{post}',
                'methods' => ['GET', 'HEAD'],
                'domain' => null,
            ],
            'posts.store' => [
                'uri' => 'posts',
                'methods' => ['POST'],
                'domain' => null,
            ],
        ];

        $this->assertEquals($expected, $routes->toArray());
    }

    /** @test */
    public function only_matching_routes_excluded_with_exclude_enabled()
    {
        $routePayload = new RoutePayload($this->router);
        $filters = ['posts.s*', 'home', 'admin.*'];
        $routes = $routePayload->filter($filters, false);

        $expected = [
            'posts.index' => [
                'uri' => 'posts',
                'methods' => ['GET', 'HEAD'],
                'domain' => null,
            ],
            'postComments.index' => [
                'uri' => 'posts/{post}/comments',
                'methods' => ['GET', 'HEAD'],
                'domain' => null,
            ],
        ];

        $this->assertEquals($expected, $routes->toArray());
    }

    /** @test */
    public function existence_of_only_config_causes_routes_to_be_included()
    {
        app()['config']->set('ziggy', [
            'only' => ['posts.s*', 'home'],
        ]);

        $routes = RoutePayload::compile($this->router);

        $expected = [
            'home' => [
                'uri' => 'home',
                'methods' => ['GET', 'HEAD'],
                'domain' => null,
            ],
            'posts.show' => [
                'uri' => 'posts/{post}',
                'methods' => ['GET', 'HEAD'],
                'domain' => null,
            ],
            'posts.store' => [
                'uri' => 'posts',
                'methods' => ['POST'],
                'domain' => null,
            ],
        ];

        $this->assertEquals($expected, $routes->toArray());
    }

    /** @test */
    public function existence_of_except_config_causes_routes_to_be_excluded()
    {
        app()['config']->set('ziggy', [
            'except' => ['posts.s*', 'home', 'admin.*'],
        ]);

        $routes = RoutePayload::compile($this->router);

        $expected = [
            'posts.index' => [
                'uri' => 'posts',
                'methods' => ['GET', 'HEAD'],
                'domain' => null,
            ],
            'postComments.index' => [
                'uri' => 'posts/{post}/comments',
                'methods' => ['GET', 'HEAD'],
                'domain' => null,
            ],
        ];

        $this->assertEquals($expected, $routes->toArray());
    }

    /** @test */
    public function existence_of_both_configs_returns_unfiltered_routes()
    {
        app()['config']->set('ziggy', [
            'except' => ['posts.s*'],
            'only' => ['home'],
        ]);

        $routes = RoutePayload::compile($this->router);

        $expected = [
            'posts.index' => [
                'uri' => 'posts',
                'methods' => ['GET', 'HEAD'],
                'domain' => null,
            ],
            'postComments.index' => [
                'uri' => 'posts/{post}/comments',
                'methods' => ['GET', 'HEAD'],
                'domain' => null,
            ],
            'home' => [
                'uri' => 'home',
                'methods' => ['GET', 'HEAD'],
                'domain' => null,
            ],
            'posts.show' => [
                'uri' => 'posts/{post}',
                'methods' => ['GET', 'HEAD'],
                'domain' => null,
            ],
            'posts.store' => [
                'uri' => 'posts',
                'methods' => ['POST'],
                'domain' => null,
            ],
            'admin.users.index' => [
                'uri' => 'admin/users',
                'methods' => ['GET', 'HEAD'],
                'domain' => null,
            ],
        ];

        $this->assertEquals($expected, $routes->toArray());
    }

    /** @test */
    public function only_matching_routes_included_with_group_enabled()
    {
        app()['config']->set('ziggy', [
            'groups' => [
                'authors' => ['home', 'posts.*'],
            ],
        ]);

        $routes = RoutePayload::compile($this->router, 'authors');

        $expected = [
            'home' => [
                'uri' => 'home',
                'methods' => ['GET', 'HEAD'],
                'domain' => null,
            ],
            'posts.index' => [
                'uri' => 'posts',
                'methods' => ['GET', 'HEAD'],
                'domain' => null,
            ],
            'posts.show' => [
                'uri' => 'posts/{post}',
                'methods' => ['GET', 'HEAD'],
                'domain' => null,
            ],
            'posts.store' => [
                'uri' => 'posts',
                'methods' => ['POST'],
                'domain' => null,
            ],
        ];

        $this->assertEquals($expected, $routes->toArray());
    }

    /** @test */
    public function non_existence_of_group_returns_unfiltered_routes()
    {
        $routes = RoutePayload::compile($this->router, 'authors');

        $expected = [
            'posts.index' => [
                'uri' => 'posts',
                'methods' => ['GET', 'HEAD'],
                'domain' => null,
            ],
            'postComments.index' => [
                'uri' => 'posts/{post}/comments',
                'methods' => ['GET', 'HEAD'],
                'domain' => null,
            ],
            'home' => [
                'uri' => 'home',
                'methods' => ['GET', 'HEAD'],
                'domain' => null,
            ],
            'posts.show' => [
                'uri' => 'posts/{post}',
                'methods' => ['GET', 'HEAD'],
                'domain' => null,
            ],
            'posts.store' => [
                'uri' => 'posts',
                'methods' => ['POST'],
                'domain' => null,
            ],
            'admin.users.index' => [
                'uri' => 'admin/users',
                'methods' => ['GET', 'HEAD'],
                'domain' => null,
            ],
        ];

        $this->assertEquals($expected, $routes->toArray());
    }

    /** @test */
    public function retrieves_middleware_if_config_is_set()
    {
        app()['config']->set('ziggy', [
            'middleware' => true,
        ]);

        $routes = RoutePayload::compile($this->router);

        $expected = [
            'posts.index' => [
                'uri' => 'posts',
                'methods' => ['GET', 'HEAD'],
                'domain' => null,
                'middleware' => [],
            ],
            'postComments.index' => [
                'uri' => 'posts/{post}/comments',
                'methods' => ['GET', 'HEAD'],
                'domain' => null,
                'middleware' => [],
            ],
            'home' => [
                'uri' => 'home',
                'methods' => ['GET', 'HEAD'],
                'domain' => null,
                'middleware' => [],
            ],
            'posts.show' => [
                'uri' => 'posts/{post}',
                'methods' => ['GET', 'HEAD'],
                'domain' => null,
                'middleware' => [],
            ],
            'posts.store' => [
                'uri' => 'posts',
                'methods' => ['POST'],
                'domain' => null,
                'middleware' => ['auth', 'role:admin'],
            ],
            'admin.users.index' => [
                'uri' => 'admin/users',
                'methods' => ['GET', 'HEAD'],
                'domain' => null,
                'middleware' => ['role:admin'],
            ],
        ];

        $this->assertEquals($expected, $routes->toArray());
    }

    /** @test */
    public function retrieves_only_configured_middleware()
    {
        app()['config']->set('ziggy', [
            'middleware' => ['auth'],
        ]);

        $routes = RoutePayload::compile($this->router);

        $expected = [
            'posts.index' => [
                'uri' => 'posts',
                'methods' => ['GET', 'HEAD'],
                'domain' => null,
                'middleware' => [],
            ],
            'postComments.index' => [
                'uri' => 'posts/{post}/comments',
                'methods' => ['GET', 'HEAD'],
                'domain' => null,
                'middleware' => [],
            ],
            'home' => [
                'uri' => 'home',
                'methods' => ['GET', 'HEAD'],
                'domain' => null,
                'middleware' => [],
            ],
            'posts.show' => [
                'uri' => 'posts/{post}',
                'methods' => ['GET', 'HEAD'],
                'domain' => null,
                'middleware' => [],
            ],
            'posts.store' => [
                'uri' => 'posts',
                'methods' => ['POST'],
                'domain' => null,
                'middleware' => ['auth'],
            ],
            'admin.users.index' => [
                'uri' => 'admin/users',
                'methods' => ['GET', 'HEAD'],
                'domain' => null,
                'middleware' => [],
            ],
        ];

        $this->assertEquals($expected, $routes->toArray());
    }
}
