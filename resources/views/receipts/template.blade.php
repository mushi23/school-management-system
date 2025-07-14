<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - {{ $receipt->receipt_number }}</title>
    <style>
        {!! $css !!}
        
        :root {
            --receipt-bg: #fff;
            --receipt-text: #000;
            --receipt-border: #ddd;
            --receipt-header-bg: #f8f9fa;
        }
        body.dark, .dark .receipt-container {
            --receipt-bg: #18181b;
            --receipt-text: #f3f4f6;
            --receipt-border: #333;
            --receipt-header-bg: #23232a;
        }
        body, .receipt-container {
            background: var(--receipt-bg) !important;
            color: var(--receipt-text) !important;
        }
        .receipt-container {
            background: var(--receipt-bg) !important;
            color: var(--receipt-text) !important;
        }
        th {
            background-color: var(--receipt-header-bg) !important;
            color: var(--receipt-text) !important;
        }
        td, th {
            border-bottom: 1px solid var(--receipt-border) !important;
        }
        .border {
            border: 1px solid var(--receipt-border) !important;
        }
        .school-name, .school-slogan, .school-info, .receipt-title, .detail-label, .detail-value, .fee-header span, .amount-section, .signature-name, .signature-title, .security-text, .receipt-footer, .fee-row span, .receipt-header, .detail-group, .notes, .qr-code, .seal-image, .signature-section {
            color: var(--receipt-text) !important;
        }
        .mt-4 {
            margin-top: 16px;
        }
        .mb-2 {
            margin-bottom: 8px;
        }
        .mb-4 {
            margin-bottom: 16px;
        }
        .p-4 {
            padding: 16px;
        }
        .rounded {
            border-radius: 4px;
        }
        .text-bold {
            font-weight: bold;
            color: var(--receipt-text) !important;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .fee-header {
            background: var(--receipt-header-bg) !important;
        }
        .amount-section {
            background: transparent;
        }
        .notes {
            background: var(--receipt-header-bg) !important;
        }
        /* Print mode always uses light */
        @media print {
            :root, body, .receipt-container {
                --receipt-bg: #fff !important;
                --receipt-text: #000 !important;
                --receipt-border: #ddd !important;
                --receipt-header-bg: #f8f9fa !important;
                background: #fff !important;
                color: #000 !important;
            }
        }
        
        /* Ensure proper page breaks */
        .page-break {
            page-break-before: always;
        }
        
        /* Table styles for better PDF rendering */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        th, td {
            padding: 8px 12px;
            text-align: left;
            border-bottom: 1px solid var(--receipt-border) !important;
        }
        
        th {
            background-color: var(--receipt-header-bg) !important;
            font-weight: bold;
            color: var(--receipt-text) !important;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-bold {
            font-weight: bold;
            color: var(--receipt-text) !important;
        }
        
        .mb-2 {
            margin-bottom: 8px;
        }
        
        .mb-4 {
            margin-bottom: 16px;
        }
        
        .mt-4 {
            margin-top: 16px;
        }
        
        .p-4 {
            padding: 16px;
        }
        
        .border {
            border: 1px solid var(--receipt-border) !important;
        }
        
        .rounded {
            border-radius: 4px;
        }
        .school-name, .school-slogan, .school-info, .receipt-title, .detail-label, .detail-value, .fee-header span, .amount-section, .signature-name, .signature-title, .security-text, .receipt-footer, .fee-row span, .receipt-header, .detail-group, .notes, .qr-code, .seal-image, .signature-section {
            color: var(--receipt-text) !important;
        }
        .detail-label, .detail-value {
            color: var(--receipt-text) !important;
        }
        .signature-image {
            background: transparent !important;
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        {{-- Watermark --}}
        @if($template->show_watermark && $template->watermark_text)
            <div class="watermark" style="
                @if($template->watermark_position === 'center') top: 50%; left: 50%; transform: translate(-50%, -50%) rotate({{ $template->watermark_rotation }}deg); @endif
                @if($template->watermark_position === 'top-left') top: 10%; left: 10%; @endif
                @if($template->watermark_position === 'top-right') top: 10%; right: 10%; left: auto; @endif
                @if($template->watermark_position === 'bottom-left') bottom: 10%; left: 10%; top: auto; @endif
                @if($template->watermark_position === 'bottom-right') bottom: 10%; right: 10%; left: auto; top: auto; @endif
                color: {{ $template->watermark_color }}; opacity: {{ $template->watermark_opacity }}; z-index: 0; position: absolute; width: 100%; text-align: center; pointer-events: none; font-size: 48px; font-weight: bold;">
                {{ $template->watermark_text }}
            </div>
        @endif

        <!-- Header Section -->
        <div class="receipt-header">
            @if($template->show_school_logo && $school->logo)
                <img src="{{ asset('storage/' . $school->logo) }}" alt="School Logo" class="school-logo">
            @endif
            
            <div class="school-name">{{ $school->name }}</div>
            
            @if($template->show_school_slogan && $template->slogan)
                <div class="school-slogan">{{ $template->slogan }}</div>
            @endif
            
            <div class="school-info">
                @if($school->county || $school->sub_county)
                    <div>
                        {{ $school->county }}@if($school->county && $school->sub_county), @endif{{ $school->sub_county }}
                    </div>
                @endif
                @if($school->admin_email || $school->admin_phone)
                    <div>
                        @if($school->admin_email && $school->admin_phone)
                            Email: {{ $school->admin_email }} &nbsp; Phone: {{ $school->admin_phone }}
                        @elseif($school->admin_email)
                            Email: {{ $school->admin_email }}
                        @elseif($school->admin_phone)
                            Phone: {{ $school->admin_phone }}
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <!-- Receipt Title -->
        <div class="receipt-title">OFFICIAL RECEIPT</div>

        <!-- Receipt Details -->
        <div class="receipt-details">
            <div>
                @if($template->show_receipt_number)
                    <div class="detail-group">
                        <div class="detail-label">Receipt Number:</div>
                        <div class="detail-value">{{ $receipt->receipt_number }}</div>
                    </div>
                @endif
                
                @if($template->show_payment_date)
                    <div class="detail-group">
                        <div class="detail-label">Payment Date:</div>
                        <div class="detail-value">{{ $receipt->payment_date->format('F j, Y') }}</div>
                    </div>
                @endif
                
                @if($template->show_payment_method)
                    <div class="detail-group">
                        <div class="detail-label">Payment Method:</div>
                        <div class="detail-value">{{ ucfirst($receipt->payment_method) }}</div>
                    </div>
                @endif
                
                @if($receipt->payment_reference)
                    <div class="detail-group">
                        <div class="detail-label">Reference:</div>
                        <div class="detail-value">{{ $receipt->payment_reference }}</div>
                    </div>
                @endif
            </div>
            
            <div>
                @if($template->show_student_details)
                    <div class="detail-group">
                        <div class="detail-label">Student Name:</div>
                        <div class="detail-value">{{ $student->name }}</div>
                    </div>
                    
                    @if($student->admission_number)
                        <div class="detail-group">
                            <div class="detail-label">Admission Number:</div>
                            <div class="detail-value">{{ $student->admission_number }}</div>
                        </div>
                    @endif
                @endif
                
                @if($receipt->term)
                    <div class="detail-group">
                        <div class="detail-label">Term:</div>
                        <div class="detail-value">{{ $receipt->term }}</div>
                    </div>
                @endif
                
                @if($receipt->academic_year)
                    <div class="detail-group">
                        <div class="detail-label">Academic Year:</div>
                        <div class="detail-value">{{ $receipt->academic_year }}</div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Fee Breakdown -->
        @if($template->show_fee_breakdown && !empty($receipt->fee_breakdown))
            <div class="fee-breakdown">
                <div class="fee-header">
                    <div style="display: grid; grid-template-columns: 2fr 1fr;">
                        <span>Description</span>
                        <span class="text-right">Amount (KES)</span>
                    </div>
                </div>
                
                @foreach($receipt->fee_breakdown as $item)
                    <div class="fee-row">
                        <span>{{ $item['item'] }}</span>
                        <span class="text-right">{{ number_format($item['amount'], 2) }}</span>
                    </div>
                @endforeach
            </div>
        @endif

        <!-- Amount Section -->
        <div class="amount-section">
            <div class="mb-2">
                <span class="text-bold">Amount Paid:</span>
                <span class="text-bold" style="margin-left: 20px;">KES {{ number_format($receipt->amount_paid, 2) }}</span>
            </div>
            
            @if($template->show_balance)
                <div class="mb-2">
                    <span>Balance Before:</span>
                    <span style="margin-left: 20px;">KES {{ number_format($receipt->balance_before, 2) }}</span>
                </div>
                
                <div class="mb-2">
                    <span>Balance After:</span>
                    <span style="margin-left: 20px;">KES {{ number_format($receipt->balance_after, 2) }}</span>
                </div>
            @endif
        </div>

        <!-- Notes -->
        @if($receipt->notes)
            <div class="mt-4 p-4 border rounded" style="background-color: var(--receipt-header-bg);">
                <div class="text-bold mb-2">Notes:</div>
                <div>{{ $receipt->notes }}</div>
            </div>
        @endif

        {{-- QR Code --}}
        @if($template->show_qr_code)
            <div class="qr-code" style="
                @if($template->qr_code_position === 'top-right') top: 20px; right: 20px; left: auto; @endif
                @if($template->qr_code_position === 'top-left') top: 20px; left: 20px; right: auto; @endif
                @if($template->qr_code_position === 'bottom-right') bottom: 20px; right: 20px; left: auto; top: auto; @endif
                @if($template->qr_code_position === 'bottom-left') bottom: 20px; left: 20px; right: auto; top: auto; @endif
                width: {{ $template->qr_code_size }}mm; height: {{ $template->qr_code_size }}mm; position: absolute;">
                {{-- Placeholder for QR code --}}
                <div style="width: 100%; height: 100%; background: #eee; display: flex; align-items: center; justify-content: center; font-size: 10px; color: #888; border: 1px dashed #bbb;">QR</div>
            </div>
        @endif

        {{-- School Seal --}}
        @if($template->show_seal && $template->seal_url)
            @php
                $sealPos = isset($seal_position) ? $seal_position : $template->seal_position;
                $sealStyle = 'position: absolute; width: ' . $template->seal_size . 'mm; height: ' . $template->seal_size . 'mm; z-index: 2;';
                if ($sealPos === 'bottom-left') {
                    $sealStyle .= 'bottom: 20px; left: 20px;';
                } elseif ($sealPos === 'bottom-right') {
                    $sealStyle .= 'bottom: 20px; right: 20px;';
                } elseif ($sealPos === 'top-left') {
                    $sealStyle .= 'top: 20px; left: 20px;';
                } elseif ($sealPos === 'top-right') {
                    $sealStyle .= 'top: 20px; right: 20px;';
                } elseif ($sealPos === 'bottom-center') {
                    $sealStyle .= 'bottom: 20px; left: 50%; transform: translateX(-50%);';
                } elseif ($sealPos === 'top-center') {
                    $sealStyle .= 'top: 20px; left: 50%; transform: translateX(-50%);';
                } elseif ($sealPos === 'left-center') {
                    $sealStyle .= 'top: 50%; left: 20px; transform: translateY(-50%);';
                } elseif ($sealPos === 'right-center') {
                    $sealStyle .= 'top: 50%; right: 20px; transform: translateY(-50%);';
                } else {
                    $sealStyle .= 'bottom: 20px; right: 20px;';
                }
            @endphp
            <img src="{{ $template->seal_url }}" alt="School Seal" class="seal-image" style="{{ $sealStyle }}">
        @endif

        <!-- Footer -->
        <div class="receipt-footer" style="position: relative;">
            {{-- Digital Signature --}}
            @if($template->show_signature && $template->signature_url)
                @php
                    $sigPos = $template->signature_position;
                    $sigStyle = 'position: absolute;';
                    if ($sigPos === 'bottom-left') {
                        $sigStyle .= 'left: 20px; bottom: 20px; text-align: left;';
                    } elseif ($sigPos === 'bottom-right') {
                        $sigStyle .= 'right: 20px; bottom: 20px; text-align: right;';
                    } elseif ($sigPos === 'top-left') {
                        $sigStyle .= 'left: 20px; top: 20px; text-align: left;';
                    } elseif ($sigPos === 'top-right') {
                        $sigStyle .= 'right: 20px; top: 20px; text-align: right;';
                    } elseif ($sigPos === 'bottom-center') {
                        $sigStyle .= 'left: 50%; bottom: 20px; transform: translateX(-50%); text-align: center;';
                    } elseif ($sigPos === 'top-center') {
                        $sigStyle .= 'left: 50%; top: 20px; transform: translateX(-50%); text-align: center;';
                    } else {
                        $sigStyle .= 'right: 20px; bottom: 20px; text-align: right;';
                    }
                @endphp
                <div class="signature-section" style="{{ $sigStyle }}">
                    <img src="{{ $template->signature_url }}" alt="Signature" class="signature-image">
                    <div class="signature-name">{{ $template->signature_name }}</div>
                    <div class="signature-title">{{ $template->signature_title }}</div>
                </div>
            @endif

            {{-- Security Text --}}
            @if($template->show_security_features && $template->security_text)
                <div class="security-text">{{ $template->security_text }}</div>
            @endif
            @if($template->show_verification_url && $template->verification_url)
                <div class="security-text">Verify at: {{ $template->verification_url }}</div>
            @endif
            @if($template->show_unique_id && $template->unique_id_prefix)
                <div class="security-text">Unique ID: {{ $template->unique_id_prefix }}{{ $receipt->id }}</div>
            @endif
            <div class="mb-4">
                <p>This is an official receipt from {{ $school->name }}.</p>
                <p>Please keep this receipt for your records.</p>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-top: 40px;">
                <div style="text-align: center;">
                    <div style="border-top: 1px solid var(--receipt-border); padding-top: 10px; margin-bottom: 10px;"></div>
                    <div>Student/Parent Signature</div>
                </div>
                
                <div style="text-align: center;">
                    <div style="border-top: 1px solid var(--receipt-border); padding-top: 10px; margin-bottom: 10px;"></div>
                    <div>Authorized Signature</div>
                </div>
            </div>
            
            <div style="margin-top: 30px; font-size: 10px; color: #666;">
                <p style="color: var(--receipt-text) !important;">Generated on {{ now()->format('F j, Y \a\t g:i A') }}</p>
                <p style="color: var(--receipt-text) !important;">Receipt ID: {{ $receipt->id }}</p>
            </div>
        </div>
    </div>
</body>
</html> 