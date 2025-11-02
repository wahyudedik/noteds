<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faqs = [
            [
                'question' => 'How do I buy notes?',
                'answer' => "Buying notes on Noteds is simple:\n\n1. Browse the Marketplace to find notes you're interested in\n2. Click on a note to view its details, including summary, price, and seller info\n3. If you haven't already, add funds to your wallet by visiting the Wallet page\n4. Click \"Purchase Note\" and confirm the transaction\n5. The note will be instantly available in your purchased notes list\n\nNote: You can also browse free notes (Rp 0) without purchasing!",
                'order' => 1,
                'is_active' => true,
            ],
            [
                'question' => 'How do I earn money?',
                'answer' => "There are several ways to earn money on Noteds:\n\n📝 Selling Notes: Create valuable notes and set your own price. When someone purchases your note, you keep 80% of the sale (20% platform commission).\n\n👥 Referral Program: Earn Rp5,000 for each person who signs up using your referral link, plus 5% commission on their purchases!\n\nUse our Earnings Calculator to estimate your potential income.",
                'order' => 2,
                'is_active' => true,
            ],
            [
                'question' => 'What are the Premium plan benefits?',
                'answer' => "Upgrade to Premium for just Rp25,000/month and unlock:\n\n• Unlimited notes - Create as many notes as you want (Basic plan limited to 10 total)\n• Priority support - Get faster response times from our support team\n• Featured placement - Your notes appear prominently in marketplace search results\n• Advanced analytics - Track your note performance, sales, and buyer insights",
                'order' => 3,
                'is_active' => true,
            ],
            [
                'question' => 'How does the wallet system work?',
                'answer' => "The Noteds wallet is your account balance for purchasing notes:\n\n• Top-up: Add funds using secure payment methods (Midtrans integration)\n• Purchase: Buy notes directly from your wallet balance\n• Earnings: Money from note sales is automatically added to your wallet\n• Withdraw: Request withdrawals (minimum Rp50,000) to your bank account\n\nPlatform commission: 20% on paid notes, 0% on free notes.",
                'order' => 4,
                'is_active' => true,
            ],
            [
                'question' => 'Can I share my notes for free?',
                'answer' => "Absolutely! We encourage knowledge sharing on Noteds.\n\nYou can choose to set your note price to Rp 0 (free) when creating or editing a note. Free notes benefit from:\n\n• No platform commission (0%)\n• Community reputation building\n• Helping others learn for free\n• Still eligible for ratings and reviews\n\nYou can mix free and paid notes to build your brand while monetizing premium content!",
                'order' => 5,
                'is_active' => true,
            ],
            [
                'question' => 'How does the referral program work?',
                'answer' => "The referral program rewards you for bringing new users to Noteds:\n\n💰 Signup Reward: Earn Rp5,000 instantly when someone signs up using your unique referral link\n\n💵 Transaction Commission: Get 5% commission on every purchase made by users you referred\n\nTrack your earnings with the Referral ROI Calculator!",
                'order' => 6,
                'is_active' => true,
            ],
            [
                'question' => 'Is there a mobile app?',
                'answer' => "Noteds web app is fully responsive and works great on mobile browsers!\n\nWe're planning to launch dedicated iOS and Android apps in the future. Join our newsletter to stay updated.",
                'order' => 7,
                'is_active' => true,
            ],
        ];

        foreach ($faqs as $faq) {
            Faq::firstOrCreate(
                ['question' => $faq['question']],
                $faq
            );
        }
    }
}
