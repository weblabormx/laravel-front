<?php

namespace WeblaborMx\Front;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Str;
use WeblaborMx\Front\Http\Controllers\PageController;
use Opis\Closure\SerializableClosure;
use WeblaborMx\Front\Console\Commands\CreateResource;
use WeblaborMx\Front\Console\Commands\CreatePage;
use WeblaborMx\Front\Console\Commands\Install;
use WeblaborMx\Front\Console\Commands\CreateFilter;
use Carbon\Carbon;
use DateTime;

class FrontServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([__DIR__.'/../config/front.php' => config_path('front.php')], 'config');
        $this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/front'),
        ]);

        $this->mergeConfigFrom(__DIR__.'/../config/front.php', 'front');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'front');
        $this->registerRoutes();
        SerializableClosure::addSecurityProvider(new SecurityProvider);
        $this->loadInputs();
        
    }

    /**
     * Register the package routes.
     *
     * @return void
     */
    protected function registerRoutes()
    {
        Route::macro('front', function ($model, $plural = null) {
            $singular = strtolower(Str::snake($model));
            if(is_null($plural)) {
                $plural = Str::plural($singular);    
            }

            Route::group(['prefix' => $plural, 'namespace' => '\WeblaborMx\Front\Http\Controllers'], function () {
                Route::get('/', 'FrontController@index');
                Route::get('create', 'FrontController@create');
                Route::post('/', 'FrontController@store');
                Route::get('search', 'FrontController@search');
                Route::get('action/{front_action}', 'FrontController@indexActionShow');
                Route::post('action/{front_action}', 'FrontController@indexActionStore');
                Route::get('lenses/{front_lense}', 'FrontController@lenses');
                Route::get('{front_object}', 'FrontController@show');
                Route::get('{front_object}/edit', 'FrontController@edit');
                Route::put('{front_object}', 'FrontController@update');
                Route::delete('{front_object}', 'FrontController@destroy');
                Route::get('{front_object}/action/{front_action}', 'FrontController@actionShow');
                Route::post('{front_object}/action/{front_action}', 'FrontController@actionStore');
                Route::get('{front_object}/masive_edit/{front_key}', 'FrontController@massiveEditShow');
                Route::post('{front_object}/masive_edit/{front_key}', 'FrontController@massiveEditStore');
            });
        });

        Route::macro('page', function ($model, $route = null) {
            $singular = strtolower(Str::snake($model));
            $route = $route ?? $singular;
            Route::get($route, function() use ($model) {
                return (new PageController)->page($model);
            });
            Route::post($route, function() use ($model) {
                return (new PageController)->page($model, 'post');
            });
            Route::put($route, function() use ($model) {
                return (new PageController)->page($model, 'put');
            });
            Route::delete($route, function() use ($model) {
                return (new PageController)->page($model, 'delete');
            });
        });

        Blade::directive('active', function ($route) {
            return "<?php if(request()->is('$route/*') || request()->is('$route')) echo 'active';?>";
        });

        Blade::directive('active_exact', function ($route) {
            return "<?php if(request()->is('$route')) echo 'active';?>";
        });

        $this->app->make('form')->considerRequest(true);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        if (! defined('WLFRONT_PATH')) {
            define('WLFRONT_PATH', realpath(__DIR__.'/../'));
        }

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
     * Register the laravel collective created inputs
     *
     * @return void
     */
    public function loadInputs()
    {
        \Form::macro('frontDatetime', function($name, $value = null, $options = [])
        {
            $value = \Form::getValueAttribute($name, $value);
            if(!is_null($value) && !$value instanceof DateTime) {
                $value = Carbon::parse($value);
            }
            return \Form::datetimeLocal($name, $value, $options);;
        });
    }
}
