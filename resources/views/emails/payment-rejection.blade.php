<!DOCTYPE html>
<html>

<head>
    <title>Payment Confirmation Incorrect</title>
</head>

<body>
    <h2>Payment Confirmation Required - Correction Needed</h2>

    <p>Hello Accounting Team,</p>

    <p>The payment confirmation document submitted for <strong>Order #{{ $orderNumber }}</strong> has been rejected.</p>

    @if($rejectionReason)
        <p><strong>Reason for rejection:</strong> {{ $rejectionReason }}</p>
    @endif

    <p><strong>Rejected by:</strong> {{ $rejectedBy }}</p>
    <p><strong>Date:</strong> {{ now()->format('F j, Y g:i A') }}</p>

    <p>Please review and upload the correct payment confirmation document.</p>

    <p>
        You can upload the corrected document here:<br>
        <a href="{{ route('tracking.orders.payment-confirmation', $orderId) }}">
            {{ route('tracking.orders.payment-confirmation', $orderId) }}
        </a>
    </p>

    <p>Thank you,<br>
        Your System</p>
</body>

</html>