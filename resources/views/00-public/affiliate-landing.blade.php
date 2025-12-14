<!-- Affiliate Landing Page Template - Premium Design -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Exclusive limited-time offer for you">
    <title>Special Offer - Limited Time</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #6366f1;
            --secondary: #8b5cf6;
            --accent: #ec4899;
            --success: #10b981;
            --warning: #f59e0b;
            --dark: #1f2937;
            --light: #f8fafc;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
        }

        /* Animated Background */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            border-radius: 50%;
            animation: float 15s ease-in-out infinite;
            z-index: -1;
        }

        body::after {
            content: '';
            position: fixed;
            bottom: -100px;
            right: -100px;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.05) 0%, transparent 70%);
            border-radius: 50%;
            animation: float 20s ease-in-out infinite reverse;
            z-index: -1;
        }

        @keyframes float {

            0%,
            100% {
                transform: translate(0, 0);
            }

            50% {
                transform: translate(30px, -30px);
            }
        }

        .container {
            background: white;
            border-radius: 24px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.25);
            padding: 50px;
            max-width: 700px;
            width: 100%;
            backdrop-filter: blur(10px);
            position: relative;
            overflow: hidden;
        }

        /* Gradient Border Effect */
        .container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2, #667eea);
            background-size: 200% 100%;
            animation: gradientShift 3s ease infinite;
        }

        @keyframes gradientShift {

            0%,
            100% {
                background-position: 0% 0%;
            }

            50% {
                background-position: 100% 0%;
            }
        }

        .container>* {
            position: relative;
            z-index: 1;
        }

        .badge {
            display: inline-block;
            background: linear-gradient(135deg, var(--accent), var(--secondary));
            color: white;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            animation: slideInDown 0.6s ease;
        }

        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        h1 {
            color: var(--dark);
            margin-bottom: 16px;
            font-size: 48px;
            text-align: center;
            line-height: 1.2;
            font-weight: 800;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: slideInDown 0.7s ease 0.1s both;
        }

        .subtitle {
            color: #6b7280;
            font-size: 18px;
            text-align: center;
            margin-bottom: 30px;
            animation: slideInDown 0.7s ease 0.2s both;
        }

        .offer-section {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.05), rgba(118, 75, 162, 0.05));
            border: 2px solid rgba(102, 126, 234, 0.2);
            border-left: 5px solid var(--primary);
            padding: 24px;
            margin: 30px 0;
            border-radius: 16px;
            animation: slideInUp 0.7s ease 0.3s both;
        }

        .offer-section h2 {
            color: var(--primary);
            font-size: 22px;
            margin-bottom: 12px;
        }

        .offer-section p {
            color: #4b5563;
            line-height: 1.8;
            margin: 0;
            font-weight: 500;
        }

        .price-section {
            text-align: center;
            margin: 40px 0;
            animation: slideInUp 0.7s ease 0.4s both;
        }

        .price-label {
            color: #9ca3af;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 12px;
            font-weight: 700;
        }

        .price {
            font-size: 64px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 900;
            margin-bottom: 8px;
        }

        .price-subtext {
            color: #6b7280;
            font-size: 16px;
            font-weight: 500;
        }

        .features {
            list-style: none;
            margin: 30px 0;
            animation: slideInUp 0.7s ease 0.5s both;
        }

        .features li {
            padding: 16px 0;
            color: #374151;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            font-weight: 500;
            line-height: 1.6;
            transition: all 0.3s ease;
        }

        .features li:hover {
            color: var(--primary);
            padding-left: 10px;
        }

        .features li:last-child {
            border-bottom: none;
        }

        .features li::before {
            content: "✓";
            color: var(--success);
            font-weight: 900;
            margin-right: 16px;
            font-size: 20px;
            flex-shrink: 0;
        }

        .timer {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.1), rgba(251, 146, 60, 0.1));
            border: 2px solid var(--warning);
            padding: 20px;
            border-radius: 16px;
            text-align: center;
            color: var(--dark);
            font-weight: 700;
            margin: 30px 0;
            animation: slideInUp 0.7s ease 0.6s both;
        }

        .timer-icon {
            font-size: 28px;
            margin-right: 10px;
        }

        #countdown {
            font-size: 28px;
            color: var(--accent);
            font-weight: 900;
            font-family: 'Courier New', monospace;
            letter-spacing: 2px;
        }

        .cta-section {
            text-align: center;
            margin: 40px 0 30px;
            animation: slideInUp 0.7s ease 0.7s both;
        }

        #affiliate-click-button {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border: none;
            padding: 18px 56px;
            font-size: 18px;
            font-weight: 700;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            position: relative;
            overflow: hidden;
            display: inline-block;
            min-width: 300px;
        }

        #affiliate-click-button::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        #affiliate-click-button:hover:not(:disabled)::before {
            width: 300px;
            height: 300px;
        }

        #affiliate-click-button:hover:not(:disabled) {
            transform: translateY(-4px);
            box-shadow: 0 20px 50px rgba(102, 126, 234, 0.4);
        }

        #affiliate-click-button:active:not(:disabled) {
            transform: translateY(-2px);
        }

        #affiliate-click-button:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.2);
        }

        #affiliate-click-button span {
            position: relative;
            z-index: 1;
        }

        .click-feedback {
            margin-top: 16px;
            padding: 16px;
            border-radius: 12px;
            text-align: center;
            display: none;
            font-weight: 600;
            animation: slideIn 0.4s ease;
            font-size: 16px;
        }

        .click-feedback-success {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(52, 211, 153, 0.1));
            color: #047857;
            border: 2px solid rgba(16, 185, 129, 0.3);
        }

        .click-feedback-error {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.1), rgba(248, 113, 113, 0.1));
            color: #dc2626;
            border: 2px solid rgba(239, 68, 68, 0.3);
        }

        .click-feedback-warning {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.1), rgba(251, 146, 60, 0.1));
            color: #d97706;
            border: 2px solid rgba(245, 158, 11, 0.3);
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .trust-badges {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 24px;
            font-size: 13px;
            color: #6b7280;
            flex-wrap: wrap;
        }

        .trust-badges span {
            display: flex;
            align-items: center;
            gap: 6px;
            font-weight: 600;
        }

        .stats {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.05), rgba(118, 75, 162, 0.05));
            padding: 24px;
            border-radius: 16px;
            margin-top: 30px;
            font-size: 14px;
            color: #4b5563;
            text-align: center;
            border: 1px solid rgba(102, 126, 234, 0.1);
            animation: slideInUp 0.7s ease 0.8s both;
        }

        .stats-number {
            font-size: 28px;
            font-weight: 900;
            color: var(--primary);
            margin-bottom: 6px;
        }

        .stats-text {
            font-weight: 600;
            color: #6b7280;
        }

        @media (max-width: 768px) {
            .container {
                padding: 30px 20px;
            }

            h1 {
                font-size: 36px;
            }

            .subtitle {
                font-size: 16px;
            }

            .price {
                font-size: 48px;
            }

            #affiliate-click-button {
                padding: 16px 40px;
                font-size: 16px;
                min-width: 100%;
            }

            .trust-badges {
                flex-direction: column;
                gap: 12px;
            }

            .features li {
                padding: 12px 0;
            }

            .price-section {
                margin: 30px 0;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 10px;
            }

            .container {
                padding: 20px 16px;
                border-radius: 16px;
            }

            h1 {
                font-size: 28px;
                margin-bottom: 12px;
            }

            .price {
                font-size: 40px;
            }

            #affiliate-click-button {
                padding: 14px 32px;
                font-size: 15px;
            }

            .badge {
                font-size: 11px;
            }

            #countdown {
                font-size: 20px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="badge">⏰ {{ $landingPageLabel ?? 'Limited Time Offer' }}</div>

        <h1>{{ $landingPageTitle ?? '🎉 Exclusive Offer Just For You!' }}</h1>
        <p class="subtitle">
            {{ $landingPageSubtitle ?? 'Get instant access to premium features at an incredible price' }}</p>

        @if ($landingPageDescription ?? false)
            <div class="offer-section">
                <h2>{{ $landingPageOfferTitle ?? 'Why Choose Us?' }}</h2>
                <p>{{ $landingPageDescription }}</p>
            </div>
        @endif

        <div class="price-section">
            <div class="price-label">Special Offer</div>
            <div class="price">{{ $landingPagePrice ?? '50% OFF' }}</div>
            <div class="price-subtext">{{ $landingPagePriceSubtext ?? 'Only for the next few hours' }}</div>
        </div>

        <ul class="features">
            <li>{{ $feature1 ?? 'Unlimited access to all premium features' }}</li>
            <li>{{ $feature2 ?? '24/7 priority customer support' }}</li>
            <li>{{ $feature3 ?? 'Money-back guarantee' }}</li>
            <li>{{ $feature4 ?? 'Free exclusive content library' }}</li>
            <li>{{ $feature5 ?? 'Lifetime updates included' }}</li>
        </ul>

        <div class="timer">
            <span class="timer-icon">⏳</span>
            <strong>{{ $countdownLabel ?? 'Offer expires in:' }}</strong>
            <span id="countdown">23:59:59</span>
        </div>

        <div class="cta-section">
            <button id="affiliate-click-button" data-affiliate-code="{{ $affiliateCode }}"
                data-destination="https://checkout.example.com?aff={{ $affiliateCode }}">
                <span>{{ $buttonText ?? 'Claim Your Offer Now' }}</span>
            </button>

            <div id="click-feedback"></div>

            <div class="trust-badges">
                <span>✓ {{ $secureLabel ?? 'Secure Payment' }}</span>
                <span>✓ {{ $fastLabel ?? 'Instant Access' }}</span>
                <span>✓ {{ $confidentialLabel ?? '100% Confidential' }}</span>
            </div>
        </div>

        <div class="stats">
            <div class="stats-number" id="stat-clicks">1,247</div>
            <div class="stats-text">{{ $statsText ?? 'people claimed this offer today' }}</div>
        </div>
    </div>

    <!-- Click Protection Script -->
    <script src="{{ asset('js/affiliate-click-protection.js') }}"></script>

    <!-- Set affiliate code globally (fallback) -->
    <script>
        window.AFFILIATE_CODE = "{{ $affiliateCode }}";
    </script>

    <!-- Timer Script -->
    <script>
        function updateCountdown() {
            const now = Date.now();
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            tomorrow.setHours(0, 0, 0, 0);

            const diff = tomorrow - now;
            const hours = Math.floor(diff / (1000 * 60 * 60));
            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((diff % (1000 * 60)) / 1000);

            document.getElementById('countdown').textContent =
                `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
        }

        updateCountdown();
        setInterval(updateCountdown, 1000);

        // Update "people claimed" counter (simulate)
        setInterval(() => {
            const currentCount = parseInt(document.getElementById('stat-clicks').textContent.replace(/,/g, ''));
            const newCount = currentCount + Math.floor(Math.random() * 3);
            document.getElementById('stat-clicks').textContent = newCount.toLocaleString();
        }, 5000);
    </script>
</body>

</html>
