<?php

declare(strict_types=1);

namespace SaddlePHP\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallCommand extends Command
{
    protected $signature = 'saddle:install {--force : Overwrite any previously published files}';

    protected $description = 'Install Saddle: publish config and panel assets';

    public function handle(): int
    {
        $this->call('vendor:publish', [
            '--tag' => 'saddle-config',
            '--force' => (bool) $this->option('force'),
        ]);

        $this->call('vendor:publish', ['--tag' => 'saddle-assets', '--force' => true]);

        File::ensureDirectoryExists(app_path('Saddle'));

        $this->offerComposerHook();

        $this->components->info('Saddle installed. Create your first resource:');
        $this->line('  php artisan saddle:resource HorseResource --model=Horse');

        return self::SUCCESS;
    }

    protected function offerComposerHook(): void
    {
        if (! $this->input->isInteractive()) {
            $this->line('  <fg=yellow>Note:</> In deploy scripts, run <fg=cyan>php artisan saddle:upgrade</> after composer updates to keep panel assets current.');

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

        if (in_array('@php artisan saddle:upgrade', $hooks, true)) {
            return;
        }

        if (! $this->confirm('Keep panel assets fresh automatically? (adds saddle:upgrade to composer post-update-cmd)', false)) {
            return;
        }

        $composer['scripts']['post-update-cmd'] = [...$hooks, '@php artisan saddle:upgrade'];

        File::put($path, json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL);
        $this->components->info('Added saddle:upgrade to composer post-update-cmd.');
    }
}
