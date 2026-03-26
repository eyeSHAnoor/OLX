{{-- resources/views/emails/verification-code.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="x-apple-disable-message-reformatting">
    <title>Verify Your Email Address</title>
    <style>
        /* Reset styles */
        body, html {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            background-color: #f5f5f5;
        }
        
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        
        .card {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }
        
        .header {
            background: #ffffff;
            padding: 32px 32px 0 32px;
            text-align: center;
            border-bottom: 1px solid #e9ecef;
        }
        
        .logo {
            margin-bottom: 24px;
        }
        
        .logo img {
            max-height: 40px;
            width: auto;
        }
        
        .content {
            padding: 32px;
        }
        
        h1 {
            color: #1a1a1a;
            font-size: 24px;
            font-weight: 600;
            margin: 0 0 16px 0;
            line-height: 1.3;
        }
        
        .greeting {
            font-size: 16px;
            color: #4a5568;
            margin-bottom: 24px;
        }
        
        .message {
            color: #4a5568;
            margin-bottom: 32px;
        }
        
        .code-container {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 24px;
            text-align: center;
            margin: 32px 0;
            border: 1px solid #e9ecef;
        }
        
        .code {
            font-family: 'SF Mono', 'Monaco', 'Inconsolata', monospace;
            font-size: 36px;
            font-weight: 700;
            letter-spacing: 4px;
            color: #2c3e50;
            background: #ffffff;
            display: inline-block;
            padding: 16px 32px;
            border-radius: 8px;
            border: 1px solid #dee2e6;
        }
        
        .expiry {
            background-color: #fff5f0;
            border-left: 4px solid #f97316;
            padding: 16px 20px;
            margin: 24px 0;
            border-radius: 6px;
        }
        
        .expiry strong {
            color: #f97316;
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .expiry p {
            margin: 0;
            color: #7c3a1e;
            font-size: 14px;
        }
        
        .security-note {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 16px 20px;
            margin: 32px 0 24px;
            font-size: 13px;
            color: #6c757d;
            border: 1px solid #e9ecef;
        }
        
        .security-note strong {
            color: #495057;
        }
        
        hr {
            border: none;
            border-top: 1px solid #e9ecef;
            margin: 32px 0 24px;
        }
        
        .footer {
            font-size: 12px;
            color: #868e96;
            text-align: center;
            padding: 0 32px 32px;
        }
        
        .footer p {
            margin: 0 0 8px 0;
        }
        
        @media only screen and (max-width: 480px) {
            .container {
                padding: 20px 16px;
            }
            
            .content {
                padding: 24px;
            }
            
            .code {
                font-size: 28px;
                padding: 12px 24px;
                letter-spacing: 2px;
            }
            
            h1 {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="header">
              <div class="logo" style="text-align: center; margin-bottom: 24px;">
                <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}" style="max-width: 100px; height: auto; display: inline-block;">
            </div>
            </div>
            
            <div class="content">
                <h1>Verify your email address</h1>
                
                <div class="greeting">
                    Hello {{ $user->name }},
                </div>
                
                <div class="message">
                    Thanks for creating an account with {{ config('app.name') }}. Please use the verification code below to complete your registration.
                </div>
                
                <div class="code-container">
                    <div class="code">{{ $code }}</div>
                </div>
                
                <div class="expiry">
                    <strong>Time-sensitive verification</strong>
                    <p>This code will expire in <strong>{{ $expiryMinutes }} minutes</strong>. Please enter it on the verification page before it expires.</p>
                </div>
                
                <div class="security-note">
                    <strong>Security reminder</strong><br>
                    Never share this code with anyone. {{ config('app.name') }} will never ask for your verification code, password, or sensitive information via email or phone.
                </div>
                
                <hr>
                
                <div class="footer">
                    <p>If you didn't request this verification code, please ignore this email or contact our support team.</p>
                    <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>