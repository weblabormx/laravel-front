<?php

namespace WeblaborMx\Front;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Str;
use WeblaborMx\Front\Http\Controllers\PageController;
use Opis\Closure\SerializableClosure;

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
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'front');
        $this->registerRoutes();
        SerializableClosure::addSecurityProvider(new SecurityProvider);
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

            Route::group(['prefix' => $plural, 'namespace' => '\WeblaborMx\Front\Http\Controllers'], function () use ($singular) {
                Route::get('/', 'FrontController@index');
                Route::get('create', 'FrontController@create');
                Route::post('/', 'FrontController@store');
                Route::get('search', 'FrontController@search');
                Route::get('action/{action}', 'FrontController@indexActionShow');
                Route::post('action/{action}', 'FrontController@indexActionStore');
                Route::get('lenses/{lense}', 'FrontController@lenses');
                Route::get('{'.$singular.'}', 'FrontController@show');
                Route::get('{'.$singular.'}/edit', 'FrontController@edit');
                Route::put('{'.$singular.'}', 'FrontController@update');
                Route::delete('{'.$singular.'}', 'FrontController@destroy');
                Route::get('{'.$singular.'}/action/{action}', 'FrontController@actionShow');
                Route::post('{'.$singular.'}/action/{action}', 'FrontController@actionStore');
            });
        });

        Route::macro('page', function ($model, $route = null) {
            $singular = strtolower(Str::snake($model));
            $route = $route ?? $singular;
            Route::get($route, function() use ($model) {
                return (new PageController)->page($model);
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
}
