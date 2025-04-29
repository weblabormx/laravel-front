<?php

namespace WeblaborMx\Front\Console\Commands;

use Illuminate\Console\Command;
use WeblaborMx\FileModifier\FileModifier;
use Illuminate\Support\Str;

class CreatePage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'front:page {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a page for Laravel Front';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $directory = WLFRONT_PATH . '/install-stubs';
        $name = $this->argument('name');

        // Create Front Folder if doesnt exist
        if (! is_dir(app_path('Front'))) {
            mkdir(app_path('Front'));
        }

        // Create Front/Page folder if doesnt exist
        if (! is_dir(app_path('Front/Pages'))) {
            mkdir(app_path('Front/Pages'));
        }

        // Create resource base
        $file_name = app_path('Front/Pages/Page.php');
        if (!FileModifier::file($file_name)->exists()) {
            copy($directory . '/base-page.php', $file_name);
        }

        // Create resource
        $file_name = app_path('Front/Pages/' . $name . '.php');
        if (FileModifier::file($file_name)->exists()) {
            $this->line('Page already exists.');
            return;
        }
        copy($directory . '/page.php', $file_name);

        FileModifier::file($file_name)
            ->replace('{name}', $name)
            ->replace('{slug}', Str::slug(Str::plural($name)))
            ->execute();

        $this->line('Page created: <info>✔</info>');
    }
}
