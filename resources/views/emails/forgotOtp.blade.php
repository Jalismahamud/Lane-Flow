@extends('emails.masterEmailLayout')

@section('content')
  <h1>🔐 Verify Your OTP</h1>

  <p style="margin-bottom:6px;">Hi {{ $user->name ?? 'there' }},</p>

  <p style="margin-top:0; margin-bottom:14px;">We received a request to reset the password for your <strong>{{ config('app.name') }}</strong> account ({{ $user->email ?? '' }}). Use the OTP below to continue.</p>

  <div class="verification-box">
    <div class="code-label">Your OTP Code</div>
    <div class="otp">{{ $otp }}</div>
    <div class="note">This code will expire in 5 minutes.</div>
  </div>

  <div class="security-info" style="margin-top:12px;">
    <p><strong>🛡️ Security Notice:</strong> Never share this code with anyone. If you didn't request a password reset, please ignore this email or contact our support team.</p>
  </div>

  <p style="margin-top:10px; font-size:14px;">If you didn't request this, please ignore this message or contact support.</p>
@endsection
