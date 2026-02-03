<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice #{{ $payment->transaction_id }}</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 40px;
            color: #000c52;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header .logo {
            font-size: 40px;
            font-weight: bold;
            color: #1e88e5;
        }

        .header .invoice-title {
            background-color: #000c52;
            color: white;
            padding: 20px 40px;
            border-top-left-radius: 40px;
            font-size: 28px;
            font-weight: bold;
        }

        .info-table {
            width: 100%;
            margin-top: 30px;
            font-size: 15px;
        }

        .info-table td {
            padding: 5px 0;
        }

        .gradient-header {
            background: linear-gradient(to right, #000c52, #2af599);
            color: white;
            padding: 12px 20px;
            font-weight: bold;
            display: flex;
            justify-content: space-between;
        }

        .payment-section {
            border-left: 5px solid #000c52;
            border-right: 5px solid #2af599;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            font-size: 15px;
            font-weight: bold;
        }

        .payment-left {
            color: #000c52;
        }

        .payment-right {
            color: #00a762;
        }

        .sub-label {
            font-weight: normal;
            font-size: 14px;
            margin-top: 8px;
        }

        .words {
            margin-top: 20px;
            font-size: 13px;
            border: 1px solid #ccc;
            padding: 8px;
        }

        .due-bar {
            background: #000c52;
            color: white;
            text-align: center;
            padding: 10px;
            font-weight: bold;
            margin-top: 30px;
            font-size: 16px;
        }

        .footer {
            margin-top: 40px;
            font-size: 13px;
            text-align: center;
            color: #666;
        }

        .footer a {
            color: #1e88e5;
            text-decoration: none;
        }

        @media print {
            .payment-section {
                font-size: 13px; /* smaller size for print */
            }
        }

        .page-container {
            max-width: 800px;
            margin: 0 auto;
        }

        /* Remove max-width and centering for print */
        @media print {
            .page-container {
                max-width: 100% !important;
                margin: 0 !important;
            }
        }
    </style>
</head>
<body onload="window.print()" class="page-container">

<div class="header">
    <div class="logo">
        <img src="/images/logo.png" alt="Logo ACIDM" style="height: 100px;">
    </div>
    <div class="invoice-title">INVOICE</div>
</div>

<table class="info-table">
    <tr>
        <td>
            <span style="display: inline-block; width: 120px;"><strong>Invoice #:</strong></span>
            {{ $payment->transaction_id }}
        </td>

        <td>
            <span style="display: inline-block; width: 120px;"><strong>Date:</strong></span>
            {{ $payment->created_at->format('d-m-Y') }}
        </td>
    </tr>
    <tr>
        <td>
            <span style="display: inline-block; width: 120px;">
                <strong>Student ID:</strong>
            </span>
            {{ $payment->enrollment?->student?->student_id }}</td>
        <td>
            <span style="display: inline-block; width: 120px;">
                <strong>Reg ID:</strong>
            </span>
            {{ $payment->enrollment->student_reg_id }}</td>
    </tr>
    <tr>
        <td>
            <span style="display: inline-block; width: 120px;">
                <strong>Student:</strong>
            </span>
            {{ $payment->enrollment->student->title }} {{ $payment->enrollment->student->name }}
        </td>
            <span style="display: inline-block; width: 120px;">
                <strong>Cashier:</strong>
            </span>
            {{ $payment->cashier?->name }}</td>
    </tr>
    <tr>
        <td colspan="2">
            <span style="display: inline-block; width: 120px;">
                <strong>Pay. Method:</strong>
            </span>
            {{ \Illuminate\Support\Str::title($payment->pay_method) }}</td>
    </tr>
</table>

<div class="gradient-header">
    <div>Programme</div>
    <div>Paid Amount</div>
</div>

<div class="payment-section">
    <div class="payment-left">
        {{ $payment->enrollment->course->title }} with {{ $payment->enrollment->course->category->name }}
        <span class="sub-label"></span><br>
        <span class="sub-label">
            ({{ $payment->feePlan?->label }})
        </span>
    </div>
    <div class="payment-right">
        <p>LKR {{ number_format($payment->amount) }}</p>
        @if($payment->usd_amount && $payment->usd_amount > 0)
            <p>USD {{ number_format($payment->usd_amount) }}</p>
        @endif
    </div>
</div>

<div class="words">
    Amount in words: {{ \Illuminate\Support\Str::title(numberToWords($payment->amount)) }} LKR Only
</div>

<div class="footer">
    Note that government taxes may be applicable for your payments.
</div>

@if($nextDues && count($nextDues))
    <div class="due-bar">
        NEXT DUE ON {{ $nextDues['due_date']?->format('d-M-Y') }} &nbsp;&nbsp;
        LKR {{ number_format($nextDues['balance_payable']) }}
    </div>
@endif

<div class="footer" style="margin-top: 30px;">
    Hotline: <a href="tel:+94117909909">0117 909 909</a> &nbsp;&nbsp;&nbsp;&nbsp; <a href="https://www.acidm.org">www.acidm.org</a><br>
    &copy; Copyright {{ now()->year }} Asia Chartered Institute of Digital Marketing. All Rights Reserved
</div>

</body>
</html>
