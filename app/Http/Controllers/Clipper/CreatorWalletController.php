<?php

namespace App\Http\Controllers\Clipper;

use App\Http\Controllers\Controller;
use App\Services\WalletService;
use App\Services\LedgerService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CreatorWalletController extends Controller
{
    public function __construct(
        private WalletService $walletService,
        private LedgerService $ledgerService
    ) {}

    public function index()
    {
        $wallet = $this->walletService->getCreatorWallet(auth()->user());

        return Inertia::render('Clipper/Wallet/Creator', [
            'wallet' => $wallet,
        ]);
    }

    public function history(Request $request)
    {
        $wallet = $this->walletService->getCreatorWallet(auth()->user());
        
        $history = $this->ledgerService->getWalletHistory(
            'creator',
            $wallet->id,
            $request->get('limit', 50)
        );

        return Inertia::render('Clipper/Wallet/CreatorHistory', [
            'wallet' => $wallet,
            'history' => $history,
        ]);
    }
}
