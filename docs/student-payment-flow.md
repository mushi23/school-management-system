# Student Payment Flow with Custom Amounts

## Overview
Students can now enter custom payment amounts instead of being limited to fixed amounts. This provides flexibility for partial payments and installment plans.

## New Payment Modal Features

### 1. Custom Amount Input
- Students can enter any amount between Ksh 1 and their total fee balance
- Real-time validation ensures amounts are within limits
- Input field with Ksh prefix for clarity

### 2. Phone Number Input
- Automatic formatting for M-PESA phone numbers
- Supports multiple input formats:
  - `254712345678` (full format)
  - `0712345678` (local format - auto-converts)
  - `712345678` (short format - auto-converts)
- Real-time validation for correct format

### 3. Quick Amount Buttons
- Dynamically generated based on fee balance
- Common amounts: 1,000, 2,000, 5,000, 10,000, 20,000, 50,000
- Half balance option (if significant)
- Full balance option
- One-click selection

### 4. Payment Summary
- Shows selected amount
- Displays phone number
- Calculates remaining balance
- Real-time updates as user changes inputs

### 5. Validation & Error Handling
- Amount validation (min: 1, max: fee balance)
- Phone number format validation
- Real-time error messages
- Disabled submit button until valid

## User Flow

### Step 1: Open Payment Modal
1. Student clicks "Pay" button
2. Modal opens with fee balance pre-filled
3. Phone number field is empty and ready for input

### Step 2: Enter Payment Details
1. **Amount**: Student can either:
   - Type custom amount in input field
   - Click quick amount buttons
   - Use pre-filled fee balance
2. **Phone**: Student enters M-PESA phone number
   - Auto-formats to 2547XXXXXXXX format
   - Validates format in real-time

### Step 3: Review & Confirm
1. Payment summary shows:
   - Selected amount
   - Phone number
   - Remaining balance after payment
2. Student reviews details
3. Clicks "Pay Now" when ready

### Step 4: Payment Processing
1. Modal closes
2. Payment initiation starts
3. M-PESA prompt sent to phone
4. Student completes payment in M-PESA app
5. System polls for payment status
6. Success/failure notification

## Technical Implementation

### Frontend Components
- **Payment Modal**: Vue.js modal with form inputs
- **Amount Input**: Number input with validation
- **Phone Input**: Text input with auto-formatting
- **Quick Amounts**: Dynamic button grid
- **Payment Summary**: Real-time calculation display

### Validation Rules
```javascript
// Amount validation
- Must be >= 1
- Must be <= fee balance
- Must be a number

// Phone validation
- Must match pattern: /^2547\d{8}$/
- Auto-converts local formats
```

### API Integration
```javascript
// Payment initiation
POST /api/mpesa/initiate-payment
{
  amount: number,
  phone_number: string,
  student_id: number,
  term: string
}
```

## Benefits

### For Students
- **Flexibility**: Pay any amount within balance
- **Convenience**: Quick amount buttons for common payments
- **Clarity**: Clear payment summary before confirmation
- **Ease**: Auto-formatting phone numbers

### For Schools
- **Partial Payments**: Students can pay in installments
- **Reduced Barriers**: Lower amounts encourage payments
- **Better UX**: Improved payment experience
- **Flexibility**: Accommodates different payment patterns

## Future Enhancements

### Planned Features
1. **Payment History**: Show previous payments
2. **Installment Plans**: Pre-defined payment schedules
3. **Payment Reminders**: Automated notifications
4. **Receipt Generation**: Digital payment receipts
5. **Multiple Payment Methods**: Add other payment options

### Technical Improvements
1. **Offline Support**: Cache payment data
2. **Better Error Handling**: More specific error messages
3. **Payment Analytics**: Track payment patterns
4. **Mobile Optimization**: Better mobile experience 