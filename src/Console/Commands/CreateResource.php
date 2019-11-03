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
        /*
        Verificar que se cree un resource, falta poner el template e instalar file modifier
        */
        $name = $this->argument('name');

        if (! is_dir(app_path('Front'))) {
            mkdir(app_path('Front'));
        }

        $file_name = app_path('Front/'.$name.'.php');
        copy($directory.'/resource.php', $file_name);

        FileModifier::file($file_name)
            ->replace('{name}', $name)
            ->replace('{slug}', Str::slug(Str::plural($name)))
            ->execute();
    }
}
