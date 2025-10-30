{{-- Master Email Layout for all emails --}}
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $title ?? config('app.name') }}</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Helvetica Neue', sans-serif;
            color: #1e293b;
        }

        .email-wrapper {
            width: 100%;
            table-layout: fixed;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 15px;
        }

        .email-content {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
        }

        .header-section {
            background: linear-gradient(135deg, #4f5fd8 0%, #5b4cc7 100%);
            padding: 30px 30px 20px;
            text-align: center;
            position: relative;
        }

        .header-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><circle cx="10" cy="10" r="2" fill="rgba(255,255,255,0.1)"/></svg>') repeat;
            opacity: 0.3;
        }

        .logo-container {
            position: relative;
            z-index: 1;
        }

        .logo-image {
            max-width: 80px;
            height: auto;
            margin-bottom: 15px;
        }

        .brand-name {
            font-size: 32px;
            font-weight: 700;
            color: #ffffff;
            margin: 0;
            letter-spacing: -0.5px;
        }

        .brand-tagline {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.85);
            margin-top: 5px;
            font-weight: 400;
        }

        .content {
            /* reduced vertical padding to tighten gap above footer */
            padding: 30px 40px 18px;
            text-align: center;
        }

        .content h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 15px;
            color: #1e293b;
            letter-spacing: -0.5px;
        }

        .content p {
            font-size: 16px;
            color: #64748b;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .verification-box {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border: 2px solid #e2e8f0;
            border-radius: 16px;
            padding: 30px 22px;
            /* smaller vertical margin to reduce empty space */
            margin: 18px 0;
            position: relative;
            overflow: hidden;
        }

        .verification-box::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(79, 95, 216, 0.05) 0%, transparent 70%);
        }

        .code-label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #4f5fd8;
            margin-bottom: 15px;
            font-weight: 600;
            position: relative;
            z-index: 1;
        }

        .otp {
            font-size: 42px;
            font-weight: 800;
            color: #1e293b;
            letter-spacing: 8px;
            font-family: 'SF Mono', 'Courier New', monospace;
            position: relative;
            z-index: 1;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .note {
            font-size: 13px;
            color: #64748b;
            margin-top: 15px;
            font-weight: 500;
            position: relative;
            z-index: 1;
        }

        .security-info {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px 10px;
            margin-top: 6px;
            border-radius: 8px;
            text-align: left;
        }

        .security-info p {
            margin: 0;
            font-size: 13px;
            color: #92400e;
            line-height: 1.5;
        }

        .security-info strong {
            color: #78350f;
        }

        .footer {
            background: #f8fafc;
            /* reduce top padding so footer sits closer to content */
            padding: 18px 40px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
        }

        .footer-links {
            margin-bottom: 15px;
        }

        .footer-links a {
            color: #4f5fd8;
            text-decoration: none;
            font-size: 13px;
            margin: 0 10px;
            font-weight: 500;
        }

        .footer-text {
            font-size: 12px;
            color: #94a3b8;
            line-height: 1.6;
        }

        .footer-text strong {
            color: #64748b;
        }

        @media screen and (max-width: 620px) {
            .email-content {
                width: 100% !important;
                border-radius: 0;
            }

            .content {
                padding: 30px 25px;
            }

            .header-section {
                padding: 25px 20px 20px;
            }

            .otp {
                font-size: 32px;
                letter-spacing: 6px;
            }

            .footer {
                padding: 25px 20px;
            }
        }
    </style>
</head>

<body>
    <table class="email-wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation">
        <tr>
            <td align="center">
                <table class="email-content" cellpadding="0" cellspacing="0" width="100%" role="presentation">
                    <tr>
                        <td class="header-section">
                            <div class="logo-container">
                                @php
                                    $icon = config('app.icon');
                                    if ($icon) {
                                        $icon = str_replace('/public/', '/', $icon);

                                        if (strpos($icon, 'http') !== 0) {
                                            $icon = url($icon);
                                        }
                                    } else {
                                        $icon = asset('default/logo.png');
                                    }
                                @endphp

                                @if ($icon)
                                    <img src="{{ $icon }}" alt="{{ config('app.name') }} Logo"
                                        class="logo-image">
                                @else
                                    <h1 class="brand-name">{{ config('app.name') }}</h1>
                                @endif

                            </div>
                        </td>
                    </tr>

                    <!-- Main Content -->
                    <tr>
                        <td class="content">
                            @yield('content')
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td class="footer">
                            <div class="footer-links">
                                <a href="{{ config('app.url') }}/privacy-policy">Privacy</a>
                                <a href="{{ config('app.url') }}/terms-and-conditions">Terms</a>
                            </div>
                            <div class="footer-text">
                                <strong>&copy; {{ date('Y') }} || {{ config('app.name') }}</strong><br>
                                All rights reserved. Crafted with care for your journey.<br>
                                <span style="color:#cbd5e1;">This email was sent to {{ $user->email ?? 'you' }}</span>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
