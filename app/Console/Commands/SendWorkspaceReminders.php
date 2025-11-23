<?php

namespace App\Console\Commands;

use App\Services\WorkspaceWorkflowService;
use Illuminate\Console\Command;

class SendWorkspaceReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'workspaces:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send due reminders for workspace tasks and notes';

    /**
     * Execute the console command.
     */
    public function handle(WorkspaceWorkflowService $workflowService): int
    {
        $this->info('Sending workspace reminders...');

        $sent = $workflowService->sendDueReminders();

        $this->info("Successfully sent {$sent} reminder(s).");

        return Command::SUCCESS;
    }
}

