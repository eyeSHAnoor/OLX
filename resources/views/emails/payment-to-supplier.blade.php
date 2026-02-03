<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment Confirmation</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
        }

        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            overflow: hidden;
        }

        .email-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 40px;
            text-align: center;
        }

        .email-header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }

        .email-body {
            padding: 40px;
        }

        .order-info {
            background-color: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 15px 20px;
            margin-bottom: 25px;
            border-radius: 4px;
        }

        .order-number {
            font-size: 18px;
            font-weight: 600;
            color: #2d3748;
            margin: 0;
        }

        .message-content {
            background-color: #f8f9fa;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 20px;
            margin: 25px 0;
            white-space: pre-line;
        }

        .attachment-info {
            background-color: #e8f5e8;
            border: 1px solid #c8e6c9;
            border-radius: 6px;
            padding: 15px 20px;
            margin: 25px 0;
        }

        .attachment-info strong {
            color: #2e7d32;
        }

        .contact-info {
            background-color: #e3f2fd;
            border: 1px solid #bbdefb;
            border-radius: 6px;
            padding: 15px 20px;
            margin: 25px 0;
        }

        .footer {
            background-color: #f8f9fa;
            padding: 20px 40px;
            text-align: center;
            border-top: 1px solid #e0e0e0;
            color: #666;
            font-size: 14px;
        }

        .company-name {
            font-weight: 600;
            color: #667eea;
            font-size: 16px;
        }

        .signature {
            margin-top: 25px;
            padding-top: 25px;
            border-top: 1px solid #e2e8f0;
        }

        .btn-reply {
            display: inline-block;
            background-color: #667eea;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            margin: 20px 0;
        }

        .btn-reply:hover {
            background-color: #5a6fd8;
        }
    </style>
</head>

<body>
    <div class="email-container">
        <!-- Header -->
        <div class="email-header">
            <h1>Payment Confirmation</h1>
        </div>

        <!-- Body -->
        <div class="email-body">
            <!-- Order Information -->
            <div class="order-info">
                <p class="order-number">Order #{{ $orderNumber }}</p>
            </div>

            <!-- Custom Message -->
            <div class="message-content">
                {!! nl2br(e($customMessage)) !!}
            </div>

            <!-- Attachment Information -->
            <div class="attachment-info">
                <p><strong>📎 Attachment Included:</strong></p>
                <p>Payment confirmation document for Order #{{ $orderNumber }}</p>
                <p><em>The attached PDF file contains the official payment confirmation for your records.</em></p>
            </div>

            <!-- Contact Information -->
            <div class="contact-info">
                <p><strong>💼 Sent by:</strong> {{ $sentBy }}</p>
                <p><strong>🏢 Company:</strong> RIVALITAS D.O.O.</p>
                <p><strong>📅 Date:</strong> {{ now()->format('F j, Y') }}</p>
            </div>

            <!-- Reply Instructions -->
            <div style="text-align: center;">
                <p><strong>Need to respond to this email?</strong></p>
                <p>Please simply reply to this email and your response will be automatically directed to the appropriate
                    department.</p>
                <!-- This creates a mailto link that will reply to the original sender -->
                <a href="mailto:{{ config('mail.from.address', 'accounting@rivalitas.com') }}?subject=Re: Payment Confirmation for Order #{{ $orderNumber }}&body=Dear RIVALITAS D.O.O.,%0D%0A%0D%0A"
                    class="btn-reply">
                    📧 Reply to this Email
                </a>
            </div>

            <!-- Signature -->
            <div class="signature">
                <p>Best regards,</p>
                <p><strong>{{ $sentBy }}</strong><br>
                    <span class="company-name">RIVALITAS D.O.O.</span>
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>This is an automated email from RIVALITAS D.O.O. Order Management System.</p>
            <p>If you have any questions about this payment confirmation, please reply directly to this email.</p>
            <p>&copy; {{ date('Y') }} RIVALITAS D.O.O. All rights reserved.</p>
        </div>
    </div>
</body>

</html>