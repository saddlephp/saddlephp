<?php

declare(strict_types=1);

namespace SaddlePHP\Console;

use Illuminate\Console\Command;

class UpgradeCommand extends Command
{
    protected $signature = 'saddle:upgrade';

    protected $description = 'Republish the compiled panel assets';

    public function handle(): int
    {
        $this->call('vendor:publish', ['--tag' => 'saddle-assets', '--force' => true]);

        $this->components->info('Panel assets refreshed.');

        return self::SUCCESS;
    }
}
