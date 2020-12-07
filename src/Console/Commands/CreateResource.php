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
    protected $signature = 'front:resource {name} {--all}';

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
        $name = $this->argument('name');
        
        // Create Front Folder if doesnt exist
        $dir = base_path(str_replace('App', 'app', config('front.resources_folder')));
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
        $file_name = $dir.'\Resource.php';
        if(!FileModifier::file($file_name)->exists()) {
            copy($directory.'/base-resource.php', $file_name);
            $this->line('Resource base class created: <info>✔</info>');
        }

        // Create resource
        $file_name = $dir.'\\'.$name.'.php';
        if(!FileModifier::file($file_name)->exists()) {
            copy($directory.'/resource.php', $file_name);

            FileModifier::file($file_name)
                ->replace('{name}', $name)
                ->replace('{model_folder}', config('front.models_folder'))
                ->replace('{default_base_url}', config('front.default_base_url'))
                ->replace('{slug}', Str::plural(Str::snake($name)))
                ->execute();

            $this->line('Resource created: <info>✔</info>');
        }

        $all = $this->option('all');
        $model = trim(trim(str_replace('App', '', config('front.models_folder').'/'.$name), '/'), '\\');
        if($all) {
            try {
                \Artisan::call("make:model {$model} -m");
                $this->line('Model created: <info>✔</info>');
                $this->line('Migration created: <info>✔</info>');
            } catch (\Exception $e) {
                
            }
            \Artisan::call("make:policy {$name}Policy --model={$model}");
            $this->line('Policy created: <info>✔</info>');
        }
    }
}
