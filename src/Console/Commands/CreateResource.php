<?php

namespace WeblaborMx\Front\Console\Commands;

use Illuminate\Console\Command;
use WeblaborMX\FileModifier\FileModifier;

class CreateResource extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'front:resource
                            {model : The class name of the model from which to create the resource}
                            {--a|all : Generate a resource with the model, policy, migration}
                            {--m|model : Create a new model and migration for the resource}
                            {--p|policy : Create a new policy for the model of the resource}';

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
        $this->createFrontDir();
        $this->createResourceParent();

        $model = str($this->argument('model'))->replace('/', '\\');

        $class = $model
            ->prepend(config('front.models_folder') . '\\')
            ->replace('App\\Models', '')
            ->replace('App\\', '\\')
            ->trim('/')
            ->trim('\\');

        $classBasename = $class->classBasename();
        $classVendor = $class->beforeLast("{$classBasename}")->trim('\\');

        $filename = $classVendor
            ->replace('\\', '/')
            ->append("/{$classBasename}")
            ->append('.php')
            ->toString();
        $filename = $this->resolveFrontDir($filename);

        $slug = $classBasename->snake()->plural();

        if (is_file($filename)) {
            $this->error($class . ' already exists.');
            return;
        }

        if ($this->option('all')) {
            $this->input->setOption('policy', true);
            $this->input->setOption('model', true);
        }

        if ($this->option('model')) {
            $this->call('make:model', ['name' => $class]);
            $this->call('make:migration', ['name' => "create_{$slug}_table"]);
        }

        if ($this->option('policy')) {
            $this->call('make:policy', [
                'name' => "{$classBasename}Policy",
                '--model' => $class
            ]);
        }

        $parent = config('front.resources_folder') . '\\Resource';

        $namespace =  collect([
            config('front.resources_folder'),
            $classVendor->toString(),
        ])->filter()->implode('\\');

        $url = $classVendor->lower()
            ->replace('\\', '/')
            ->append($slug)
            ->trim('/');

        if (!is_dir($dirname = dirname($filename))) {
            mkdir($dirname, 0755, true);
        }

        copy($this->resolveStubPath('/resource.stub'), $filename);

        FileModifier::file($filename)
            ->replace('{{ model }}', "App\\Models\\$class")
            ->replace('{{ class }}', $classBasename)
            ->replace('{{ parent }}', $parent)
            ->replace('{{ default_base_url }}', rtrim(config('front.default_base_url'), '/'))
            ->replace('{{ url }}', $url)
            ->replace('{{ namespace }}', $namespace)
            ->execute();

        $this->components->info(sprintf('Resource [%s] created successfully.', $this->prettifyPath($filename)));
    }

    protected function prettifyPath($path)
    {
        return str($path)->after(base_path())->trim('/');
    }

    protected function resolveFrontDir($path = '')
    {
        $vendor = str(config('front.resources_folder'));
        return str(base_path(
            $vendor
                ->after('\\')
                ->prepend(
                    $vendor->before('\\')
                        ->snake()
                        ->append('\\')
                )
                ->replace('\\', '/')
        ))->append('/' . ltrim($path, '/'))
            ->rtrim('/')
            ->toString();
    }

    protected function resolveStubPath($stub)
    {
        $stub = trim($stub, '/');
        return file_exists($customPath = $this->laravel->basePath('/stubs/' . $stub))
            ? $customPath
            : __DIR__ . '/stubs/' . $stub;
    }

    protected function createFrontDir()
    {
        $path = $this->resolveFrontDir();

        if (!is_dir($path)) {
            mkdir($path, 0755, true);
            $this->components->info(sprintf('Folder [%s] created successfully.', $this->prettifyPath($path)));
        }
    }

    protected function createResourceParent()
    {
        $path = $this->resolveFrontDir('/Resource.php');
        $directory = WLFRONT_PATH . '/install-stubs';

        if (!FileModifier::file($path)->exists()) {
            copy($directory . '/base-resource.php', $path);
            $this->components->info(sprintf('Class [%s] created successfully.', $this->prettifyPath($path)));
        }
    }
}
