# Receipt Generation System

## Overview

The school management system now includes a comprehensive receipt generation system that automatically creates professional, customizable receipts for all fee payments. The system supports both sandbox and production environments, with full customization options for schools.

## Features

### 🎨 Customizable Templates
- **School Branding**: Logo, slogan, colors, and fonts
- **Layout Control**: Toggle visibility of various receipt elements
- **Custom CSS**: Advanced styling options
- **Multi-school Support**: Each school has its own template

### 📄 Professional Receipts
- **Auto-generation**: Receipts created automatically for every payment
- **Unique Numbers**: Auto-generated receipt numbers with school prefix
- **PDF Export**: High-quality PDF generation with DomPDF
- **Digital Signatures**: Space for student/parent and authorized signatures

### 🔧 Technical Features
- **Database Storage**: All receipts stored with full payment details
- **Term Organization**: Receipts grouped by term and academic year
- **Balance Tracking**: Shows balance before and after payment
- **Fee Breakdown**: Detailed breakdown of fee items
- **Audit Trail**: Complete payment history and receipt generation logs

## Database Structure

### Receipt Templates Table
```sql
receipt_templates
├── id (Primary Key)
├── school_id (Foreign Key)
├── name (Template name)
├── header_html (Custom header HTML)
├── footer_html (Custom footer HTML)
├── logo_path (School logo file path)
├── slogan (School slogan)
├── primary_font (Primary font family)
├── secondary_font (Secondary font family)
├── primary_color (Primary color hex)
├── secondary_color (Secondary color hex)
├── accent_color (Accent color hex)
├── logo_width (Logo width in mm)
├── logo_height (Logo height in mm)
├── show_school_logo (Boolean)
├── show_school_slogan (Boolean)
├── show_school_address (Boolean)
├── show_school_phone (Boolean)
├── show_school_email (Boolean)
├── show_payment_method (Boolean)
├── show_receipt_number (Boolean)
├── show_payment_date (Boolean)
├── show_student_details (Boolean)
├── show_fee_breakdown (Boolean)
├── show_balance (Boolean)
├── custom_css (Custom CSS styles)
├── layout_settings (JSON layout preferences)
├── is_active (Boolean)
└── timestamps
```

### Receipts Table
```sql
receipts
├── id (Primary Key)
├── school_id (Foreign Key)
├── student_id (Foreign Key)
├── payment_id (Foreign Key)
├── receipt_template_id (Foreign Key)
├── receipt_number (Unique receipt number)
├── amount_paid (Payment amount)
├── payment_method (Payment method)
├── payment_reference (Payment reference)
├── payment_date (Payment date)
├── term (Academic term)
├── academic_year (Academic year)
├── balance_before (Balance before payment)
├── balance_after (Balance after payment)
├── fee_breakdown (JSON fee items)
├── notes (Additional notes)
├── generated_by (User who generated)
├── pdf_path (PDF file path)
├── receipt_data (JSON receipt data)
└── timestamps
```

## Models

### ReceiptTemplate Model
- **Relationships**: School, Receipts
- **Methods**:
  - `getDefaultForSchool()`: Get default template for school
  - `createDefaultForSchool()`: Create default template
  - `getCssStyles()`: Generate CSS styles for template

### Receipt Model
- **Relationships**: School, Student, Payment, Template
- **Methods**:
  - `generateReceiptNumber()`: Generate unique receipt number
  - `createFromPayment()`: Create receipt from payment
  - `getPdfUrl()`: Get PDF URL
  - Scopes: `byTermAndYear()`, `byStudent()`, `bySchool()`

## Controllers

### ReceiptController
- **Methods**:
  - `index()`: Display student receipts
  - `generate()`: Generate receipt for payment
  - `view()`: View receipt as PDF
  - `download()`: Download receipt PDF
  - `getTemplate()`: Get school template
  - `updateTemplate()`: Update template settings
  - `uploadLogo()`: Upload school logo

## Frontend Components

### Student Receipts Page (`/student/receipts`)
- **Features**:
  - Grouped by term and academic year
  - Receipt preview modal
  - PDF download functionality
  - Payment method indicators
  - Fee breakdown display

### School Admin Template Settings (`/school-admin/receipt-template`)
- **Features**:
  - Logo upload and management
  - Color scheme customization
  - Font selection
  - Display options toggles
  - Live preview
  - Custom CSS editor

## API Endpoints

### Student Routes
```
GET /student/receipts - View student receipts
GET /student/receipts/{receipt}/view - View receipt PDF
GET /student/receipts/{receipt}/download - Download receipt PDF
```

### School Admin Routes
```
GET /school-admin/receipt-template - Template settings page
GET /school-admin/receipt-template/data - Get template data
POST /school-admin/receipt-template - Update template
POST /school-admin/receipt-template/logo - Upload logo
```

### API Routes
```
POST /api/receipts/generate/{payment} - Generate receipt
GET /api/receipts/{receipt}/view - View receipt
GET /api/receipts/{receipt}/download - Download receipt
```

## Receipt Number Format

Receipt numbers follow the format: `{SCHOOL_CODE}{YEAR}{MONTH}{SEQUENTIAL_NUMBER}`

Example: `ABC2024120001`
- `ABC`: School code (first 3 letters of school name)
- `2024`: Year
- `12`: Month
- `0001`: Sequential number (4 digits, zero-padded)

## PDF Generation

### Template Structure
1. **Header**: School logo, name, slogan, contact info
2. **Title**: "OFFICIAL RECEIPT"
3. **Details**: Receipt number, payment date, method, reference
4. **Student Info**: Name, admission number, term, academic year
5. **Fee Breakdown**: Itemized list of fees
6. **Amount Section**: Amount paid, balance before/after
7. **Notes**: Additional information
8. **Footer**: Signatures, generation timestamp

### Styling
- **Responsive Design**: Adapts to different screen sizes
- **Print Optimization**: Optimized for PDF generation
- **Custom CSS**: Schools can add custom styles
- **Color Schemes**: Configurable primary, secondary, and accent colors

## Integration with Payment System

### Automatic Generation
- Receipts are automatically generated when payments are completed
- Works with both M-PESA sandbox and production environments
- Handles both successful payments and sandbox simulations

### Payment Flow
1. Student initiates payment
2. Payment is processed (M-PESA or sandbox)
3. Payment record is created
4. Receipt is automatically generated
5. Student can view/download receipt

## Customization Options

### Visual Customization
- **Logo**: Upload school logo with size controls
- **Colors**: Primary, secondary, and accent color selection
- **Fonts**: Primary and secondary font family selection
- **Layout**: Toggle visibility of receipt elements

### Content Customization
- **School Information**: Name, slogan, address, phone, email
- **Receipt Elements**: Choose which information to display
- **Custom CSS**: Advanced styling for unique requirements

## Security Features

### Access Control
- Students can only view their own receipts
- School admins can manage their school's template
- Receipt generation is logged for audit purposes

### Data Protection
- Receipt data is encrypted in storage
- PDF files are stored securely
- Access is controlled through role-based permissions

## Usage Examples

### For Students
1. Make a payment through the fees page
2. Navigate to "View Receipts" from the fees page
3. Browse receipts grouped by term
4. View receipt preview or download PDF
5. Keep receipts for record-keeping

### For School Admins
1. Access receipt template settings
2. Upload school logo and customize branding
3. Adjust colors, fonts, and layout options
4. Preview changes in real-time
5. Save template for all future receipts

## Future Enhancements

### Planned Features
1. **Email Receipts**: Automatic email delivery
2. **Bulk Operations**: Generate multiple receipts
3. **Receipt Analytics**: Payment and receipt statistics
4. **Digital Signatures**: Electronic signature integration
5. **Multi-language Support**: Receipts in different languages
6. **QR Codes**: QR codes for receipt verification
7. **Receipt Templates**: Multiple template options per school

### Technical Improvements
1. **Caching**: PDF caching for better performance
2. **Batch Processing**: Background receipt generation
3. **API Rate Limiting**: Protect against abuse
4. **Receipt Archiving**: Long-term storage solutions
5. **Export Options**: Excel, CSV export capabilities

## Troubleshooting

### Common Issues
1. **PDF Generation Fails**: Check DomPDF installation and permissions
2. **Logo Not Displaying**: Verify file path and storage permissions
3. **Receipt Numbers Duplicate**: Check database constraints
4. **Template Not Saving**: Verify form validation and permissions

### Debug Commands
```bash
# Generate receipts for existing payments
php artisan receipts:generate-for-existing-payments

# Check receipt count
php artisan tinker --execute="echo 'Receipts: ' . \App\Models\Receipt::count();"

# Test PDF generation
php artisan tinker --execute="\$receipt = \App\Models\Receipt::first(); if(\$receipt) { \$receipt->generatePdf(); }"
```

## Conclusion

The receipt generation system provides a comprehensive solution for schools to create professional, customizable receipts for all fee payments. With automatic generation, extensive customization options, and secure storage, it ensures a professional experience for both students and school administrators. 