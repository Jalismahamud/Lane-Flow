@extends('emails.masterEmailLayout')

@section('content')
  <h1>🔐 Verify Your Email to Get Started</h1>
  <p>Welcome to {{ config('app.name') }}! We're excited to have you on board. Please use the verification code below to complete your registration.</p>

  <div class="verification-box">
    <div class="code-label">Your Verification Code</div>
    <div class="otp">{{ $otp }}</div>
    <div class="note">⏰ This code expires in 5 minutes</div>
  </div>

  <div class="security-info">
    <p><strong>🛡️ Security Notice:</strong> Never share this code with anyone. Our team will never ask for your verification code via email, phone, or any other channel.</p>
  </div>

  <p style="margin-top: 30px; font-size: 14px;">If you didn't request this code, please ignore this email or contact our support team.</p>
@endsection
