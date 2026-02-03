<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Registration Details #{{ $enrollment->student_reg_id }}</title>
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

        .info-table td {
            padding: 5px 0;
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
            . {
                font-size: 13px; /* smaller size for print */
            }
        }

        .page-container {
            max-width: 1600px;
            margin: 0 auto;
        }

        /* Remove max-width and centering for print */
        @media print {

            .page-container {
                max-width: 100% !important;
                margin: 0 !important;
            }

            body {
                margin: 10mm;
                font-size: 11px;
                line-height: 1.4;
            }

            table {
                width: 100%;
                border-collapse: collapse;
            }

            td, th {
                padding: 4px 6px;
                vertical-align: top;
            }

            strong {
                display: inline-block;
                min-width: 120px;
            }

            .info-table {
                margin-bottom: 15px;
            }

            .primary-header {
                display: flex;
                justify-content: space-between;
                font-weight: bold;
                margin-top: 20px;
                margin-bottom: 5px;
            }

            .payment-section {
                display: flex;
                justify-content: space-between;
                margin-bottom: 5px;
            }

            .payment-left {
                flex: 1;
            }

            .payment-right {
                text-align: right;
                flex: 1;
            }
        }
    </style>
</head>
<body onload="window.print()" class="page-container">


<div class="header">
    <div class="logo">
        <img src="/images/logo.png" alt="Logo ACIDM" style="height: 100px;">
    </div>
    <div class="invoice-title">Registration</div>
</div>

<!-- Registration Details Table -->
<table style="width: 100%; margin-top: 40px; font-family: Arial, sans-serif; font-size: 14px; line-height: 1.6;">
    <tr>
        <td style="width: 50%; font-weight: bold; font-size: 16px;">Registration Details</td>
        <td style="font-weight: bold; font-size: 16px;">Student Details</td>
    </tr>
    <tr>
        <td>
            <span style="display: inline-block; width: 120px;"><strong>Course:</strong></span>
            {{ $enrollment->course->title }} with {{ $enrollment->course->category->name }}
        </td>
        <td>
            <span
                style="display: inline-block; width: 120px;"><strong>{{ $enrollment->student->nic_unit }}:</strong></span>
            {{ $enrollment->student->nic_no }}
        </td>
    </tr>
    <tr>
        <td>
            <span style="display: inline-block; width: 120px;"><strong>Reg. No.:</strong></span>
            {{ $enrollment->student->student_id }}
        </td>
        <td>
            <span style="display: inline-block; width: 120px;"><strong>Full Name:</strong></span>
            {{ $enrollment->student->name }}
        </td>
    </tr>
    <tr>
        <td>
            <span style="display: inline-block; width: 120px;"><strong>Batch:</strong></span>
            {{ $enrollment->batch->code }}
        </td>
        <td>
            <span style="display: inline-block; width: 120px;"><strong>Certificate Name:</strong></span>
            {{-- Add certificate name if available --}}
        </td>
    </tr>
    <tr>
        <td>
            <span style="display: inline-block; width: 120px;"><strong>Date:</strong></span>
            {{ $enrollment->created_at->format('d-m-Y') }}
        </td>
        <td>
            <span style="display: inline-block; width: 120px;"><strong>Address:</strong></span>
            @php
                $address = $enrollment->student?->addresses?->where('type', 'permanent')->first();
            @endphp
            <span> {{ $address->address_line_1 }}, {{ $address->address_line_2 }},
            {{ $address->city }},
            {{ $address->country }}
            </span>
        </td>
    </tr>
    <tr>
        <td>
            <span style="display: inline-block; width: 120px;"><strong>Reg. Expiry:</strong></span>
            {{ $enrollment->university_expiry_date }}
        </td>
        <td>
            <span style="display: inline-block; width: 120px;"><strong>Email</strong></span>
            {{ $enrollment->student?->email }}
        </td>
    </tr>

    <tr>
        <td>
        </td>
        <td>
            <span style="display: inline-block; width: 120px;"><strong>Phone</strong></span>
            {{ $enrollment->student?->phone }}
        </td>
    </tr>
</table>


<table style="width: 100%; border-collapse: collapse; margin-top: 30px; font-size: 14px;">
    <tr>
        <td colspan="2" style="background: #000c52; color: white; padding: 10px; font-weight: bold;">
            Payment Plan
        </td>
    </tr>

    @php
        $localFeeSum = $enrollment->feePlans?->where('type', 'local')->sum('final_amount');
        $foreignFeeSum = $enrollment->feePlans?->where('type', '!=', 'local')->sum('amount');
    @endphp

    @foreach($enrollment->feePlans as $fee)
        <tr>
            <td style="padding: 8px 12px; border: 1px solid #e0e0e0; width: 70%;">
                <strong>{{ $fee->label }}</strong>
            </td>
            <td style="padding: 8px 12px; border: 1px solid #e0e0e0; width: 30%;">
                {{ number_format($fee->amount) }} {{ $fee->currency }}
            </td>
        </tr>
    @endforeach

    <tr>
        <td colspan="2" style="background: #000c52; color: white; padding: 10px; font-weight: bold;">
            Fee Payable Summary
        </td>
    </tr>

    <tr>
        <td style="padding: 8px 12px; border: 1px solid #e0e0e0;">
            Total Payable Fee (Local)
        </td>
        <td style="padding: 8px 12px; border: 1px solid #e0e0e0;">
            LKR {{ number_format($localFeeSum) }}
        </td>
    </tr>

    <tr>
        <td style="padding: 8px 12px; border: 1px solid #e0e0e0;">
            Total Payable Fee (Foreign)
        </td>
        <td style="padding: 8px 12px; border: 1px solid #e0e0e0;">
            USD {{ number_format($foreignFeeSum) }}
        </td>
    </tr>
</table>


@php
    $grouped = collect($enrollment->installmentPlans)
        ->groupBy('installment_no');

    $columns = collect($enrollment->installmentPlans)
        ->map(fn ($p) => $p['label'] . ' (' . $p['currency'] . ')')
        ->unique()
        ->values();
@endphp

<table style="width: 100%; border-collapse: collapse; border: 1px solid #ccc; font-size: 13px; margin-top: 20px">
    <thead>
    <tr>
        <td colspan="5" style="background: #000c52; color: white; padding: 10px; font-weight: bold;">
            Installment(s) Plan
        </td>
    </tr>

    <tr>
        <th style="border: 1px solid #ccc; padding: 5px; text-align: left;">Installment No</th>
        <th style="border: 1px solid #ccc; padding: 5px; text-align: left;">Due Date</th>
        @foreach ($columns as $col)
            <th style="border: 1px solid #ccc; padding: 5px; text-align: left;">{{ $col }}</th>
        @endforeach
    </tr>
    </thead>
    <tbody>
    @foreach ($grouped as $installmentNo => $plans)
        @php
            $dueDate = $plans->first()['due_date'] ?? null;
        @endphp
        <tr>
            <td style="border: 1px solid #ccc; padding: 5px;">{{ $installmentNo }}</td>
            <td style="border: 1px solid #ccc; padding: 5px;">
                {{ \Carbon\Carbon::parse($dueDate)->format('d M Y') }}
            </td>
            @foreach ($columns as $col)
                @php
                    $plan = $plans->firstWhere(fn ($p) => ($p['label'] . ' (' . $p['currency'] . ')') === $col);
                @endphp
                <td style="border: 1px solid #ccc; padding: 5px;">
                    @if ($plan)
                        {{ number_format($plan['type'] === 'local' ? $plan['final_amount'] : $plan['amount'], 2) }} {{ $plan['currency'] }}
                    @endif
                </td>
            @endforeach
        </tr>
    @endforeach
    </tbody>
</table>

<table style="width: 100%; border-collapse: collapse; border: 1px solid #ccc; font-size: 13px; margin-top: 20px">
    <thead>
    <tr>
        <td colspan="5" style="background: #000c52; color: white; padding: 10px; font-weight: bold;">
            Terms & Conditions
        </td>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td style="border: 1px solid #ccc; padding: 5px;">
            <ol>
                <li>Payments for those who opt for installments must be made monthly on or before the due date. We reserve the right to charge interest and/or a penalty for late payments.</li>
                <li>Payments are typically non-refundable, subject to the terms outlined in the refund policy.</li>
                <li>You agree to comply with the specific academic guidelines and regulations relevant to your chosen program of study.</li>
                <li>Your registration is valid for a set period. Should your registration expire, you will be required to pay fees to extend your course.</li>
                <li>Access to course materials, live sessions, exams, or assignments may be restricted if there are any outstanding payments.</li>
                <li>All course content and resources are the intellectual property of the institute and are for personal academic use only. Reproduction, sharing, or distribution is strictly prohibited without written permission.</li>
                <li>LMS login credentials must be kept confidential. Any misuse or unauthorized access may result in account suspension or termination.</li>
                <li>The institute reserves the right to modify course content, schedules, faculty assignments, or delivery methods (e.g., online/offline) with prior notice. Such changes will not entitle students to refunds.</li>
                <li>Student data will be used only for academic and administrative purposes, in compliance with data protection regulations.</li>
                <li>By registering and/or making payment, the student confirms they have read, understood, and accepted these terms and conditions.</li>
            </ol>
        </td>
    </tr>
    </tbody>
</table>


<div class="footer">
    Note that government taxes may be applicable for your payments.
</div>

<div class="footer" style="margin-top: 30px;">
    Hotline: <a href="tel:+94117909909">0117 909 909</a>&nbsp;&nbsp;&nbsp;&nbsp; <a href="https://www.acidm.org">www.acidm.org</a><br>
    &copy; Copyright {{ now()->year }} Asia Chartered Institute of Digital Marketing. All Rights Reserved
</div>

</body>
</html>
