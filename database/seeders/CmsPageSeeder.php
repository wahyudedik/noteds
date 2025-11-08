<?php

namespace Database\Seeders;

use App\Models\CmsPage;
use Illuminate\Database\Seeder;

class CmsPageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pages = [
            [
                'slug' => 'privacy-policy',
                'title' => 'Privacy Policy',
                'content' => <<<HTML
<h2>Privacy Policy</h2>
<p>Welcome to Noteds.com. We respect your privacy and are committed to protecting your personal data. This policy explains how we collect, use, and safeguard information when you use our platform hosted on our secure VPS infrastructure.</p>
<h3>What we collect</h3>
<ul>
    <li>Account information such as name, email, and username</li>
    <li>Content you upload, including notes, files, and messages</li>
    <li>Transaction history and wallet balance for marketplace activities</li>
</ul>
<h3>How we use your data</h3>
<p>We use your information to provide the Noteds marketplace experience, process secure payments between buyers and sellers, improve platform features, and comply with legal requirements.</p>
<h3>Data storage</h3>
<p>All data is stored on our managed VPS servers configured for high availability and security. Access is limited to authorized personnel only.</p>
<h3>Contact</h3>
<p>If you have questions about this policy, email us at <a href="mailto:privacy@noteds.com">privacy@noteds.com</a>.</p>
HTML,
                'meta_title' => 'Privacy Policy - Noteds',
                'meta_description' => 'Learn how Noteds.com collects, uses, and protects your personal data across our VPS-hosted marketplace.',
                'is_active' => true,
            ],
            [
                'slug' => 'terms-and-conditions',
                'title' => 'Terms &amp; Conditions',
                'content' => <<<HTML
<h2>Terms &amp; Conditions</h2>
<p>These terms govern your access to and use of Noteds.com, including the marketplace for buying and selling digital notes.</p>
<h3>Accounts</h3>
<p>Users must provide accurate information during registration. Sellers are responsible for the originality of their uploaded content.</p>
<h3>Marketplace Rules</h3>
<ul>
    <li>Platform commission and creator commission rates are set in the admin panel</li>
    <li>Reselling purchased notes without substantial modifications is prohibited</li>
    <li>All transactions are processed through the secure Noteds wallet system</li>
</ul>
<h3>Liability</h3>
<p>Noteds is not responsible for losses arising from misuse of accounts or unauthorized sharing of purchased content.</p>
<h3>Dispute Resolution</h3>
<p>For any disputes, please contact support@noteds.com. We aim to resolve issues within 5 working days.</p>
HTML,
                'meta_title' => 'Terms and Conditions - Noteds',
                'meta_description' => 'Review the terms and conditions for using Noteds.com and participating in the digital notes marketplace.',
                'is_active' => true,
            ],
        ];

        foreach ($pages as $page) {
            CmsPage::updateOrCreate(
                ['slug' => $page['slug']],
                [
                    'title' => $page['title'],
                    'content' => $page['content'],
                    'meta_title' => $page['meta_title'],
                    'meta_description' => $page['meta_description'],
                    'is_active' => $page['is_active'],
                ]
            );
        }
    }
}

