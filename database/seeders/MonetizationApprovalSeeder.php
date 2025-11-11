<?php

namespace Database\Seeders;

use App\Models\Note;
use Illuminate\Database\Seeder;

class MonetizationApprovalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * This seeder runs after TransactionSeeder to auto-approve monetization
     * for free notes whose sellers have at least 1 successful sale.
     */
    public function run(): void
    {
        // Get all free notes that are not yet approved
        $freeNotes = Note::where('price', 0)
            ->where(function($query) {
                $query->where('monetization_approved', false)
                      ->where('monetization_auto_approved', false);
            })
            ->get();

        $approved = 0;
        foreach ($freeNotes as $note) {
            if ($note->checkAndAutoApproveMonetization()) {
                $approved++;
            }
        }

        $this->command->info("Auto-approved monetization for {$approved} free notes.");
    }
}

