<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy - Laneflow</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #2563eb 0%, #3b82f6 50%, #60a5fa 100%);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
            min-height: 100vh;
            padding: 20px;
        }

        .header {
            text-align: center;
            padding: 40px 20px 30px;
            max-width: 1000px;
            margin: 0 auto;
        }

        .logo-container {
            margin-bottom: 20px;
        }

        .logo {
            width: 100px;
            height: 100px;
            margin: 0 auto 15px;
            background: white;
            border-radius: 24px;
            padding: 12px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.15);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .brand-title {
            font-size: 2.5rem;
            font-weight: 800;
            color: white;
            text-shadow: 0 2px 12px rgba(0,0,0,0.2);
            letter-spacing: -1px;
            margin-bottom: 8px;
        }

        .brand-subtitle {
            font-size: 1.1rem;
            color: rgba(255,255,255,0.95);
            font-weight: 500;
            letter-spacing: 0.5px;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
            overflow: hidden;
        }

        .page-header {
            background: linear-gradient(135deg, #1e40af 0%, #2563eb 100%);
            padding: 40px 40px 35px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        }

        .page-header h1 {
            color: white;
            font-size: 2.2rem;
            font-weight: 700;
            margin: 0;
            position: relative;
            z-index: 1;
            letter-spacing: -0.5px;
        }

        .policy-content {
            padding: 50px 50px 60px;
            color: #1e293b;
            font-size: 1.05rem;
            line-height: 1.8;
        }

        .policy-content h1 {
            color: #1e40af;
            font-size: 1.8rem;
            font-weight: 700;
            margin: 35px 0 20px;
            padding-bottom: 12px;
            border-bottom: 3px solid #e0e7ff;
        }

        .policy-content h1:first-child {
            margin-top: 0;
        }

        .policy-content h2 {
            color: #1e40af;
            font-size: 1.6rem;
            font-weight: 700;
            margin: 35px 0 18px;
            padding-bottom: 12px;
            border-bottom: 3px solid #e0e7ff;
        }

        .policy-content h2:first-child {
            margin-top: 0;
        }

        .policy-content h3 {
            color: #2563eb;
            font-size: 1.3rem;
            font-weight: 600;
            margin: 28px 0 14px;
        }

        .policy-content h4 {
            color: #3b82f6;
            font-size: 1.15rem;
            font-weight: 600;
            margin: 24px 0 12px;
        }

        .policy-content p {
            margin-bottom: 20px;
            color: #475569;
        }

        .policy-content ul,
        .policy-content ol {
            margin: 18px 0 24px 24px;
            color: #475569;
        }

        .policy-content li {
            margin-bottom: 12px;
            padding-left: 8px;
        }

        .policy-content strong,
        .policy-content b {
            color: #1e293b;
            font-weight: 600;
        }

        .policy-content a {
            color: #2563eb;
            text-decoration: none;
            font-weight: 500;
        }

        .policy-content a:hover {
            text-decoration: underline;
        }

        .policy-content blockquote {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            border-left: 4px solid #2563eb;
            padding: 20px 24px;
            margin: 25px 0;
            border-radius: 8px;
        }

        .policy-content table {
            width: 100%;
            border-collapse: collapse;
            margin: 24px 0;
        }

        .policy-content table th,
        .policy-content table td {
            padding: 12px 16px;
            border: 1px solid #e2e8f0;
            text-align: left;
        }

        .policy-content table th {
            background: #f1f5f9;
            color: #1e40af;
            font-weight: 600;
        }

        .policy-content code {
            background: #f1f5f9;
            padding: 2px 8px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-size: 0.95em;
            color: #1e40af;
        }

        .policy-content pre {
            background: #1e293b;
            color: #e2e8f0;
            padding: 20px;
            border-radius: 8px;
            overflow-x: auto;
            margin: 24px 0;
        }

        .policy-content hr {
            border: none;
            border-top: 2px solid #e2e8f0;
            margin: 35px 0;
        }

        .footer {
            background: #f8fafc;
            padding: 30px 50px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
        }

        .footer-text {
            color: #64748b;
            font-size: 0.95rem;
            line-height: 1.6;
        }

        .footer-links {
            margin-top: 15px;
        }

        .footer-links a {
            color: #2563eb;
            text-decoration: none;
            font-weight: 600;
            margin: 0 12px;
            font-size: 0.95rem;
        }

        .footer-links a:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            body {
                padding: 10px;
            }

            .header {
                padding: 30px 15px 20px;
            }

            .brand-title {
                font-size: 1.8rem;
            }

            .brand-subtitle {
                font-size: 0.95rem;
            }

            .logo {
                width: 80px;
                height: 80px;
            }

            .container {
                border-radius: 16px;
            }

            .page-header {
                padding: 30px 25px;
            }

            .page-header h1 {
                font-size: 1.6rem;
            }

            .policy-content {
                padding: 30px 25px 40px;
                font-size: 1rem;
            }

            .policy-content h1 {
                font-size: 1.5rem;
            }

            .policy-content h2 {
                font-size: 1.35rem;
            }

            .policy-content h3 {
                font-size: 1.15rem;
            }

            .policy-content h4 {
                font-size: 1.05rem;
            }

            .footer {
                padding: 25px 25px;
            }

            .footer-links {
                display: flex;
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>
<body>

    <div class="header">
        <div class="logo-container">
            <div class="logo">

                @if(config('app.logo'))
                    <img src="{{ config('app.logo') }}" alt="Laneflow Logo">
                @else
                    <img src="{{ asset('default/logo.png') }}" alt="Laneflow Logo">
                @endif

            </div>
        </div>
        <h1 class="brand-title">{{ config('app.name', 'LANEFLOW') }}</h1>
        <p class="brand-subtitle">TRAFFIC & EMERGENCY AID</p>
    </div>

    <div class="container">
        <div class="page-header">
            <h1>Privacy Policy</h1>
        </div>

        <div class="policy-content">
            {!! $content !!}
        </div>

        <div class="footer">
            <p class="footer-text">
                <strong>&copy; {{ date('Y') }} {{ config('app.name', 'Laneflow') }} - Traffic & Emergency Aid</strong><br>
                All rights reserved. Keeping you safe on every journey.
            </p>
            <div class="footer-links">
                <a href="{{ url('/terms-and-conditions') }}">Terms and Conditions</a>
                <a href="{{ url('/privacy-policy') }}">Privacy Policy</a>
            </div>
        </div>
    </div>
</body>
</html>
