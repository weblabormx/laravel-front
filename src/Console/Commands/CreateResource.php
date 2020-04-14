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
    protected $signature = 'front:resource {name}';

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
        if (! is_dir(app_path('Front'))) {
            mkdir(app_path('Front'));
            $this->line('Front folder created: <info>✔</info>');
        }

        // Create resource base
        $file_name = app_path('Front/Resource.php');
        if(!FileModifier::file($file_name)->exists()) {
            copy($directory.'/base-resource.php', $file_name);
            $this->line('Resource base class created: <info>✔</info>');
        }

        // Create resource
        $file_name = app_path('Front/'.$name.'.php');
        if(FileModifier::file($file_name)->exists()) {
            $this->line('Resource already exists.');
            return;
        }
        copy($directory.'/resource.php', $file_name);

        FileModifier::file($file_name)
            ->replace('{name}', $name)
            ->replace('{model_folder}', config('front.models_folder'))
            ->replace('{default_base_url}', config('front.default_base_url'))
            ->replace('{slug}', Str::plural(Str::snake($name)))
            ->execute();

        $this->line('Resource created: <info>✔</info>');
    }
}
