# FIB Payment Gateway Integration Setup

## Overview
This guide will help you set up the FIB (First Iraqi Bank) payment gateway integration in your application.

## Backend Setup

### 1. Environment Configuration

Add the following to your `.env` file:

```env
# FIB Payment Gateway Configuration
FIB_BASE_URL=https://fib.stage.fib.iq
FIB_AUTH_URL=https://fib.stage.fib.iq/auth/realms/fib-online-shop/protocol/openid-connect/token
FIB_IDENTIFIER=salahadin-testig-creds
FIB_SECRET_KEY=9bcffb11-84d1-469b-b1c1-5cc765598720
```

### 2. Run Database Migration

```bash
php artisan migrate
```

This will create the `payments` table to store payment records.

### 3. Clear Configuration Cache

```bash
php artisan config:clear
php artisan cache:clear
```

## API Endpoints

### Create Payment
- **POST** `/api/payments`
- **Auth:** Required
- **Body:**
  ```json
  {
    "amount": 100.00,
    "currency": "IQD",
    "description": "Payment description"
  }
  ```

### Get User Payments
- **GET** `/api/payments`
- **Auth:** Required

### Get Payment Details
- **GET** `/api/payments/{id}`
- **Auth:** Required

### Check Payment Status
- **POST** `/api/payments/{id}/check-status`
- **Auth:** Required

### Cancel Payment
- **POST** `/api/payments/{id}/cancel`
- **Auth:** Required

### Payment Callback (Webhook)
- **POST** `/api/payment/callback`
- **Auth:** Not required (public endpoint)
- This endpoint receives callbacks from FIB when payment status changes

## Frontend Usage

### Access Payment Page
Navigate to `/payments` in your application.

### Features
1. **Create Payment:** Enter amount and description, then create a payment
2. **View Payment History:** See all your past payments
3. **Payment Status:** Check real-time payment status
4. **QR Code:** Display QR code for mobile payments
5. **Payment URL:** Direct link to FIB payment page

## Payment Flow

1. User creates a payment through the frontend
2. Backend creates payment record and calls FIB API
3. FIB returns payment URL and QR code
4. User is redirected to FIB payment page or scans QR code
5. User completes payment on FIB platform
6. FIB sends callback to `/api/payment/callback`
7. Payment status is updated in database

## Payment Statuses

- **pending:** Payment created but not yet processed
- **processing:** Payment submitted to FIB, awaiting completion
- **completed:** Payment successfully completed
- **failed:** Payment failed
- **cancelled:** Payment was cancelled

## Testing Credentials

Based on the provided information:

- **Personal Account:**
  - Phone: 7301111215
  - Password: Personal@123
  - OTP: 123-456

- **Business Account:**
  - Phone: 7301111215
  - Password: Business@123
  - OTP: 123-456

## Important Notes

1. **Testing Environment:** The current configuration uses the staging environment (`fib.stage.fib.iq`)
2. **Production:** For production, update `FIB_BASE_URL` to `https://fib.iq`
3. **Callback URL:** Make sure your callback URL is publicly accessible
4. **Amount Conversion:** The service automatically converts amounts to smallest currency unit (fils for IQD) by multiplying by 100
5. **Token Management:** Access tokens are automatically cached and refreshed when needed

## Troubleshooting

### Payment Creation Fails
- Check `.env` configuration
- Verify FIB credentials are correct
- Check Laravel logs: `storage/logs/laravel.log`

### Callback Not Working
- Ensure callback URL is publicly accessible
- Check that route is not behind authentication
- Verify FIB has your callback URL configured

### Token Errors
- Verify `FIB_IDENTIFIER` and `FIB_SECRET_KEY` are correct
- Check network connectivity to FIB servers
- Review authentication logs

## Files Created

### Backend
- `app/Models/Payment.php` - Payment model
- `app/Services/PaymentService.php` - FIB API service
- `app/Http/Controllers/Api/PaymentController.php` - Payment controller
- `database/migrations/*_create_payments_table.php` - Payments table migration
- `config/payment.php` - Payment configuration

### Frontend
- `frontend/src/services/payments.js` - Payment API service
- `frontend/src/stores/payments.js` - Payment store (Pinia)
- `frontend/src/views/payments/PaymentView.vue` - Payment UI component

## Next Steps

1. Add payment link to navigation menu
2. Integrate payments into your business logic (subscriptions, purchases, etc.)
3. Add payment notifications
4. Implement payment receipts/invoices
5. Add admin panel for viewing all payments

