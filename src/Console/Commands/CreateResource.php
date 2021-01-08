<?php

namespace WeblaborMx\Front\Console\Commands;

use Illuminate\Console\Command;
use WeblaborMX\FileModifier\FileModifier;
use Illuminate\Support\Str;

class CreateResource extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'front:resource {dir_location} {--all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a resource for Laravel Front';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $directory = WLFRONT_PATH.'/install-stubs';
        $dir_location = $this->argument('dir_location');
        $model = str_replace('/', '\\', $dir_location);
        $model_name = class_basename($model);
        $model_extra_path = str_replace('\\'.$model_name, '', $model);
        if(strlen($model_extra_path)>0) {
            $model_extra_path = '\\'.$model_extra_path;
        }
        $slug = Str::plural(Str::snake($model_name));
        $url = strtolower(str_replace($model_name, $slug, $dir_location));

        // Create Front Folder if doesnt exist
        $dir = str_replace('\\', '/', base_path(str_replace('App', 'app', config('front.resources_folder'))));
        if (!is_dir(app_path('Front'))) {
            mkdir(app_path('Front'));
            $this->line('Front folder created: <info>✔</info>');
        }

        // Create resources front folder
        if(app_path('Front')!=$dir && !is_dir($dir)) {
            mkdir($dir);
            $this->line('Front resources folder created: <info>✔</info>');
        }

        // Create resource base
        $file_name = $dir.'/Resource.php';
        if(!FileModifier::file($file_name)->exists()) {
            copy($directory.'/base-resource.php', $file_name);
            $this->line('Resource base class created: <info>✔</info>');
        }

        // Create resource
        $file_name = $dir.'/'.$dir_location.'.php';
        if(!FileModifier::file($file_name)->exists()) {
            copy($directory.'/resource.php', $file_name);

            FileModifier::file($file_name)
                ->replace('{model}', $model)
                ->replace('{model_name}', $model_name)
                ->replace('{model_folder}', config('front.models_folder'))
                ->replace('{default_base_url}', config('front.default_base_url'))
                ->replace('{url}', $url)
                ->replace('{model_extra_path}', $model_extra_path)
                ->replace('{resources_folder}', config('front.resources_folder'))
                ->execute();

            $this->line('Resource created: <info>✔</info>');
        }

        $all = $this->option('all');
        if($all) {
            $var = config('front.models_folder').'\\'.$model;
            $var = str_replace('App\Models', '', $var);
            $var = str_replace('App', '', $var);
            $var = trim($var, '/');
            $var = trim($var, '\\');
            $var = str_replace('\\', '/', $var);
            
            try {
                \Artisan::call("make:model {$var} -m");
                $this->line('Model created: <info>✔</info>');
                $this->line('Migration created: <info>✔</info>');
            } catch (\Exception $e) {
                
            }
            \Artisan::call("make:policy {$model_name}Policy --model={$var}");
            $this->line('Policy created: <info>✔</info>');
        }
    }
}
