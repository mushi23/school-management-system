# M-PESA Partnership Setup Guide

## Overview
This guide explains how to partner with existing M-PESA businesses to collect school fees payments.

## Finding a Business Partner

### Potential Partners:
- Local shops with M-PESA till numbers
- Existing businesses with PayBill numbers
- M-PESA agents with business accounts
- Other schools with M-PESA integration

### What to Ask For:
```
Consumer Key: [Their Daraja API key]
Consumer Secret: [Their Daraja API secret]
Shortcode: [Their PayBill/Till number]
Passkey: [Their Daraja passkey]
Environment: production
```

## Testing Partner Credentials

### Web Interface:
1. Go to: `/school-admin/mpesa-settings`
2. Click "Test Production"
3. Enter partner's credentials
4. Click "Test Credentials"

### Command Line:
```bash
php artisan mpesa:test-production \
  --consumer-key="PARTNER_KEY" \
  --consumer-secret="PARTNER_SECRET" \
  --shortcode="PARTNER_SHORTCODE" \
  --passkey="PARTNER_PASSKEY"
```

## Business Agreement Template

### Essential Terms:
1. **Permission**: Written consent to use their M-PESA credentials
2. **Revenue Sharing**: Agreed percentage (if any)
3. **Transaction Limits**: Maximum amount per transaction
4. **Reconciliation**: How to handle payment matching
5. **Liability**: Who's responsible for failed transactions

### Sample Agreement Points:
- Partner provides M-PESA credentials for school fees collection
- School pays X% of transaction fees to partner
- Daily/weekly reconciliation of payments
- Partner receives transaction reports
- Clear identification of school payments in M-PESA

## Technical Configuration

### Callback URL Setup:
```
https://yourdomain.com/api/mpesa/callback
```

### Account Reference Format:
```
SCHOOL_[SCHOOL_ID]_STUDENT_[STUDENT_ID]_[TIMESTAMP]
```

### Transaction Description:
```
School Fees - [School Name] - [Student Name]
```

## Security Best Practices

1. **Credential Storage**: Use environment variables
2. **Access Control**: Limit who can access credentials
3. **Monitoring**: Regular transaction monitoring
4. **Backup**: Keep secure backup of credentials
5. **Updates**: Regular credential rotation

## Troubleshooting

### Common Issues:
1. **Invalid Credentials**: Double-check all fields
2. **Network Issues**: Verify internet connectivity
3. **Callback Failures**: Check callback URL accessibility
4. **Transaction Limits**: Verify amount limits

### Support Contacts:
- Safaricom Business: +254 722 002 100
- Daraja Developer Support: developer@safaricom.co.ke

## Sample Configuration

### Environment Variables:
```env
MPESA_ENV=production
MPESA_CONSUMER_KEY=partner_consumer_key
MPESA_CONSUMER_SECRET=partner_consumer_secret
MPESA_SHORTCODE=partner_shortcode
MPESA_PASSKEY=partner_passkey
MPESA_CALLBACK_URL=https://yourdomain.com/api/mpesa/callback
```

### Database Setting:
```php
SchoolMpesaSetting::create([
    'school_id' => $school->id,
    'consumer_key' => 'partner_consumer_key',
    'consumer_secret' => 'partner_consumer_secret',
    'shortcode' => 'partner_shortcode',
    'passkey' => 'partner_passkey',
    'environment' => 'production',
    'callback_url' => 'https://yourdomain.com/api/mpesa/callback',
    'description' => 'Partner Business - [Business Name]',
    'is_active' => true,
]);
``` 