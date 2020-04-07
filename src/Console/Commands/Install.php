<?php

namespace WeblaborMx\Front\Console\Commands;

use Illuminate\Console\Command;
use WeblaborMX\FileModifier\FileModifier;
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
        \Log::info(\Artisan::call('vendor:publish', [
            '--provider' => "WeblaborMx\Front\FrontServiceProvider",
        ]));
        $this->line('Configuration files published: <info>✔</info>');

        $directory = WLFRONT_PATH.'/install-stubs';

        // Create Front Folder if doesnt exist
        if (! is_dir(resource_path('views/front'))) {
            mkdir(resource_path('views/front'));
        }

        // Copy sidebar
        $file_name = resource_path('views/front/sidebar.blade.php');
        if(FileModifier::file($file_name)->exists()) {
            $this->line('Sidebar already exists.');
        } else {
            copy($directory.'/sidebar.blade.php', $file_name);
            $this->line('Sidebar added: <info>✔</info>');
        }

        // Copy all files
        (new Filesystem)->copyDirectory(
            WLFRONT_PATH.'/install-stubs/public', public_path('')
        );
        $this->line('Assets added: <info>✔</info>');
    }
}
