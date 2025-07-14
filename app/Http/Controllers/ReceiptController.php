<?php

namespace App\Http\Controllers;

use App\Models\Receipt;
use App\Models\ReceiptTemplate;
use App\Models\Payment;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class ReceiptController extends Controller
{
    /**
     * Display receipts for a student
     */
    public function index(Request $request)
    {
        $student = $request->user()->student;
        if (!$student) {
            abort(404, 'Student not found');
        }

        $student->append('profile_picture_url');

        $receipts = Receipt::where('student_id', $student->id)
            ->with(['payment', 'template'])
            ->orderBy('payment_date', 'desc')
            ->get()
            ->groupBy(function ($receipt) {
                return $receipt->term . ' - ' . $receipt->academic_year;
            });

        // Debug: Log the receipts data
        \Log::info('Receipts for student ' . $student->id, [
            'student_id' => $student->id,
            'receipts_count' => $receipts->flatten()->count(),
            'receipts_groups' => $receipts->keys()->toArray(),
            'receipts_data' => $receipts->toArray()
        ]);

        return Inertia::render('Student/Receipts', [
            'receipts' => $receipts,
            'student' => $student,
            'school' => $student->school ? [
                'name' => $student->school->name,
                'logo' => $student->school->logo_url,
                'background' => $student->school->background_url,
                'slogan' => $student->school->slogan,
            ] : null
        ]);
    }

    /**
     * Generate receipt for a payment
     */
    public function generate(Request $request, $paymentId)
    {
        $payment = Payment::with(['student', 'student.school'])->findOrFail($paymentId);
        
        // Check if receipt already exists
        $existingReceipt = Receipt::where('payment_id', $paymentId)->first();
        if ($existingReceipt) {
            return response()->json([
                'success' => true,
                'message' => 'Receipt already exists',
                'receipt' => $existingReceipt
            ]);
        }

        // Get term from request or payment
        $term = $request->get('term');
        $academicYear = $request->get('academic_year', date('Y'));

        // Create receipt
        $receipt = Receipt::createFromPayment($payment, $term, $academicYear);

        return response()->json([
            'success' => true,
            'message' => 'Receipt generated successfully',
            'receipt' => $receipt
        ]);
    }

    /**
     * View receipt as PDF
     */
    public function view(Request $request, $receiptId)
    {
        $receipt = Receipt::with(['student', 'school', 'payment', 'template'])->findOrFail($receiptId);
        
        // Check if user has access to this receipt
        $user = $request->user();
        if ($user->hasRole('student') && $receipt->student_id !== $user->student->id) {
            abort(403, 'Access denied');
        }

        // Generate PDF if not exists
        if (!$receipt->pdf_path) {
            $this->generatePdf($receipt);
        }

        // Serve the PDF file directly from public storage
        $path = Storage::disk('public')->path($receipt->pdf_path);
        if (!file_exists($path)) {
            abort(404, 'PDF file not found');
        }

        return response()->file($path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . basename($receipt->pdf_path) . '"'
        ]);
    }

    /**
     * Download receipt as PDF
     */
    public function download(Request $request, $receiptId)
    {
        $receipt = Receipt::with(['student', 'school', 'payment', 'template'])->findOrFail($receiptId);
        
        // Check if user has access to this receipt
        $user = $request->user();
        if ($user->hasRole('student') && $receipt->student_id !== $user->student->id) {
            abort(403, 'Access denied');
        }

        // Generate PDF if not exists
        if (!$receipt->pdf_path) {
            $this->generatePdf($receipt);
        }

        $path = Storage::disk('public')->path($receipt->pdf_path);
        return response()->download($path, "receipt-{$receipt->receipt_number}.pdf");
    }

    /**
     * Generate PDF for receipt
     */
    public function generatePdf(Receipt $receipt)
    {
        $template = $receipt->template;
        $student = $receipt->student;
        $school = $receipt->school;
        $payment = $receipt->payment;

        // Pick a random safe seal position for this receipt
        $safePositions = [
            'top-left', 'top-right', 'bottom-left', 'bottom-right',
            'bottom-center', 'top-center', 'left-center', 'right-center'
        ];
        // Optionally, avoid center if receipt has a lot of text (not implemented here)
        $randomSealPosition = $safePositions[array_rand($safePositions)];

        // Prepare data for PDF
        $data = [
            'receipt' => $receipt,
            'student' => $student,
            'school' => $school,
            'payment' => $payment,
            'template' => $template,
            'css' => $template->getCssStyles(),
            'seal_position' => $randomSealPosition, // pass to Blade
        ];

        // Generate PDF
        $pdf = Pdf::loadView('receipts.template', $data);
        $pdf->setPaper('A4', 'portrait');

        // Save PDF in public storage
        $filename = "receipts/{$school->id}/{$receipt->receipt_number}.pdf";
        Storage::disk('public')->put($filename, $pdf->output());

        // Update receipt with PDF path
        $receipt->update(['pdf_path' => $filename]);
    }

    /**
     * Get receipt template for school
     */
    public function getTemplate(Request $request)
    {
        $user = $request->user();
        $schoolId = null;

        if ($user->hasRole('school_admin')) {
            $schoolId = $user->school_id;
        } elseif ($user->hasRole('student')) {
            $schoolId = $user->student->school_id;
        }

        if (!$schoolId) {
            return response()->json(['success' => false, 'message' => 'School not found']);
        }

        $template = ReceiptTemplate::getDefaultForSchool($schoolId);
        if (!$template) {
            $template = ReceiptTemplate::createDefaultForSchool($schoolId);
        }

        $adminUser = $template->school ? $template->school->adminUser : null;
        return response()->json([
            'success' => true,
            'template' => array_merge($template->toArray(), [
                'logo_url' => $template->logo_url,
                'signature_url' => $template->signature_url,
                'seal_url' => $template->seal_url,
            ]),
            'school_logo_url' => $template->school ? $template->school->logo_url : null,
            'school_brand_color' => $template->school ? $template->school->brand_color : null,
            'school_county' => $template->school ? $template->school->county : null,
            'school_sub_county' => $template->school ? $template->school->sub_county : null,
            'school_admin_email' => $adminUser ? $adminUser->email : null,
            'school_admin_phone' => $adminUser ? $adminUser->phone : null,
        ]);
    }

    /**
     * Update receipt template
     */
    public function updateTemplate(Request $request)
    {
        $user = $request->user();
        if (!$user->hasRole('school_admin')) {
            abort(403, 'Access denied');
        }

        $schoolId = $user->school_id;
        $template = ReceiptTemplate::getDefaultForSchool($schoolId);
        if (!$template) {
            $template = ReceiptTemplate::createDefaultForSchool($schoolId);
        }

        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'slogan' => 'nullable|string|max:255',
            'primary_font' => 'nullable|string|max:255',
            'secondary_font' => 'nullable|string|max:255',
            'primary_color' => 'nullable|string|max:7',
            'secondary_color' => 'nullable|string|max:7',
            'accent_color' => 'nullable|string|max:7',
            'logo_width' => 'nullable|numeric|min:50|max:300',
            'logo_height' => 'nullable|numeric|min:20|max:200',
            'show_school_logo' => 'boolean',
            'show_school_slogan' => 'boolean',
            'show_school_address' => 'boolean',
            'show_school_phone' => 'boolean',
            'show_school_email' => 'boolean',
            'show_payment_method' => 'boolean',
            'show_receipt_number' => 'boolean',
            'show_payment_date' => 'boolean',
            'show_student_details' => 'boolean',
            'show_fee_breakdown' => 'boolean',
            'show_balance' => 'boolean',
            'custom_css' => 'nullable|string',
            'layout_settings' => 'nullable|array',
            // Security features
            'signature_name' => 'nullable|string|max:255',
            'signature_title' => 'nullable|string|max:255',
            'show_signature' => 'boolean',
            'signature_position' => 'nullable|string|max:32',
            'show_seal' => 'boolean',
            'seal_position' => 'nullable|string|max:32',
            'seal_size' => 'nullable|numeric|min:20|max:100',
            'seal_style' => 'nullable|string|max:32',
            'watermark_text' => 'nullable|string|max:255',
            'show_watermark' => 'boolean',
            'watermark_color' => 'nullable|string|max:7',
            'watermark_opacity' => 'nullable|numeric|min:0|max:1',
            'watermark_position' => 'nullable|string|max:32',
            'watermark_rotation' => 'nullable|integer|min:-90|max:90',
            'show_qr_code' => 'boolean',
            'qr_code_position' => 'nullable|string|max:32',
            'qr_code_size' => 'nullable|numeric|min:20|max:50',
            'qr_code_data' => 'nullable|string',
            'show_security_features' => 'boolean',
            'security_text' => 'nullable|string|max:255',
            'show_verification_url' => 'boolean',
            'verification_url' => 'nullable|string|max:255',
            'show_unique_id' => 'boolean',
            'unique_id_prefix' => 'nullable|string|max:32',
            'receipt_number_format' => 'nullable|string|max:64',
            'receipt_number_start' => 'nullable|integer|min:1',
            'receipt_number_prefix' => 'nullable|string|max:32',
        ]);

        $template->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Template updated successfully',
            'template' => array_merge($template->toArray(), [
                'logo_url' => $template->logo_url,
                'signature_url' => $template->signature_url,
                'seal_url' => $template->seal_url,
            ])
        ]);
    }

    /**
     * Upload logo for template
     */
    public function uploadLogo(Request $request)
    {
        $user = $request->user();
        if (!$user->hasRole('school_admin')) {
            abort(403, 'Access denied');
        }

        $request->validate([
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $schoolId = $user->school_id;
        $template = ReceiptTemplate::getDefaultForSchool($schoolId);
        
        if (!$template) {
            $template = ReceiptTemplate::createDefaultForSchool($schoolId);
        }

        // Delete old logo if exists
        if ($template->logo_path) {
            Storage::delete($template->logo_path);
        }

        // Store new logo
        $path = $request->file('logo')->store("schools/{$schoolId}/logos", 'public');
        $template->update(['logo_path' => $path]);

        return response()->json([
            'success' => true,
            'message' => 'Logo uploaded successfully',
            'logo_url' => $template->logo_url
        ]);
    }

    /**
     * Upload signature for template
     */
    public function uploadSignature(Request $request)
    {
        $user = $request->user();
        if (!$user->hasRole('school_admin')) {
            abort(403, 'Access denied');
        }

        $request->validate([
            'signature' => 'required|file|mimes:svg,png,jpg,jpeg|max:2048'
        ]);

        $schoolId = $user->school_id;
        $template = ReceiptTemplate::getDefaultForSchool($schoolId);
        if (!$template) {
            $template = ReceiptTemplate::createDefaultForSchool($schoolId);
        }

        // Delete old signature if exists
        if ($template->signature_path) {
            Storage::delete($template->signature_path);
        }

        // Store new signature
        $path = $request->file('signature')->store("schools/{$schoolId}/signatures", 'public');
        $template->update(['signature_path' => $path]);

        return response()->json([
            'success' => true,
            'message' => 'Signature uploaded successfully',
            'signature_url' => $template->signature_url,
            'signature_path' => $template->signature_path,
        ]);
    }

    /**
     * Upload seal for template
     */
    public function uploadSeal(Request $request)
    {
        $user = $request->user();
        if (!$user->hasRole('school_admin')) {
            abort(403, 'Access denied');
        }

        // Debug: Log the request details
        \Log::info('Seal upload request', [
            'has_file' => $request->hasFile('seal'),
            'file_name' => $request->file('seal') ? $request->file('seal')->getClientOriginalName() : 'no file',
            'file_size' => $request->file('seal') ? $request->file('seal')->getSize() : 'no file',
            'mime_type' => $request->file('seal') ? $request->file('seal')->getMimeType() : 'no file',
            'extension' => $request->file('seal') ? $request->file('seal')->getClientOriginalExtension() : 'no file',
        ]);

        // Check if file exists
        if (!$request->hasFile('seal')) {
            return response()->json([
                'success' => false,
                'message' => 'No seal file provided.'
            ], 422);
        }

        $file = $request->file('seal');
        $extension = strtolower($file->getClientOriginalExtension());
        $mimeType = $file->getMimeType();
        $fileSize = $file->getSize();

        // Validate file size
        if ($fileSize > 5 * 1024 * 1024) { // 5MB
            return response()->json([
                'success' => false,
                'message' => 'The seal file size must not exceed 5MB.'
            ], 422);
        }

        // Validate file type
        $allowedExtensions = ['svg', 'png', 'jpg', 'jpeg'];
        $allowedMimeTypes = [
            'image/svg+xml',
            'image/png',
            'image/jpeg',
            'image/jpg',
            'text/plain', // Some SVG files might be detected as text/plain
            'application/octet-stream' // Fallback for some file types
        ];

        if (!in_array($extension, $allowedExtensions) && !in_array($mimeType, $allowedMimeTypes)) {
            return response()->json([
                'success' => false,
                'message' => 'The seal must be a file of type: svg, png, jpg, jpeg. Detected: ' . $mimeType . ' (' . $extension . ')'
            ], 422);
        }

        $schoolId = $user->school_id;
        $template = ReceiptTemplate::getDefaultForSchool($schoolId);
        if (!$template) {
            $template = ReceiptTemplate::createDefaultForSchool($schoolId);
        }

        // Delete old seal if exists
        if ($template->seal_path) {
            Storage::disk('public')->delete($template->seal_path);
        }

        try {
            // Store new seal with correct extension
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            
            // Ensure SVG files get the correct extension
            if ($extension === 'svg' || $mimeType === 'text/plain' && $extension === 'svg') {
                $filename = pathinfo($originalName, PATHINFO_FILENAME) . '.svg';
            } else {
                $filename = $originalName;
            }
            
            // Get the seal style from the request
            $sealStyle = $request->input('seal_style');
            
            $path = $file->storeAs("schools/{$schoolId}/seals", $filename, 'public');
            
            // Update template with seal path and style
            $updateData = ['seal_path' => $path];
            if ($sealStyle) {
                $updateData['seal_style'] = $sealStyle;
            }
            $template->update($updateData);

            \Log::info('Seal uploaded successfully', [
                'path' => $path,
                'url' => $template->seal_url,
                'original_name' => $originalName,
                'extension' => $extension,
                'mime_type' => $mimeType,
                'seal_style' => $sealStyle
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Seal uploaded successfully',
                'seal_url' => $template->seal_url,
                'seal_path' => $template->seal_path,
                'seal_style' => $sealStyle,
            ]);
        } catch (\Exception $e) {
            \Log::error('Seal upload failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload seal: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete signature from template
     */
    public function deleteSignature(Request $request)
    {
        $user = $request->user();
        if (!$user->hasRole('school_admin')) {
            abort(403, 'Access denied');
        }

        $schoolId = $user->school_id;
        $template = ReceiptTemplate::getDefaultForSchool($schoolId);
        
        if ($template && $template->signature_path) {
            Storage::disk('public')->delete($template->signature_path);
            $template->update([
                'signature_path' => null
            ]);
        }

        return response()->json([
            'success' => true
        ]);
    }

    /**
     * Delete seal from template
     */
    public function deleteSeal(Request $request)
    {
        $user = $request->user();
        if (!$user->hasRole('school_admin')) {
            abort(403, 'Access denied');
        }

        $schoolId = $user->school_id;
        $template = ReceiptTemplate::getDefaultForSchool($schoolId);
        
        if ($template && $template->seal_path) {
            Storage::disk('public')->delete($template->seal_path);
            $template->update([
                'seal_path' => null,
                'seal_style' => null
            ]);
        }

        return response()->json([
            'success' => true
        ]);
    }
}
