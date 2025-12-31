<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SyncLiteLLMLogsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'litellm:sync-logs {--days=30 : Number of days to sync}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync logs from LiteLLM to Laravel database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting LiteLLM logs sync...');
        
        try {
            $job = new \App\Jobs\SyncLiteLLMLogs();
            $job->handle();
            
            $this->info('Sync completed successfully!');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Sync failed: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            return Command::FAILURE;
        }
    }
}
