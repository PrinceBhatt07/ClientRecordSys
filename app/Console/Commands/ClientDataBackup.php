<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ClientDataBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:client-data-backup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command To Backup Client Data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!Storage::exists('backup')) {
            Storage::makeDirectory('backup');
        }
    
        $fileName = "backup-" . Carbon::now()->format('Y-m-d') . ".sql";
        $filePath = storage_path('app/backup/' . $fileName);
    
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');
        $database = config('database.connections.mysql.database');
    
        $command = sprintf(
            'mysqldump --user=%s --password=%s --host=%s %s > %s',
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($host),
            escapeshellarg($database),
            escapeshellarg($filePath)
        );
    
        $output = null;
        $returnVar = null;
        exec($command, $output, $returnVar);
    
        if ($returnVar === 0) {
            $this->info('Backup created successfully.');
        } else {
            $this->error('Failed to create backup.');
        }
    }
}
