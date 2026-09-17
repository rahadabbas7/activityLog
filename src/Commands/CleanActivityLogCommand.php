<?php

namespace Rahat\ActivityLog\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Rahat\ActivityLog\Models\ActivityLog;

class CleanActivityLogCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'activitylog:clean
                            {--date= : The specific date to delete activity logs for (YYYY-MM-DD)}
                            {--days= : Delete activity logs older than specified number of days}
                            {--force : Force the operation without confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean and prune activity log records from the database';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $modelClass = config('activitylog.model', ActivityLog::class);
        $date = $this->option('date');
        $days = $this->option('days');
        $force = $this->option('force');

        // 1. If a specific date is provided via CLI option
        if ($date) {
            return $this->deleteByDate($modelClass, $date, $force);
        }

        // 2. If days threshold is provided via CLI option
        if ($days !== null) {
            return $this->deleteByDays($modelClass, (int) $days, $force);
        }

        // 3. Interactive Mode: Ask the user what they want to do
        $this->info('Activity Log Cleanup Wizard');

        $retentionDays = config('activitylog.delete_records_older_than_days', 365);
        $retentionLabel = $retentionDays ? "Delete logs older than {$retentionDays} days (configured retention)" : 'Delete logs older than retention';

        $choice = $this->choice(
            'What would you like to clean?',
            [
                '1' => 'Enter a specific date to delete',
                '2' => $retentionLabel,
                '3' => 'Cancel',
            ],
            '1'
        );

        if ($choice === '1' || $choice === 'Enter a specific date to delete') {
            $inputDate = $this->ask('Please enter the date to delete (format: YYYY-MM-DD)', Carbon::today()->toDateString());
            return $this->deleteByDate($modelClass, $inputDate, $force);
        }

        if ($choice === '2' || str_contains($choice, 'retention')) {
            $daysToUse = $retentionDays ?: (int) $this->ask('Enter number of days threshold:', '30');
            return $this->deleteByDays($modelClass, $daysToUse, $force);
        }

        $this->line('Operation cancelled.');
        return self::SUCCESS;
    }

    /**
     * Delete logs for a specific date.
     */
    protected function deleteByDate(string $modelClass, string $date, bool $force): int
    {
        try {
            $parsedDate = Carbon::createFromFormat('Y-m-d', trim($date))->toDateString();
        } catch (\Exception $e) {
            $this->error("Invalid date format [{$date}]. Please use YYYY-MM-DD format (e.g. " . Carbon::today()->toDateString() . ').');
            return self::FAILURE;
        }

        $count = $modelClass::whereDate('created_at', $parsedDate)->count();

        if ($count === 0) {
            $this->warn("No activity log records found for date [{$parsedDate}].");
            return self::SUCCESS;
        }

        if (! $force && ! $this->confirm("Are you sure you want to permanently delete all {$count} activity log(s) for [{$parsedDate}]?", false)) {
            $this->line('Operation cancelled.');
            return self::SUCCESS;
        }

        $deleted = $modelClass::whereDate('created_at', $parsedDate)->delete();

        $this->info("Successfully deleted {$deleted} activity log record(s) for date [{$parsedDate}].");
        return self::SUCCESS;
    }

    /**
     * Delete logs older than given days.
     */
    protected function deleteByDays(string $modelClass, int $days, bool $force): int
    {
        if ($days < 0) {
            $this->error('Days value must be greater than or equal to 0.');
            return self::FAILURE;
        }

        $cutoff = Carbon::now()->subDays($days);
        $count = $modelClass::where('created_at', '<=', $cutoff)->count();

        if ($count === 0) {
            $this->warn("No activity log records found older than {$days} days (cutoff: {$cutoff->toDateString()}).");
            return self::SUCCESS;
        }

        if (! $force && ! $this->confirm("Are you sure you want to permanently delete {$count} activity log(s) older than {$days} days (before {$cutoff->toDateTimeString()})?", false)) {
            $this->line('Operation cancelled.');
            return self::SUCCESS;
        }

        $deleted = $modelClass::where('created_at', '<=', $cutoff)->delete();

        $this->info("Successfully pruned {$deleted} old activity log record(s).");
        return self::SUCCESS;
    }
}
