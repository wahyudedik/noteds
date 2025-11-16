<?php

namespace App\Console\Commands;

use App\Models\OrderActivity;
use App\Models\ServiceOrder;
use Carbon\Carbon;
use Illuminate\Console\Command;

class StudioSlaReminders extends Command
{
    protected $signature = 'studio:sla-reminders';
    protected $description = 'Send SLA reminders for Studio milestones due and funding reminders';

    public function handle(): int
    {
        $now = Carbon::now();
        $remindersSent = 0;

        // Milestone due reminders: look for milestones with due_at <= now and not yet reminded
        $orders = ServiceOrder::whereNotNull('milestones')->get();
        foreach ($orders as $order) {
            $milestones = $order->milestones ?? [];
            foreach ($milestones as $index => $m) {
                if (empty($m['due_at'])) {
                    continue;
                }
                $dueAt = Carbon::parse($m['due_at']);
                if ($dueAt->greaterThan($now)) {
                    continue;
                }
                // Check if already reminded
                $already = OrderActivity::where('service_order_id', $order->id)
                    ->where('action', 'milestone_due_reminder')
                    ->where('meta->milestone_index', $index)
                    ->exists();
                if ($already) {
                    continue;
                }
                // Notify buyer and vendor if assigned
                if ($order->assigned_user_id) {
                    \App\Models\AppNotification::create([
                        'user_id' => $order->assigned_user_id,
                        'type' => 'studio_sla',
                        'title' => 'Milestone jatuh tempo',
                        'message' => 'Milestone #' . ($index + 1) . ' untuk "' . $order->title . '" telah jatuh tempo.',
                        'link' => route('studio.orders.show', $order),
                        'is_read' => false,
                        'data' => ['milestone_index' => $index],
                    ]);
                }
                \App\Models\AppNotification::create([
                    'user_id' => $order->user_id,
                    'type' => 'studio_sla',
                    'title' => 'Milestone jatuh tempo',
                    'message' => 'Milestone #' . ($index + 1) . ' untuk "' . $order->title . '" telah jatuh tempo.',
                    'link' => route('studio.orders.show', $order),
                    'is_read' => false,
                    'data' => ['milestone_index' => $index],
                ]);

                OrderActivity::create([
                    'service_order_id' => $order->id,
                    'user_id' => null,
                    'action' => 'milestone_due_reminder',
                    'description' => 'Pengingat milestone jatuh tempo',
                    'meta' => ['milestone_index' => $index, 'due_at' => $dueAt->toDateTimeString()],
                ]);
                $remindersSent++;
            }
        }

        // Funding reminders: orders quoted with zero escrow for X days
        $days = (int) (\App\Models\Setting::getSetting('studio_sla_funding_reminder_days', 'studio', 3) ?? 3);
        $threshold = Carbon::now()->subDays($days);
        $unfunded = ServiceOrder::where('status', 'quoted')
            ->where('escrow_amount', 0)
            ->where('updated_at', '<=', $threshold)
            ->get();
        foreach ($unfunded as $order) {
            $already = OrderActivity::where('service_order_id', $order->id)
                ->where('action', 'funding_reminder')
                ->where('created_at', '>=', $threshold)
                ->exists();
            if ($already) {
                continue;
            }
            \App\Models\AppNotification::create([
                'user_id' => $order->user_id,
                'type' => 'studio_sla',
                'title' => 'Pengingat pendanaan escrow',
                'message' => 'Order "' . $order->title . '" menunggu pendanaan escrow.',
                'link' => route('studio.orders.show', $order),
                'is_read' => false,
                'data' => ['days' => $days],
            ]);
            OrderActivity::create([
                'service_order_id' => $order->id,
                'user_id' => null,
                'action' => 'funding_reminder',
                'description' => 'Pengingat pendanaan escrow ke buyer',
                'meta' => ['days' => $days],
            ]);
            $remindersSent++;
        }

        $this->info("SLA reminders sent: {$remindersSent}");
        return Command::SUCCESS;
    }
}


