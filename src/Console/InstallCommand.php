<?php

declare(strict_types=1);

namespace RodeoPHP\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallCommand extends Command
{
    protected $signature = 'rodeo:install {--force : Overwrite any previously published files}';

    protected $description = 'Install RodeoPHP: publish config and panel assets';

    public function handle(): int
    {
        $this->call('vendor:publish', [
            '--tag' => 'rodeo-config',
            '--force' => (bool) $this->option('force'),
        ]);

        $this->call('vendor:publish', ['--tag' => 'rodeo-assets', '--force' => true]);

        File::ensureDirectoryExists(app_path('Rodeo'));

        $this->offerComposerHook();

        $this->components->info('RodeoPHP installed. Create your first resource:');
        $this->line('  php artisan rodeo:resource HorseResource --model=Horse');

        return self::SUCCESS;
    }

    protected function offerComposerHook(): void
    {
        if (! $this->input->isInteractive()) {
            return;
        }

        $path = base_path('composer.json');

        if (! File::exists($path)) {
            return;
        }

        $composer = json_decode(File::get($path), true);

        if (! is_array($composer)) {
            return;
        }

        $hooks = $composer['scripts']['post-update-cmd'] ?? [];

        if (in_array('@php artisan rodeo:upgrade', $hooks, true)) {
            return;
        }

        if (! $this->confirm('Keep panel assets fresh automatically? (adds rodeo:upgrade to composer post-update-cmd)', false)) {
            return;
        }

        $composer['scripts']['post-update-cmd'] = [...$hooks, '@php artisan rodeo:upgrade'];

        File::put($path, json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL);
        $this->components->info('Added rodeo:upgrade to composer post-update-cmd.');
    }
}
