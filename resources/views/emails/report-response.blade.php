<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Response</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .header {
            background-color: #dc2626;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background-color: white;
            padding: 30px;
            border-radius: 0 0 8px 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .report-details {
            background-color: #f8f9fa;
            border-left: 4px solid #dc2626;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .response-message {
            background-color: #f0f9ff;
            border: 1px solid #bae6fd;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            font-style: italic;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            color: #6c757d;
            font-size: 0.9em;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 600;
        }
        .status-resolved { background-color: #10b981; color: white; }
        .status-reviewed { background-color: #3b82f6; color: white; }
        .status-rejected { background-color: #6b7280; color: white; }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #dc2626;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Report Update</h1>
        </div>
        
        <div class="content">
            <p>Hello {{ $report->reporter->name }},</p>
            
            <p>Thank you for taking the time to report content on {{ config('app.name') }}. Your report helps us maintain a safe and trustworthy community.</p>
            
            <div class="report-details">
                <h3 style="margin-top: 0;">Report Details:</h3>
                <p><strong>Report ID:</strong> #{{ $report->id }}</p>
                <p><strong>Date Submitted:</strong> {{ $report->created_at->format('F j, Y, g:i a') }}</p>
                <p><strong>Reason:</strong> {{ ucfirst(str_replace('_', ' ', $report->reason)) }}</p>
                <p><strong>Status:</strong> 
                    <span class="status-badge status-{{ $report->status }}">
                        {{ ucfirst($report->status) }}
                    </span>
                </p>
                @if($report->ad)
                <p><strong>Reported Ad:</strong> {{ $report->ad->ad_title }}</p>
                @endif
                <p><strong>Reported User:</strong> {{ $report->reportedUser->name }}</p>
            </div>
            
            <h3>Our Response:</h3>
            <div class="response-message">
                {{ $report->admin_response }}
            </div>
            
            <p>We have reviewed your report and taken appropriate action based on our community guidelines.</p>
            
            <p>If you have any further questions or concerns, please don't hesitate to contact our support team.</p>
            
            <div style="text-align: center;">
                <a href="{{ route('home') }}" class="button">Visit {{ config('app.name') }}</a>
            </div>
            
            <p>Thank you for helping keep our community safe!</p>
            
            <p>Best regards,<br>
            The {{ config('app.name') }} Team</p>
        </div>
        
        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            <p>This email was sent in response to your report. Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>