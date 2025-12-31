<?php

namespace Tests\Feature\Marketplace;

use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\Withdrawal;
use App\Services\NotificationService;
use App\Mail\OrderConfirmation;
use App\Mail\PaymentSuccess;
use App\Mail\WithdrawalRequest;
use App\Mail\WithdrawalStatus;
use App\Notifications\NewOrderNotification;
use App\Notifications\WithdrawalRequestNotification;
use App\Notifications\WithdrawalStatusNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class EmailNotificationsTest extends TestCase
{
    use RefreshDatabase;

    protected User $buyer;
    protected User $seller;
    protected User $admin;
    protected Product $product;
    protected NotificationService $notificationService;

    protected function setUp(): void
    {
        parent::setUp();
        
        Mail::fake();
        Notification::fake();
        
        $this->buyer = User::factory()->create();
        $this->seller = User::factory()->create();
        $this->admin = User::factory()->create(['role' => 'admin']);
        
        $this->product = Product::create([
            'user_id' => $this->seller->id,
            'name' => 'Test Digital Product',
            'slug' => 'test-digital-product',
            'description' => 'Test product description',
            'price' => 100000,
            'category' => 'Software',
            'is_active' => true,
        ]);

        $this->notificationService = app(NotificationService::class);
    }

    /** @test */
    public function seller_receives_notification_on_new_order()
    {
        $order = Order::create([
            'user_id' => $this->buyer->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
            'price' => $this->product->price,
            'total' => $this->product->price,
            'status' => 'completed',
            'payment_status' => 'paid',
            'order_number' => 'ORD-' . now()->format('Ymd') . '-000001',
        ]);

        $this->notificationService->notifyNewOrder($order);

        Notification::assertSentTo(
            $this->seller,
            NewOrderNotification::class,
            function ($notification, $channels) use ($order) {
                return $notification->order->id === $order->id;
            }
        );
    }

    /** @test */
    public function admin_receives_notification_on_withdrawal_request()
    {
        $withdrawal = Withdrawal::create([
            'user_id' => $this->seller->id,
            'amount' => 100000,
            'method' => 'bank_transfer',
            'bank_name' => 'BCA',
            'account_number' => '1234567890',
            'account_name' => 'Test Account',
            'status' => 'pending',
            'user_type' => 'seller',
        ]);

        $this->notificationService->notifyWithdrawalRequest($withdrawal);

        Notification::assertSentTo(
            $this->admin,
            WithdrawalRequestNotification::class,
            function ($notification, $channels) use ($withdrawal) {
                return $notification->withdrawal->id === $withdrawal->id;
            }
        );
    }

    /** @test */
    public function user_receives_notification_on_withdrawal_status_change()
    {
        $withdrawal = Withdrawal::create([
            'user_id' => $this->seller->id,
            'amount' => 100000,
            'method' => 'bank_transfer',
            'bank_name' => 'BCA',
            'account_number' => '1234567890',
            'account_name' => 'Test Account',
            'status' => 'approved',
            'user_type' => 'seller',
        ]);

        $this->notificationService->notifyWithdrawalStatus($withdrawal);

        Notification::assertSentTo(
            $this->seller,
            WithdrawalStatusNotification::class,
            function ($notification, $channels) use ($withdrawal) {
                return $notification->withdrawal->id === $withdrawal->id;
            }
        );
    }

    /** @test */
    public function order_confirmation_email_is_sent()
    {
        $order = Order::create([
            'user_id' => $this->buyer->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
            'price' => $this->product->price,
            'total' => $this->product->price,
            'status' => 'completed',
            'payment_status' => 'paid',
            'order_number' => 'ORD-' . now()->format('Ymd') . '-000002',
        ]);

        Mail::to($this->buyer->email)->send(new OrderConfirmation($order));

        Mail::assertSent(OrderConfirmation::class, function ($mail) use ($order) {
            return $mail->order->id === $order->id &&
                   $mail->hasTo($this->buyer->email);
        });
    }

    /** @test */
    public function payment_success_email_is_sent()
    {
        $order = Order::create([
            'user_id' => $this->buyer->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
            'price' => $this->product->price,
            'total' => $this->product->price,
            'status' => 'completed',
            'payment_status' => 'paid',
            'order_number' => 'ORD-' . now()->format('Ymd') . '-000003',
        ]);

        Mail::to($this->buyer->email)->send(new PaymentSuccess($order));

        Mail::assertSent(PaymentSuccess::class, function ($mail) use ($order) {
            return $mail->order->id === $order->id &&
                   $mail->hasTo($this->buyer->email);
        });
    }

    /** @test */
    public function withdrawal_request_email_is_sent_to_admin()
    {
        $withdrawal = Withdrawal::create([
            'user_id' => $this->seller->id,
            'amount' => 100000,
            'method' => 'bank_transfer',
            'bank_name' => 'BCA',
            'account_number' => '1234567890',
            'account_name' => 'Test Account',
            'status' => 'pending',
            'user_type' => 'seller',
        ]);

        Mail::to($this->admin->email)->send(new WithdrawalRequest($withdrawal));

        Mail::assertSent(WithdrawalRequest::class, function ($mail) use ($withdrawal) {
            return $mail->withdrawal->id === $withdrawal->id &&
                   $mail->hasTo($this->admin->email);
        });
    }

    /** @test */
    public function withdrawal_status_email_is_sent_to_user()
    {
        $withdrawal = Withdrawal::create([
            'user_id' => $this->seller->id,
            'amount' => 100000,
            'method' => 'bank_transfer',
            'bank_name' => 'BCA',
            'account_number' => '1234567890',
            'account_name' => 'Test Account',
            'status' => 'approved',
            'user_type' => 'seller',
        ]);

        Mail::to($this->seller->email)->send(new WithdrawalStatus($withdrawal));

        Mail::assertSent(WithdrawalStatus::class, function ($mail) use ($withdrawal) {
            return $mail->withdrawal->id === $withdrawal->id &&
                   $mail->hasTo($this->seller->email);
        });
    }

    /** @test */
    public function multiple_admins_receive_withdrawal_request_notification()
    {
        $admin2 = User::factory()->create(['role' => 'admin']);

        $withdrawal = Withdrawal::create([
            'user_id' => $this->seller->id,
            'amount' => 100000,
            'method' => 'bank_transfer',
            'bank_name' => 'BCA',
            'account_number' => '1234567890',
            'account_name' => 'Test Account',
            'status' => 'pending',
            'user_type' => 'seller',
        ]);

        $this->notificationService->notifyWithdrawalRequest($withdrawal);

        Notification::assertSentTo(
            $this->admin,
            WithdrawalRequestNotification::class
        );

        Notification::assertSentTo(
            $admin2,
            WithdrawalRequestNotification::class
        );
    }

    /** @test */
    public function notification_contains_correct_order_details()
    {
        $order = Order::create([
            'user_id' => $this->buyer->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
            'price' => $this->product->price,
            'total' => $this->product->price,
            'status' => 'completed',
            'payment_status' => 'paid',
            'order_number' => 'ORD-' . now()->format('Ymd') . '-000004',
        ]);

        $this->notificationService->notifyNewOrder($order);

        Notification::assertSentTo(
            $this->seller,
            NewOrderNotification::class,
            function ($notification) use ($order) {
                return $notification->order->id === $order->id &&
                       $notification->order->product->id === $this->product->id &&
                       $notification->order->buyer->id === $this->buyer->id;
            }
        );
    }

    /** @test */
    public function notification_contains_correct_withdrawal_details()
    {
        $withdrawal = Withdrawal::create([
            'user_id' => $this->seller->id,
            'amount' => 100000,
            'method' => 'bank_transfer',
            'bank_name' => 'BCA',
            'account_number' => '1234567890',
            'account_name' => 'Test Account',
            'status' => 'approved',
            'user_type' => 'seller',
        ]);

        $this->notificationService->notifyWithdrawalStatus($withdrawal);

        Notification::assertSentTo(
            $this->seller,
            WithdrawalStatusNotification::class,
            function ($notification) use ($withdrawal) {
                return $notification->withdrawal->id === $withdrawal->id &&
                       $notification->withdrawal->amount == 100000 &&
                       $notification->withdrawal->status === 'approved';
            }
        );
    }

    /** @test */
    public function email_notification_is_sent_via_queue_job()
    {
        Queue::fake();

        $order = Order::create([
            'user_id' => $this->buyer->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
            'price' => $this->product->price,
            'total' => $this->product->price,
            'status' => 'completed',
            'payment_status' => 'paid',
            'order_number' => 'ORD-' . now()->format('Ymd') . '-000005',
        ]);

        \App\Jobs\SendOrderConfirmationEmail::dispatch($order);

        Queue::assertPushed(\App\Jobs\SendOrderConfirmationEmail::class, function ($job) use ($order) {
            return $job->order->id === $order->id;
        });
    }
}

