# Environment Variables Setup

## FIB Payment Gateway Configuration

Add the following variables to your `.env` file:

```env
# FIB Payment Gateway Configuration
FIB_BASE_URL=https://fib.stage.fib.iq
FIB_AUTH_URL=https://fib.stage.fib.iq/auth/realms/fib-online-shop/protocol/openid-connect/token
FIB_IDENTIFIER=salahadin-testig-creds
FIB_SECRET_KEY=9bcffb11-84d1-469b-b1c1-5cc765598720
```

## Steps to Add:

1. Open your `.env` file in the root directory
2. Add the above variables at the end of the file
3. Save the file
4. Clear configuration cache:
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

## Testing Credentials

- **Personal Account:**
  - Phone: 7301111215
  - Password: Personal@123
  - OTP: 123-456

- **Business Account:**
  - Phone: 7301111215
  - Password: Business@123
  - OTP: 123-456

## Production

For production, update:
```env
FIB_BASE_URL=https://fib.iq
FIB_AUTH_URL=https://fib.iq/auth/realms/fib-online-shop/protocol/openid-connect/token
```

