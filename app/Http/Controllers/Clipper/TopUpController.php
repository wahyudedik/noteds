<?php

namespace App\Http\Controllers\Clipper;

use App\Http\Controllers\Controller;
use App\Services\TopUpService;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TopUpController extends Controller
{
    public function __construct(
        private TopUpService $topUpService,
        private MidtransService $midtransService
    ) {}

    public function index()
    {
        $topUps = auth()->user()->topUps()
            ->latest()
            ->paginate(15);

        return Inertia::render('Clipper/TopUps/Index', [
            'topUps' => $topUps,
        ]);
    }

    public function create()
    {
        return Inertia::render('Clipper/TopUps/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:10000',
            'payment_method' => 'required|in:ewallet,virtual_account,credit_card',
        ]);

        try {
            $topUp = $this->topUpService->createTopUp(
                auth()->user(),
                $validated['amount'],
                $validated['payment_method']
            );

            // Get snap token
            $params = [
                'transaction_details' => [
                    'order_id' => 'TOPUP-' . $topUp->id,
                    'gross_amount' => (int) $validated['amount'],
                ],
                'customer_details' => [
                    'first_name' => auth()->user()->name,
                    'email' => auth()->user()->email,
                ],
            ];

            $snapToken = \Midtrans\Snap::getSnapToken($params);

            return Inertia::render('Clipper/TopUps/Payment', [
                'topUp' => $topUp,
                'snapToken' => $snapToken,
            ]);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function webhook(Request $request)
    {
        $data = $request->all();
        
        try {
            if ($this->midtransService->handleWebhook($data)) {
                // Find top up by order ID
                $orderId = $data['order_id'] ?? null;
                if ($orderId && str_starts_with($orderId, 'TOPUP-')) {
                    $topUpId = str_replace('TOPUP-', '', $orderId);
                    $topUp = \App\Models\TopUp::find($topUpId);
                    
                    if ($topUp && $data['transaction_status'] === 'settlement') {
                        $this->topUpService->processTopUpSuccess($topUp);
                    }
                }
                
                // Always return 200 to acknowledge receipt
                return response()->json(['status' => 'success'], 200);
            }

            // Always return 200 to acknowledge receipt, even on error
            return response()->json(['status' => 'error', 'message' => 'Webhook processing failed'], 200);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('TopUp webhook error: ' . $e->getMessage(), [
                'exception' => $e,
                'webhook_data' => $data,
            ]);
            // Always return 200 to acknowledge receipt, even on error
            return response()->json(['status' => 'error', 'message' => 'Internal server error'], 200);
        }
    }
}
