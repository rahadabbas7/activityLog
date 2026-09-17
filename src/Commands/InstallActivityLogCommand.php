<?php

namespace Rahad\ActivityLog\Commands;

use Illuminate\Console\Command;

class InstallActivityLogCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'activitylog:install
                            {--views : Also publish customizable Blade views}
                            {--force : Overwrite existing published files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install and configure the Rahad ActivityLog package';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Installing Rahad ActivityLog Package...');

        // 1. Publish Configuration
        $this->comment('Publishing configuration...');
        $this->call('vendor:publish', [
            '--tag' => 'activitylog-config',
            '--force' => $this->option('force'),
        ]);

        // 2. Publish Migrations
        $this->comment('Publishing database migrations...');
        $this->call('vendor:publish', [
            '--tag' => 'activitylog-migrations',
            '--force' => $this->option('force'),
        ]);

        // 3. Publish Views (if requested or prompted)
        if ($this->option('views') || $this->confirm('Would you like to publish the Blade views for full customization?', false)) {
            $this->comment('Publishing Blade views...');
            $this->call('vendor:publish', [
                '--tag' => 'activitylog-views',
                '--force' => $this->option('force'),
            ]);
        }

        // 4. Ask to run migrations
        if ($this->confirm('Would you like to run the database migrations now?', true)) {
            $this->comment('Running migrations...');
            $this->call('migrate');
        }

        $this->newLine();
        $this->info('Rahad ActivityLog successfully installed! 🚀');
        $this->line('Web Dashboard: ' . url(config('activitylog.web.route_prefix', 'activity-logs')));

        return self::SUCCESS;
    }
}
