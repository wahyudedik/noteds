<?php

namespace App\Console\Commands;

use App\Models\MlModel;
use App\Models\Stock;
use App\Services\MLIntegrationService;
use Illuminate\Console\Command;

class MLSelectBestModelsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ml:select-best-models';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Evaluate and select best model for each stock/horizon combination';

    /**
     * Execute the console command.
     */
    public function handle(MLIntegrationService $mlService): int
    {
        $this->info('Selecting best models for each stock/horizon combination...');

        $stocks = Stock::active()->get();
        $horizons = [1, 7, 30];
        $total = $stocks->count() * count($horizons);

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        foreach ($stocks as $stock) {
            foreach ($horizons as $horizon) {
                try {
                    $bestModel = $mlService->selectBestModel($stock->code, $horizon);
                    if ($bestModel) {
                        // Mark all models for this stock/horizon as not best
                        MlModel::where('stock_id', $stock->id)
                            ->where('prediction_horizon', $horizon)
                            ->update(['is_best_model' => false]);

                        // Mark the best model
                        $bestModel->update(['is_best_model' => true]);
                    }
                } catch (\Exception $e) {
                    $this->warn("Error selecting best model for {$stock->code} (horizon: {$horizon}): " . $e->getMessage());
                }

                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine();
        $this->info('Best models selected successfully');

        return Command::SUCCESS;
    }
}

