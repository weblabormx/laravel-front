<?php

namespace WeblaborMx\Front;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use WeblaborMx\Front\Http\Controllers\PageController;
use Opis\Closure\SerializableClosure;
use WeblaborMx\Front\Console\Commands\CreateResource;
use WeblaborMx\Front\Console\Commands\CreatePage;
use WeblaborMx\Front\Console\Commands\Install;
use WeblaborMx\Front\Console\Commands\CreateFilter;
use WeblaborMx\Front\Http\Controllers\FrontController;
use Illuminate\Http\Request;
use WeblaborMx\Front\ButtonManager;
use WeblaborMx\Front\Facades\Front;
use WeblaborMx\Front\ThumbManager;

class FrontServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        if (! defined('WLFRONT_PATH')) {
            define('WLFRONT_PATH', realpath(__DIR__ . '/../'));
        }

        $this->registerFront();

        if ($this->app->runningInConsole()) {
            $this->commands([
                CreateResource::class,
                CreatePage::class,
                Install::class,
                CreateFilter::class
            ]);
        }
    }

    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([__DIR__ . '/../config/front.php' => config_path('front.php')], 'config');
        $this->publishes([
            __DIR__ . '/../resources/views' => base_path('resources/views/vendor/front'),
        ]);

        $this->mergeConfigFrom(__DIR__ . '/../config/front.php', 'front');

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'front');
        $this->registerRoutes();
        $this->registerBladeDirectives();
        SerializableClosure::addSecurityProvider(new SecurityProvider());
    }

    /**
     * Register Front Facade
     *
     * @return void
     */
    protected function registerFront()
    {
        $this->app->singleton(ButtonManager::class);
        $this->app->singleton(ThumbManager::class);
        $this->app->singleton('Front', Front::class);
        $loader = AliasLoader::getInstance();

        $loader->alias('Front', Front::class);
    }

    /**
     * Register the package routes.
     *
     * @return void
     */
    protected function registerRoutes()
    {
        $provider = $this;
        Route::macro('front', function ($model) use ($provider) {
            $class = Front::registerResource($model);
            $front = Front::makeResource($class);

            $prefix = class_basename($front->base_url);

            return Route::prefix($prefix)->name('front.' . str($prefix)->classBasename()->snake()->lower())->group(function () use ($front, $model, $provider) {
                $controller = new FrontController($model);
                $provider->generateFrontRoutes($front, $controller);
            });
        });

        Route::macro('lense', function ($model) use ($provider) {
            $model = 'Lenses\\' . $model;
            $class = Front::registerResource($model);
            $front = Front::makeResource($class);
            $prefix = $front->base_url;

            return Route::prefix($prefix)->group(function () use ($model, $provider, $front) {
                $controller = new FrontController($model);
                $provider->generateFrontRoutes($front, $controller);
            });
        });

        Route::macro('page', function ($model, $route = null) {
            $model = Front::resolvePage($model);
            $slug = str($model)->classBasename()->snake()->lower()->toString();
            $route = \strval($route) ?? $slug;

            return Route::prefix($route)->name('front.page.' . $slug)->group(function () use ($model) {
                Route::get('/', fn() =>  app(PageController::class)->page($model, 'get'))->name('');
                Route::post('/', fn() =>  app(PageController::class)->page($model, 'post'))->name('.post');
                Route::put('/', fn() =>  app(PageController::class)->page($model, 'put'))->name('.put');
                Route::delete('/', fn() =>  app(PageController::class)->page($model, 'delete'))->name('.delete');
            });
        });

        Route::post('api/laravel-front/upload-image', '\WeblaborMx\Front\Http\Controllers\ToolsController@uploadImage');
    }

    /**
     * Register blade directives
     *
     * @return void
     */
    protected function registerBladeDirectives()
    {
        Blade::directive('active', function ($route) {
            return "<?php if(request()->is('$route/*') || request()->is('$route')) echo 'active';?>";
        });

        Blade::directive('active_exact', function ($route) {
            return "<?php if(request()->is('$route')) echo 'active';?>";
        });

        Blade::directive('var_active', function ($route) {
            return "<?php if(request()->is($route.'/*') || request()->is($route)) echo 'active';?>";
        });

        Blade::if('isactive', function ($route) {
            return request()->is($route . '/*') || request()->is($route);
        });

        Blade::directive('var_active_exact', function ($route) {
            return "<?php if(request()->is($route)) echo 'active';?>";
        });

        Blade::directive('pushonce', function ($expression) {
            $var = '$__env->{"__pushonce_" . md5(__FILE__ . ":" . __LINE__)}';
            return "<?php if(!isset({$var})): {$var} = true; \$__env->startPush({$expression}); ?>";
        });

        Blade::directive('endpushonce', function ($expression) {
            return '<?php $__env->stopPush(); endif; ?>';
        });
    }

    /**
     * Generate Front Routes
     *
     * @return void
     */
    public function generateFrontRoutes($front, $controller)
    {
        $actions = [];

        $actions['index'] = Route::get('/', function (Request $request) use ($controller) {
            return $controller->index();
        })->name('');

        $actions['create'] = Route::get('create', function () use ($controller) {
            return $controller->create();
        })->name('.create');

        Route::post('/', function (Request $request) use ($controller) {
            return $controller->store($request);
        })->name('.store');

        Route::get('search', function (Request $request) use ($controller) {
            return $controller->search($request);
        })->name('.search');

        Route::get('action/{front_action}', function ($front_action) use ($controller) {
            return $controller->indexActionShow($front_action);
        })->name('.index_action');

        Route::post('action/{front_action}', function ($front_action, Request $request) use ($controller) {
            return $controller->indexActionStore($front_action, $request);
        })->name('.index_action.post');

        Route::get('lenses/{front_lense}', function ($front_lense, Request $request) use ($controller) {
            return $controller->lenses($front_lense, $request);
        })->name('.lenses');

        Route::get('massive_edit', function () use ($controller) {
            return $controller->massiveIndexEditShow();
        })->name('.massive_index');

        Route::post('massive_edit', function (Request $request) use ($controller) {
            return $controller->massiveIndexEditStore($request);
        })->name('.massive_index.post');

        $actions['show'] = Route::get('{front_object}', function () use ($controller) {
            return $controller->show($controller->getParameter());
        })->name('.show');

        $actions['edit'] = Route::get('{front_object}/edit', function () use ($controller) {
            return $controller->edit($controller->getParameter());
        })->name('.edit');

        Route::put('{front_object}', function (Request $request) use ($controller) {
            return $controller->update($controller->getParameter(), $request);
        })->name('.update');

        Route::delete('{front_object}', function () use ($controller) {
            return $controller->destroy($controller->getParameter());
        })->name('.destroy');

        Route::get('{front_object}/action/{front_action}', function () use ($controller) {
            return $controller->actionShow($controller->getParameter(), $controller->getParameter('action'));
        })->name('.show_action');

        Route::post('{front_object}/action/{front_action}', function (Request $request) use ($controller) {
            return $controller->actionStore($controller->getParameter(), $controller->getParameter('action'), $request);
        })->name('.show_action.post');

        Route::get('{front_object}/massive_edit/{front_key}', function () use ($controller) {
            return $controller->massiveEditShow($controller->getParameter(), $controller->getParameter('key'));
        })->name('.massive_show');

        Route::post('{front_object}/massive_edit/{front_key}', function (Request $request) use ($controller) {
            return $controller->massiveEditStore($controller->getParameter(), $controller->getParameter('key'), $request);
        })->name('.massive_show.post');

        Route::get('{front_object}/sortable/up', function () use ($controller) {
            return $controller->sortableUp($controller->getParameter());
        })->name('sort.up');

        Route::get('{front_object}/sortable/down', function (Request $request) use ($controller) {
            return $controller->sortableDown($controller->getParameter());
        })->name('sort.down');

        Route::post('{front_object}/sortable', function (Request $request) use ($controller) {
            return $controller->sortable($controller->getParameter(), $request->input('order'), $request->input('start'));
        })->name('sort');


        foreach ($actions as $key => $value) {
            Front::registerRoute($front, $value, $key);
        }
    }

    /**
     * Register the laravel collective created inputs
     *
     * @return void
     */
    public function loadInputs()
    {
        \Form::macro('frontDatetime', function ($name, $value = null, $options = []) {
            $value = \Form::getValueAttribute($name, $value);
            if (!is_null($value) && !$value instanceof DateTime) {
                try {
                    $value = Carbon::parse($value);
                } catch (\Exception $e) {
                    
                }
            }
            return \Form::datetimeLocal($name, $value, $options);;
        });
    }
}
