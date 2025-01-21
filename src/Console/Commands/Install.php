<?php

namespace WeblaborMx\Front\Console\Commands;

use Illuminate\Console\Command;
use WeblaborMx\FileModifier\FileModifier;
use Illuminate\Filesystem\Filesystem;

class Install extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'front:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install Laravel Front';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Publish configuration
        \Artisan::call('vendor:publish', [
            '--provider' => "WeblaborMx\Front\Facades\FrontServiceProvider",
        ]);
        $this->line('Configuration files published: <info>✔</info>');

        $directory = WLFRONT_PATH . '/install-stubs';

        // Create Front Folder on views if doesnt exist
        if (! is_dir(resource_path('views/front'))) {
            mkdir(resource_path('views/front'));
        }

        // Create Front Folder if doesnt exist
        if (! is_dir(app_path('Front'))) {
            mkdir(app_path('Front'));
            $this->line('Front folder created: <info>✔</info>');
        }

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

        // Copy sidebar
        $file_name = resource_path('views/front/sidebar.blade.php');
        if (FileModifier::file($file_name)->exists()) {
            $this->line('Sidebar already exists.');
        } else {
            copy($directory . '/sidebar.blade.php', $file_name);
            $this->line('Sidebar added: <info>✔</info>');
        }

        // Copy all files
        /*(new Filesystem)->copyDirectory(
            WLFRONT_PATH.'/install-stubs/public', public_path('')
        );
        $this->line('Assets added: <info>✔</info>');*/
    }
}
