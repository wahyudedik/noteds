<!-- Affiliate Landing Page Template -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Special Offer - Limited Time</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 40px;
            max-width: 600px;
            width: 100%;
        }

        h1 {
            color: #333;
            margin-bottom: 20px;
            font-size: 32px;
            text-align: center;
        }

        .offer-section {
            background: #f5f5f5;
            border-left: 4px solid #667eea;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
        }

        .offer-section p {
            color: #666;
            line-height: 1.6;
            margin: 10px 0;
        }

        .price {
            font-size: 48px;
            color: #667eea;
            font-weight: bold;
            text-align: center;
            margin: 20px 0;
        }

        .features {
            list-style: none;
            margin: 20px 0;
        }

        .features li {
            padding: 10px 0;
            color: #666;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
        }

        .features li:before {
            content: "✓";
            color: #4caf50;
            font-weight: bold;
            margin-right: 10px;
            font-size: 18px;
        }

        .features li:last-child {
            border-bottom: none;
        }

        .cta-section {
            text-align: center;
            margin: 30px 0;
        }

        #affiliate-click-button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 16px 48px;
            font-size: 18px;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        #affiliate-click-button:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
        }

        #affiliate-click-button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        #affiliate-click-button:active:not(:disabled) {
            transform: translateY(0);
        }

        .click-feedback {
            margin-top: 16px;
            padding: 12px 16px;
            border-radius: 8px;
            text-align: center;
            display: none;
            font-weight: 500;
            animation: slideIn 0.3s ease;
        }

        .click-feedback-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .click-feedback-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .click-feedback-warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
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

        .stats {
            background: #f9f9f9;
            padding: 16px;
            border-radius: 8px;
            margin-top: 20px;
            font-size: 12px;
            color: #999;
            text-align: center;
        }

        .timer {
            background: #fff3cd;
            padding: 12px;
            border-radius: 8px;
            margin-top: 16px;
            text-align: center;
            color: #856404;
            font-weight: 600;
        }

        @media (max-width: 600px) {
            .container {
                padding: 20px;
            }

            h1 {
                font-size: 24px;
            }

            .price {
                font-size: 36px;
            }

            #affiliate-click-button {
                padding: 14px 32px;
                font-size: 16px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>🎉 Limited Time Offer!</h1>

        <div class="offer-section">
            <h2 style="color: #667eea; font-size: 20px; margin-bottom: 10px;">
                Exclusive Deal Just For You
            </h2>
            <p>
                Get access to premium features at an incredible price. This offer is only available for the next few
                hours!
            </p>
        </div>

        <div class="price">
            50% OFF
        </div>

        <ul class="features">
            <li>Unlimited access to all features</li>
            <li>24/7 customer support</li>
            <li>Money-back guarantee</li>
            <li>Free premium content library</li>
            <li>Lifetime updates included</li>
        </ul>

        <div class="timer">
            ⏰ Offer expires in: <span id="countdown">23:59:59</span>
        </div>

        <div class="cta-section">
            <button id="affiliate-click-button" data-affiliate-code="{{ $affiliateCode }}"
                data-destination="https://checkout.example.com?aff={{ $affiliateCode }}">
                Claim Your Offer Now
            </button>

            <div id="click-feedback"></div>

            <p style="margin-top: 16px; color: #999; font-size: 12px;">
                ✓ Secure payment • ✓ Fast processing • ✓ 100% confidential
            </p>
        </div>

        <div class="stats">
            <p>
                <strong id="stat-clicks">1,247</strong> people claimed this offer today
            </p>
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
