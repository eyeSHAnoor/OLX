{{-- resources/views/emails/verification-code.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email</title>
    <style>
        /* ... existing styles ... */
        .expiry {
            background-color: #fee2e2;
            border-left: 4px solid #ef4444;
            padding: 12px 16px;
            margin: 24px 0;
            font-size: 14px;
            color: #991b1b;
        }
        .timer-icon {
            display: inline-block;
            margin-right: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="logo">
                <img src="{{ asset('images/logo.png') }}" alt="OLX Clone">
            </div>
            
            <h1>Verify Your Email Address</h1>
            
            <p>Hello {{ $user->name }},</p>
            
            <p>Thank you for registering with OLX Clone! To complete your registration, please use the following verification code:</p>
            
            <div class="code">{{ $code }}</div>
            
            <div class="expiry">
                <strong>⏰ Important:</strong> This code will expire in <strong>{{ $expiryMinutes }} minutes</strong>.
                Please enter it on the verification page before it expires.
            </div>
            
            <p>If the code expires, you can request a new one on the verification page.</p>
            
            <hr>
            
            <p style="font-size: 14px;">For security reasons, never share this code with anyone. Our team will never ask for your verification code.</p>
            
            <div class="footer">
                <p>&copy; {{ date('Y') }} OLX Clone. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>