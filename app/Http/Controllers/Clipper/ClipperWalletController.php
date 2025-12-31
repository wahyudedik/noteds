<?php

namespace App\Http\Controllers\Clipper;

use App\Http\Controllers\Controller;
use App\Services\WalletService;
use App\Services\LedgerService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClipperWalletController extends Controller
{
    public function __construct(
        private WalletService $walletService,
        private LedgerService $ledgerService
    ) {}

    public function index()
    {
        $wallet = $this->walletService->getClipperWallet(auth()->user());

        return Inertia::render('Clipper/Wallet/Clipper', [
            'wallet' => $wallet,
        ]);
    }

    public function history(Request $request)
    {
        $wallet = $this->walletService->getClipperWallet(auth()->user());
        
        $history = $this->ledgerService->getWalletHistory(
            'clipper',
            $wallet->id,
            $request->get('limit', 50)
        );

        return Inertia::render('Clipper/Wallet/ClipperHistory', [
            'wallet' => $wallet,
            'history' => $history,
        ]);
    }
}
