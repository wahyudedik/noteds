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
<p>Effective date: 9 November 2025</p>
<p>Noteds (&ldquo;we&rdquo;, &ldquo;our&rdquo;, or &ldquo;us&rdquo;) operates a digital marketplace where creators publish learning notes and premium resources for buyers around the world. We value your trust and are committed to protecting the personal data that you share with us when you create an account, publish content, or make a purchase on Noteds.</p>

<h3>1. Information We Collect</h3>
<ul>
    <li><strong>Account Data:</strong> name, username, email address, password (hashed), role (buyer, seller, admin), and profile details you choose to share.</li>
    <li><strong>Transactional Data:</strong> wallet balance, purchase history, invoices, payout requests, referral records, and tax documentation when required by law.</li>
    <li><strong>Content Data:</strong> notes, files, thumbnails, post captions, forum discussions, comments, and messages that you upload or generate inside Noteds.</li>
    <li><strong>Usage &amp; Device Data:</strong> IP address, browser information, preferred language, time zone, device identifiers, and log information collected via cookies or similar technologies.</li>
    <li><strong>Support &amp; Verification Data:</strong> identity documents, screenshots, or other materials that you voluntarily provide when contacting support or appealing moderation decisions.</li>
</ul>

<h3>2. How We Use Your Information</h3>
<ul>
    <li>To create and maintain your Noteds account, personalize your dashboard, and keep your session secure.</li>
    <li>To enable marketplace features such as listing notes, processing purchases, handling withdrawals, and generating invoices.</li>
    <li>To deliver community features including forum posts, notifications, private workspaces, and email digests.</li>
    <li>To communicate with you regarding updates, policy changes, security alerts, promotional campaigns, and customer support responses.</li>
    <li>To monitor platform integrity, prevent fraud or misuse, enforce our Terms & Conditions, and comply with legal obligations.</li>
    <li>To analyze usage trends so we can improve search relevance, recommendation systems, and product performance.</li>
</ul>

<h3>3. Legal Bases for Processing</h3>
<p>We process your personal data on the following legal grounds (where applicable):</p>
<ul>
    <li><strong>Contractual necessity</strong> – to deliver the services you sign up for.</li>
    <li><strong>Legitimate interests</strong> – to maintain secure systems, fight abuse, and improve our offerings.</li>
    <li><strong>Legal obligations</strong> – to satisfy bookkeeping, tax, and anti-fraud regulations.</li>
    <li><strong>Consent</strong> – for optional features such as marketing subscriptions or cookies other than strictly necessary ones.</li>
</ul>

<h3>4. How We Share Information</h3>
<ul>
    <li>With <strong>service providers</strong> that supply payment processing, analytics, storage, email delivery, or customer support tools. These partners are contractually bound to protect your data.</li>
    <li>With <strong>other users</strong> when it is inherent to the product (for example, purchasers see seller profile information, forum posts are public to the community).</li>
    <li>With <strong>authorities</strong> where disclosure is required by law, regulation, court order, or to protect the rights, property, or safety of Noteds, our users, or others.</li>
    <li>During <strong>business transfers</strong> such as mergers, acquisitions, or restructuring, subject to the safeguards of this policy.</li>
</ul>

<h3>5. Cookies and Tracking Technologies</h3>
<p>We use cookies and similar technologies to keep you logged in, remember preferences, measure campaign performance, and detect suspicious activity. You can manage cookie preferences in your browser; note that disabling cookies may limit certain features.</p>

<h3>6. Data Retention</h3>
<p>We retain personal data for as long as your account is active or as needed to provide services. We may also retain limited information after account closure to meet legal obligations, resolve disputes, prevent abuse, or enforce our agreements. Content retained for analytics or historical reference is anonymized whenever feasible.</p>

<h3>7. Your Rights and Choices</h3>
<p>Depending on your jurisdiction, you may have the right to:</p>
<ul>
    <li>Access a copy of the personal data we hold about you.</li>
    <li>Request corrections of inaccurate or incomplete data.</li>
    <li>Request deletion of data when it is no longer needed or when consent is withdrawn.</li>
    <li>Object to or restrict certain processing activities.</li>
    <li>Export your data in a portable format.</li>
    <li>Lodge a complaint with your local data protection authority.</li>
</ul>
<p>You can manage most settings directly from your profile. For additional privacy requests, contact us using the email below. We will respond within a reasonable timeframe and in accordance with applicable law.</p>

<h3>8. Security</h3>
<p>We implement layered safeguards including TLS encryption, access controls, audit logging, and regular security reviews. Nevertheless, no online platform is completely immune to risks. We encourage you to create strong passwords, enable two-factor authentication when available, and promptly notify us of any suspected breaches.</p>

<h3>9. International Data Transfers</h3>
<p>Noteds infrastructure is primarily hosted in the Asia Pacific region. We may transfer data to other jurisdictions where our service providers operate. When we do so, we rely on legally recognized transfer mechanisms and ensure adequate protection of your data.</p>

<h3>10. Changes to This Policy</h3>
<p>We may update this Privacy Policy to reflect product improvements or legal requirements. Material changes will be announced via email or dashboard notifications. Continued use of Noteds after the effective date constitutes your acceptance of the revised policy.</p>

<h3>11. Contact Us</h3>
<p>If you have questions about this Privacy Policy or would like to exercise your rights, reach out to our privacy team at <a href="mailto:info@noteds.com">info@noteds.com</a> or via the in-app support center.</p>
HTML,
                'meta_title' => 'Privacy Policy - Noteds',
                'meta_description' => 'Learn how Noteds.com collects, uses, and protects your personal data across our VPS-hosted marketplace.',
                'is_active' => true,
            ],
            [
                'slug' => 'terms-and-conditions',
                'title' => 'Terms & Conditions',
                'content' => <<<HTML
<h2>Terms & Conditions</h2>
<p>Effective date: 9 November 2025</p>
<p>Welcome to Noteds, a platform operated by Noteds Technology (the &ldquo;Company&rdquo;, &ldquo;we&rdquo;, or &ldquo;us&rdquo;). These Terms & Conditions (&ldquo;Terms&rdquo;) form a legally binding agreement between Noteds and any person or entity (&ldquo;you&rdquo; or &ldquo;user&rdquo;) that accesses or uses our services, websites, APIs, or mobile applications (collectively, the &ldquo;Services&rdquo;). By creating an account, purchasing content, or using Noteds in any manner, you agree to these Terms and to our Privacy Policy.</p>

<h3>1. Eligibility &amp; Accounts</h3>
<ul>
    <li>You must be at least 13 years old (or older if required by your jurisdiction) to use Noteds. Parents or legal guardians are responsible for supervising minors using the Services.</li>
    <li>When registering you must provide accurate, current, and complete information. You are responsible for safeguarding your login credentials and for all activities occurring under your account.</li>
    <li>We reserve the right to suspend or terminate accounts that violate these Terms, abuse refund systems, upload prohibited content, or pose a security risk.</li>
</ul>

<h3>2. Roles on Noteds</h3>
<ul>
    <li><strong>Buyers</strong> purchase notes, join premium workspaces, bookmark materials, and participate in community features.</li>
    <li><strong>Sellers</strong> upload content, set pricing (subject to platform rules), manage promotions, and receive payouts.</li>
    <li><strong>Admins &amp; Moderators</strong> oversee marketplace operations, review reports, and enforce community guidelines.</li>
</ul>

<h3>3. Seller Responsibilities</h3>
<ul>
    <li>Content must be original, lawful, and clearly described. You must own the rights to distribute the materials or have obtained necessary licenses.</li>
    <li>Sellers grant Noteds a worldwide, non-exclusive, royalty-free license to host, display, and distribute their content inside the Service for as long as the listing remains active.</li>
    <li>Sellers are responsible for complying with applicable tax regulations. We may require tax forms or identity verification before disbursing payouts.</li>
</ul>

<h3>4. Purchasing and Payments</h3>
<ul>
    <li>All prices are listed in the platform currency and may change at any time. Sales tax or VAT may be added according to your billing location.</li>
    <li>Payments are processed through the Noteds wallet or integrated payment partners. By purchasing, you authorize us to bill your selected payment method.</li>
    <li>Digital products are typically non-refundable. However, we may issue refunds at our discretion if the file is corrupted, misleading, or violates platform policies.</li>
    <li>Download links are limited to personal use. Reselling, sharing publicly, or redistributing purchased materials is prohibited without explicit permission from the seller.</li>
</ul>

<h3>5. Fees and Payouts</h3>
<ul>
    <li>Noteds charges a platform service fee on each transaction. Commission rates may vary by membership tier, promotional campaign, or payment channel.</li>
    <li>Payout requests are processed to the seller&rsquo;s registered bank account or e-wallet within the timeframes communicated in the seller dashboard. Minimum withdrawal thresholds may apply.</li>
    <li>We may withhold payouts if fraud is suspected, if a chargeback occurs, or if the seller&rsquo;s account is under review.</li>
</ul>

<h3>6. Community Rules</h3>
<ul>
    <li>Respect other members. Harassment, hate speech, spam, or impersonation is not tolerated.</li>
    <li>Forum posts, comments, and messages must remain relevant and constructive. Moderators may remove content or restrict access at their discretion.</li>
    <li>Do not upload malware, phishing links, or any content that could disrupt service performance or compromise user security.</li>
</ul>

<h3>7. Intellectual Property</h3>
<ul>
    <li>All trademarks, logos, and platform code remain the property of Noteds and its licensors.</li>
    <li>By publishing content you affirm that you own or control all necessary rights. You retain ownership of your materials while granting the licenses described above.</li>
    <li>If you believe material on Noteds infringes your intellectual property, submit a notice to <a href="mailto:info@noteds.com">info@noteds.com</a> with supporting documentation.</li>
</ul>

<h3>8. Disclaimers</h3>
<ul>
    <li>Noteds provides an online marketplace and community on an &ldquo;as-is&rdquo; basis. We do not guarantee uninterrupted access, error-free content, or specific learning outcomes.</li>
    <li>Sellers, not Noteds, are responsible for the accuracy, legality, and quality of the content they provide. Buyers should evaluate materials and sellers before making a purchase.</li>
</ul>

<h3>9. Limitation of Liability</h3>
<p>To the maximum extent permitted by law, Noteds shall not be liable for indirect, incidental, special, consequential, or punitive damages arising from your use of the Services. Our total liability for any claim shall not exceed the amount you paid to Noteds during the six (6) months preceding the event giving rise to the claim.</p>

<h3>10. Indemnity</h3>
<p>You agree to indemnify, defend, and hold harmless Noteds, its affiliates, directors, employees, and partners from any claims or damages arising from your breach of these Terms, your misuse of the Services, or your violation of any law or third-party rights.</p>

<h3>11. Suspension &amp; Termination</h3>
<p>We may suspend or permanently terminate access to the Services if we reasonably believe that a user has violated these Terms, engaged in fraudulent activity, or posed a security risk. Users may also deactivate their accounts at any time. Certain provisions of these Terms shall survive termination, including intellectual property, disclaimers, and limitation of liability.</p>

<h3>12. Governing Law &amp; Disputes</h3>
<p>Unless otherwise required by local law, these Terms are governed by the laws of Indonesia. Any dispute will first be addressed through our internal support team. If a resolution cannot be reached, parties agree to fair and informal mediation before pursuing formal legal proceedings.</p>

<h3>13. Changes to the Terms</h3>
<p>We may modify these Terms to reflect product updates or regulatory changes. Updated Terms will be posted on this page with a new effective date. Continued use of Noteds after changes become effective constitutes acceptance of the revised Terms.</p>

<h3>14. Contact</h3>
<p>Questions about these Terms can be sent to <a href="mailto:info@noteds.com">info@noteds.com</a> or submitted through our support ticket system.</p>
HTML,
                'meta_title' => 'Terms and Conditions - Noteds',
                'meta_description' => 'Review the terms and conditions for using Noteds.com and participating in the digital notes marketplace.',
                'is_active' => true,
            ],
            [
                'slug' => 'user-agreement',
                'title' => 'User Agreement',
                'content' => <<<HTML
<h2>User Agreement</h2>
<p>Effective date: 17 November 2025</p>

<p>Welcome to Noteds. By creating an account, purchasing content, or using Noteds in any manner, you agree to this User Agreement and to our <a href="/page/terms-and-conditions">Terms & Conditions</a> and <a href="/page/privacy-policy">Privacy Policy</a>.</p>

<h3>1. Account Registration</h3>
<ul>
    <li>You must be at least 13 years old (or older if required by your jurisdiction) to use Noteds.</li>
    <li>When registering, you must provide accurate, current, and complete information.</li>
    <li>You are responsible for safeguarding your login credentials and for all activities occurring under your account.</li>
    <li>You agree to complete your profile verification by uploading KTP (Identity Card) and selfie photo as required for identity verification.</li>
</ul>

<h3>2. User Responsibilities</h3>
<ul>
    <li><strong>Buyers</strong> agree to use purchased content for personal use only and not to redistribute, resell, or share purchased materials without explicit permission from the seller.</li>
    <li><strong>Sellers</strong> agree to provide original, lawful content and to accurately describe their listings. Sellers grant Noteds a worldwide, non-exclusive, royalty-free license to host and display their content.</li>
    <li>All users agree to respect community guidelines, refrain from harassment, hate speech, spam, or illegal activities.</li>
</ul>

<h3>3. Verification & Identity</h3>
<ul>
    <li>Noteds requires identity verification (KTP and selfie) for account security and compliance with legal requirements.</li>
    <li>Verification documents are securely stored and only accessible by authorized administrators.</li>
    <li>Your verification status may affect your ability to withdraw funds or use certain platform features.</li>
</ul>

<h3>4. Payments & Transactions</h3>
<ul>
    <li>All transactions on Noteds are processed through secure payment systems.</li>
    <li>Platform fees may apply to transactions as disclosed at the point of purchase.</li>
    <li>Digital products are typically non-refundable, except as required by law or at Noteds' discretion.</li>
</ul>

<h3>5. Platform Fees</h3>
<ul>
    <li>Noteds charges platform fees on transactions to support platform operations and development.</li>
    <li>Fee rates are disclosed at the point of transaction and may vary based on membership status or promotional campaigns.</li>
</ul>

<h3>6. Content Guidelines</h3>
<ul>
    <li>Content must be original, lawful, and clearly described.</li>
    <li>Prohibited content includes: malware, phishing links, illegal materials, or content that violates intellectual property rights.</li>
    <li>Noteds reserves the right to remove content that violates these guidelines.</li>
</ul>

<h3>7. Account Suspension & Termination</h3>
<ul>
    <li>Noteds may suspend or terminate accounts that violate this agreement, engage in fraudulent activity, or pose a security risk.</li>
    <li>Users may deactivate their accounts at any time through account settings.</li>
    <li>Certain provisions of this agreement shall survive termination, including intellectual property, disclaimers, and limitation of liability.</li>
</ul>

<h3>8. Changes to This Agreement</h3>
<p>We may modify this User Agreement to reflect product updates or regulatory changes. Updated agreements will be posted on this page with a new effective date. Continued use of Noteds after changes become effective constitutes acceptance of the revised agreement.</p>

<h3>9. Contact</h3>
<p>Questions about this User Agreement can be sent to <a href="mailto:info@noteds.com">info@noteds.com</a> or submitted through our support ticket system.</p>
HTML,
                'meta_title' => 'User Agreement - Noteds',
                'meta_description' => 'Review the user agreement for using Noteds.com marketplace platform.',
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
