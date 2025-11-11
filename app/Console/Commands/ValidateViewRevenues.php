<?php

namespace App\Console\Commands;

use App\Services\NoteViewMonetizationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ValidateViewRevenues extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'views:validate {--limit=100 : Number of views to validate per run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Validate pending view revenues and mark as approved or rejected';

    /**
     * Execute the console command.
     */
    public function handle(NoteViewMonetizationService $monetizationService): int
    {
        $limit = (int) $this->option('limit');
        
        $this->info("Validating pending view revenues (limit: {$limit})...");
        
        $validated = $monetizationService->validatePendingViews($limit);
        
        $this->info("Validated {$validated} view revenues.");
        
        Log::info('View revenues validated', [
            'count' => $validated,
            'limit' => $limit,
        ]);
        
        return Command::SUCCESS;
    }
}

