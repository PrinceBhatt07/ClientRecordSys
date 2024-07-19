<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ClearBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will clear the previous backup sql file';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */

    public function handle()
    {
        $backupDirectory = storage_path('app/backup');
        $files = File::files($backupDirectory);

        $sevenDaysAgo = Carbon::now()->subDays(7);

        foreach ($files as $file) {
            $filePath = $file->getPathname();
            $fileModificationTime = Carbon::createFromTimestamp(filemtime($filePath));

            if ($fileModificationTime->lessThan($sevenDaysAgo)) {
                File::delete($filePath);
                $this->info("Deleted: $filePath");
            }
        }

        $this->info('Old backup files have been deleted successfully.');
    }
}
