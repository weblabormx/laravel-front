<?php

namespace WeblaborMx\Front\Console\Commands;

use Illuminate\Console\Command;
use WeblaborMx\FileModifier\FileModifier;
use Illuminate\Support\Str;

class CreateFilter extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'front:filter {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a filter for Laravel Front';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $directory = WLFRONT_PATH . '/install-stubs';
        $name = $this->argument('name');

        // Create Front/Filters folder if doesnt exist
        if (! is_dir(app_path('Front/Filters'))) {
            mkdir(app_path('Front/Filters'));
            $this->line('Filters folder created: <info>✔</info>');
        }

        // Create filter base
        $file_name = app_path('Front/Filters/Filter.php');
        if (!FileModifier::file($file_name)->exists()) {
            copy($directory . '/base-filter.php', $file_name);
            $this->line('Base Filter class created: <info>✔</info>');
        }

        // Create search filter base
        $file_name = app_path('Front/Filters/SearchFilter.php');
        if (!FileModifier::file($file_name)->exists()) {
            copy($directory . '/search-filter.php', $file_name);
            $this->line('Search Filter added on filters folder: <info>✔</info>');
        }

        // Create resource
        $file_name = app_path('Front/Filters/' . $name . '.php');
        if (FileModifier::file($file_name)->exists()) {
            $this->line('Filter already exists.');
            return;
        }
        copy($directory . '/filter.php', $file_name);

        FileModifier::file($file_name)
            ->replace('{name}', $name)
            ->replace('{slug}', Str::slug(Str::plural($name)))
            ->execute();

        $this->line('Filter created: <info>✔</info>');
    }
}
