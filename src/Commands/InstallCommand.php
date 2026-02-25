<?php

namespace PictaStudio\Contento\Commands;

use Illuminate\Console\Command;

use function Laravel\Prompts\confirm;

class InstallCommand extends Command
{
    protected $signature = 'contento:install';

    protected $description = 'Install Contento package';

    public function handle(): int
    {
        $this->components->info('Installing Contento package...');

        $this->components->info('Publishing contento configuration...');
        $this->call('vendor:publish', ['--tag' => 'contento-config']);

        $this->components->info('Publishing contento migrations...');
        $this->call('vendor:publish', ['--tag' => 'contento-migrations']);

        if (confirm('Do you want to publish bruno api files?', false)) {
            $this->components->info('Publishing bruno api files...');
            $this->call('vendor:publish', ['--tag' => 'contento-bruno']);
        }

        if (confirm('Do you want to run migrations now?')) {
            $this->call('migrate');
        }

        $this->components->info('Contento package installed successfully.');

        return self::SUCCESS;
    }
}
