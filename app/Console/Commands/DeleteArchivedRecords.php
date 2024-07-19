<?php

namespace App\Console\Commands;

use App\Models\Client;
use Illuminate\Console\Command;

class DeleteArchivedRecords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'action:delete-archived-records';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete records that have been archived for more than 90 Days.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $ninetyDaysAgo = now()->subDays(90);

        Client::where('is_archived',true)
            ->where('archived_at', '<', $ninetyDaysAgo)
            ->delete();

        $this->info('Archived records older than 90 Days have been deleted.');
    }
}
