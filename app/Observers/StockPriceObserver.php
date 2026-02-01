<?php

namespace App\Observers;

use App\Events\StockPriceUpdated;
use App\Models\StockPrice;

class StockPriceObserver
{
    public function created(StockPrice $price): void
    {
        $stock = $price->stock()->first();
        if ($stock) {
            event(new StockPriceUpdated($stock, $price));
        }
    }

    public function updated(StockPrice $price): void
    {
        $stock = $price->stock()->first();
        if ($stock) {
            event(new StockPriceUpdated($stock, $price));
        }
    }
}
