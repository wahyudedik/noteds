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
            // Buying & Marketplace
            [
                'question' => 'How do I buy notes?',
                'answer' => "Buying notes on Noteds is simple:\n\n1. Browse the Marketplace to find notes you're interested in\n2. Click on a note to view its details, including summary, price, and seller info\n3. If you haven't already, add funds to your wallet by visiting the Wallet page\n4. Click \"Purchase Note\" and confirm the transaction\n5. The note will be instantly available in your purchased notes list\n\nNote: You can also browse free notes (Rp 0) without purchasing!",
                'order' => 1,
                'is_active' => true,
            ],
            [
                'question' => 'What payment methods are available?',
                'answer' => "We support multiple payment methods through Midtrans integration:\n\n• Credit/Debit Card (Visa, Mastercard)\n• Bank Transfer (BCA, Mandiri, BNI, CIMB Niaga)\n• E-Wallets (OVO, DANA, GCash)\n• QRIS (Quick Response Code Indonesian Standard)\n\nAll transactions are secure and encrypted. Your payment information is never stored on our servers.",
                'order' => 2,
                'is_active' => true,
            ],
            [
                'question' => 'Can I return or get a refund for notes I purchased?',
                'answer' => "Our refund policy is designed to protect both buyers and sellers:\n\n• Refunds are available within 24 hours of purchase if you haven't accessed the note\n• If you've already viewed the content, refunds are not available (it's a digital product)\n• Contact our support team with your order details for refund requests\n• Refunds are processed back to your original payment method within 3-5 business days\n\nWe recommend reading note previews and checking ratings before purchasing!",
                'order' => 3,
                'is_active' => true,
            ],

            // Selling & Earning
            [
                'question' => 'How do I earn money?',
                'answer' => "There are several ways to earn money on Noteds:\n\n📝 Selling Notes: Create valuable notes and set your own price. When someone purchases your note, you keep 80% of the sale (20% platform commission).\n\n👥 Referral Program: Earn Rp5,000 for each person who signs up using your referral link, plus 5% commission on their purchases!\n\nUse our Earnings Calculator to estimate your potential income.",
                'order' => 4,
                'is_active' => true,
            ],
            [
                'question' => 'What is the Income Growth Projector and how do I use it?',
                'answer' => "The Income Growth Projector is a simulator tool that helps you forecast your potential earnings:\n\n📊 How it works:\n1. Enter the number of notes you plan to create (1-50)\n2. Set average sales per month per note (1-100)\n3. Add your expected monthly growth rate (0-20%)\n4. Input your average note price\n5. Click \"Project My Income\"\n\n📈 You'll see:\n• Month 1 and Month 6 income projections\n• Total 12-month net income (after 20% commission)\n• Interactive line chart showing growth trajectory\n• Month-by-month breakdown table\n\nThis helps you understand realistic income potential and plan your content strategy!",
                'order' => 5,
                'is_active' => true,
            ],
            [
                'question' => 'What is the Earnings Calculator?',
                'answer' => "The Earnings Calculator is a quick tool to estimate your monthly income from selling notes:\n\n💰 Simply enter:\n• Note Price (in your currency)\n• Expected Sales per Month\n\n✅ You'll instantly see:\n• Monthly gross income\n• Yearly gross income\n• Net income after 20% platform commission\n\nPerfect for quick \"what-if\" scenarios! Try different price points to find your sweet spot.",
                'order' => 6,
                'is_active' => true,
            ],
            [
                'question' => 'What are platform fees and how are they calculated?',
                'answer' => "Platform Commission Structure:\n\n📊 Paid Notes: 20% commission\n- Example: If your note sells for Rp100,000, you earn Rp80,000\n\n📝 Free Notes: 0% commission\n- You can share knowledge without any fees\n\n💳 Payment Gateway Fee: Already included in your net earnings\n\nUse our Commission Structure Visualizer to see the exact breakdown for your note prices!\n\nTip: Premium sellers get no additional discounts, but unlimited notes and featured placement!",
                'order' => 7,
                'is_active' => true,
            ],
            [
                'question' => 'What is the Price Benchmark Tool?',
                'answer' => "The Price Benchmark Tool helps you optimize your note pricing:\n\n🎯 How to use it:\n1. Enter your note price\n2. Select the category (Tutorial, Template, Guide, Resource)\n3. Click \"Compare Note Prices\"\n\n📊 You'll see:\n• Market average price for that category\n• Your price position (below/at/above market)\n• Percentage difference from average\n\nThis helps you price competitively while maximizing your earnings!",
                'order' => 8,
                'is_active' => true,
            ],

            // Wallet & Transactions
            [
                'question' => 'How does the wallet system work?',
                'answer' => "The Noteds wallet is your account balance for purchasing notes:\n\n• Top-up: Add funds using secure payment methods (Midtrans integration)\n• Purchase: Buy notes directly from your wallet balance\n• Earnings: Money from note sales is automatically added to your wallet\n• Withdraw: Request withdrawals (minimum Rp50,000) to your bank account\n\nPlatform commission: 20% on paid notes, 0% on free notes.",
                'order' => 9,
                'is_active' => true,
            ],
            [
                'question' => 'What is the Wallet Simulator?',
                'answer' => "The Wallet Simulator lets you practice using wallet features in a risk-free environment:\n\n💰 Features:\n• Top-up: Simulate adding funds to your wallet\n• Withdraw: Practice withdrawing money\n• Balance Tracking: See your current wallet balance in real-time\n• Transaction History: View all simulated transactions with timestamps\n\nUse this to understand how wallet operations work before making real transactions!",
                'order' => 10,
                'is_active' => true,
            ],
            [
                'question' => 'What is the Transaction Flow Simulator?',
                'answer' => "The Transaction Flow Simulator visualizes the complete purchase process:\n\n🔄 4-Step Flow:\n1. Select Note - Browse and choose a note from marketplace\n2. Checkout - Review order details and confirm\n3. Payment - Securely pay via Midtrans gateway\n4. Complete - Get instant access to the note content\n\nClick \"Simulate Transaction\" to watch the entire flow animate. This helps new buyers understand exactly what happens when they purchase a note!",
                'order' => 11,
                'is_active' => true,
            ],
            [
                'question' => 'How long does a withdrawal take?',
                'answer' => "Withdrawal timelines:\n\n⏱️ Processing:\n• Withdrawal requests are processed within 1-2 business days\n• Bank transfers typically take 1-3 business days to appear in your account\n• Total time: 2-5 business days\n\n📋 Requirements:\n• Minimum withdrawal: Rp50,000\n• Valid bank account information\n• Account verification completed\n\n💡 Tip: Batch your withdrawals to save time - request larger amounts less frequently!",
                'order' => 12,
                'is_active' => true,
            ],

            // Premium & Plans
            [
                'question' => 'What are the Premium plan benefits?',
                'answer' => "Upgrade to Premium for Rp25,000/month and unlock:\n\n• Unlimited notes - Create as many notes as you want (Basic plan limited to 10 total)\n• Priority support - Get faster response times from our support team\n• Featured placement - Your notes appear prominently in marketplace search results\n• Advanced analytics - Track your note performance, sales, and buyer insights",
                'order' => 13,
                'is_active' => true,
            ],
            [
                'question' => 'Can I cancel my Premium subscription?',
                'answer' => "Yes, you can cancel anytime:\n\n❌ Cancellation Details:\n• Cancel from Settings > Subscription at any time\n• No cancellation fees or penalties\n• Keep access until the end of your current billing period\n• Your notes remain published and sellable\n\n✅ What you keep:\n• All your notes and content\n• Wallet balance and earnings\n• Buyer and seller history\n\n💡 Tip: You can re-subscribe anytime if you want the benefits back!",
                'order' => 14,
                'is_active' => true,
            ],
            [
                'question' => 'What\'s the difference between Basic and Premium plans?',
                'answer' => "Plan Comparison:\n\n📌 BASIC (Free):\n✓ 10 notes maximum\n✓ Standard support (24-48 hour response)\n✓ Public marketplace listing\n✓ Basic analytics\n✓ Seller features\n\n⭐ PREMIUM (Rp25,000/month):\n✓ Unlimited notes\n✓ Priority support (4-8 hour response)\n✓ Featured marketplace placement\n✓ Advanced analytics & insights\n✓ All Basic features\n\nUse our Plan Comparison tool in Simulators to see which plan is best for you!",
                'order' => 15,
                'is_active' => true,
            ],

            // Referral Program
            [
                'question' => 'How does the referral program work?',
                'answer' => "The referral program rewards you for bringing new users to Noteds:\n\n💰 Signup Reward: Earn Rp5,000 instantly when someone signs up using your unique referral link\n\n💵 Transaction Commission: Get 5% commission on every purchase made by users you referred\n\nTrack your earnings with the Referral ROI Calculator!",
                'order' => 16,
                'is_active' => true,
            ],
            [
                'question' => 'What is the Referral ROI Calculator?',
                'answer' => "The Referral ROI Calculator helps you estimate your referral program earnings:\n\n📊 Input:\n• Total Referrals - Number of people who signed up via your link\n• Average Transaction - Typical purchase amount per referral\n\n💹 You'll see:\n• Signup Rewards (Rp5,000 × number of referrals)\n• Transaction Commissions (5% of their purchases)\n• Total Monthly Referral Income\n\nPerfect for understanding your referral program potential!",
                'order' => 17,
                'is_active' => true,
            ],
            [
                'question' => 'How do I get my unique referral link?',
                'answer' => "Finding your referral link:\n\n1. Go to Dashboard > Referral Program (or Settings > Referral)\n2. Your unique referral link is displayed prominently\n3. Click \"Copy Link\" to copy it to clipboard\n4. Share it on social media, email, or anywhere\n\n📱 Where to share:\n• Social media (Instagram, Twitter, TikTok, Facebook)\n• Email newsletters\n• Personal blog or website\n• Discord/Slack communities\n• WhatsApp groups\n\nEvery signup using your link earns you Rp5,000 + 5% on their purchases!",
                'order' => 18,
                'is_active' => true,
            ],

            // Notes & Content
            [
                'question' => 'How do I create a note?',
                'answer' => "Creating a note is easy:\n\n1. Click \"Create Note\" from Dashboard or main menu\n2. Fill in basic information:\n   • Title (clear and descriptive)\n   • Description/Summary\n   • Category (Tutorial, Template, Guide, Resource)\n   • Tags (help with discoverability)\n3. Add your content (text, formatting, images)\n4. Set price (Rp0 for free or any amount)\n5. Choose visibility (Public for marketplace or Private)\n6. Click \"Publish\"\n\n💡 Tips for success:\n• Make titles clear and SEO-friendly\n• Write detailed descriptions\n• Use relevant tags\n• Add images/formatting for readability\n• Price competitively using Price Benchmark tool",
                'order' => 19,
                'is_active' => true,
            ],
            [
                'question' => 'Can I edit or delete notes after publishing?',
                'answer' => "Yes! You have full control over your published notes:\n\n✏️ Editing:\n• Click \"Edit\" on any of your notes\n• Modify title, description, content, price, tags\n• Changes are saved immediately\n• Buyers have access to the latest version\n\n🗑️ Deleting:\n• Click \"Delete\" to permanently remove a note\n• This action cannot be undone\n• Note will no longer be visible to buyers\n• Existing purchases are not affected (buyers keep access)\n\n💡 Tip: Don't delete notes with sales - just make them private if you don't want new purchases!",
                'order' => 20,
                'is_active' => true,
            ],
            [
                'question' => 'Can I share my notes for free?',
                'answer' => "Absolutely! We encourage knowledge sharing on Noteds.\n\nYou can choose to set your note price to Rp 0 (free) when creating or editing a note. Free notes benefit from:\n\n• No platform commission (0%)\n• Community reputation building\n• Helping others learn for free\n• Still eligible for ratings and reviews\n\nYou can mix free and paid notes to build your brand while monetizing premium content!",
                'order' => 21,
                'is_active' => true,
            ],
            [
                'question' => 'What categories are available for notes?',
                'answer' => "Noteds supports 4 main note categories:\n\n📚 Tutorial - Step-by-step guides, how-to articles\n🎨 Template - Ready-to-use formats, layouts, frameworks\n📖 Guide - Comprehensive references, best practices\n📦 Resource - Tools, checklists, resources\n\nChoose the most relevant category for better discoverability in marketplace search!",
                'order' => 22,
                'is_active' => true,
            ],

            // General Features
            [
                'question' => 'Is there a mobile app?',
                'answer' => "Noteds web app is fully responsive and works great on mobile browsers!\n\nWe're planning to launch dedicated iOS and Android apps in the future. Join our newsletter to stay updated.",
                'order' => 23,
                'is_active' => true,
            ],
            [
                'question' => 'How do I contact support?',
                'answer' => "We're here to help! Contact us through:\n\n💬 In-app Chat: Available through the help icon in your dashboard\n📧 Email: support@noteds.com\n🐛 Report Bug: Use the Report Bug feature in settings\n📱 Social Media: Find us on Twitter, Instagram, Facebook\n\nResponse times:\n• Basic plan: 24-48 hours\n• Premium plan: 4-8 hours\n• Urgent issues: Priority handling",
                'order' => 24,
                'is_active' => true,
            ],
            [
                'question' => 'Is my data safe and private?',
                'answer' => "Security is our top priority:\n\n🔒 Data Protection:\n• All data encrypted in transit (HTTPS)\n• Database encryption for sensitive information\n• Regular security audits and penetration testing\n• GDPR compliant data handling\n\n👤 Privacy:\n• We never share your data with third parties\n• You control your note visibility (public/private)\n• Purchases are private between you and the seller\n• Payment info handled by secure Midtrans gateway\n\n📜 Read our Privacy Policy for full details",
                'order' => 25,
                'is_active' => true,
            ],
            [
                'question' => 'What are the community guidelines?',
                'answer' => "Keep Noteds a safe and respectful community:\n\n✅ DO:\n• Share original, valuable content\n• Be respectful to other members\n• Use appropriate language\n• Report inappropriate content\n\n❌ DON'T:\n• Post plagiarized or stolen content\n• Harass or insult other users\n• Spam or promote unrelated products\n• Share personal information of others\n• Engage in illegal activities\n\nViolations may result in warning, suspension, or permanent ban. Report violations using the report button on any note or profile.",
                'order' => 26,
                'is_active' => true,
            ],
            [
                'question' => 'How are notes rated and reviewed?',
                'answer' => "Transparency through ratings:\n\n⭐ Rating System:\n• 1-5 stars based on buyer experience\n• Buyers can write detailed reviews\n• Anonymous or identified reviews\n• You can reply to reviews\n\n📊 What affects ratings:\n• Note quality and accuracy\n• Content organization and clarity\n• Timeliness (if applicable)\n• Value for money\n\n💡 Tips for positive reviews:\n• Create high-quality content\n• Proofread before publishing\n• Update notes when needed\n• Respond professionally to feedback",
                'order' => 27,
                'is_active' => true,
            ],
            [
                'question' => 'Can I change my username?',
                'answer' => "Yes, you can change your username in settings:\n\n⚙️ How to change:\n1. Go to Settings > Account Settings\n2. Click \"Change Username\"\n3. Enter your new username\n4. Follow the same rules: 3-30 characters, lowercase, letters/numbers/dashes/underscores\n5. Save changes\n\n⏳ Limitations:\n• You can change your username once every 30 days\n• Old username becomes available for others after change\n• Your public profile URL updates automatically\n• Previous links to your profile will redirect to new username",
                'order' => 28,
                'is_active' => true,
            ],
            [
                'question' => 'What is a workspace?',
                'answer' => "Workspaces help you organize notes for different purposes:\n\n📁 Workspace Types:\n• Personal - For your own notes\n• Team - For small team collaboration\n• Organization - For larger teams/departments\n\n✨ Benefits:\n• Organize notes by project or topic\n• Invite team members (for team/org workspaces)\n• Set different permissions per workspace\n• Keep projects separate and organized\n\nEach workspace has its own notes, members, and settings. Start with \"Personal\" if you're just beginning!",
                'order' => 29,
                'is_active' => true,
            ],
            [
                'question' => 'How do I explore the marketplace?',
                'answer' => "The marketplace is where you discover and buy notes:\n\n🔍 Finding Notes:\n1. Go to Marketplace from main menu\n2. Use search bar to find specific topics\n3. Browse by category or tags\n4. Sort by price, rating, newest, or trending\n\n📌 Marketplace Preview:\nUse the Marketplace Preview Simulator to explore features before buying!\n\n💡 Tips:\n• Read reviews before purchasing\n• Check note preview/description\n• Follow sellers you like\n• Use filters to narrow results\n• Sort by highest rated for quality\n\nFree notes available too - no purchase needed!",
                'order' => 30,
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
