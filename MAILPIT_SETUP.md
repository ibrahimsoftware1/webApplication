# Mailpit Setup - Quick Guide

## ✅ Mailpit Found!

Your Mailpit is located at: `C:\Users\ichom\mailpit-windows-amd64\mailpit.exe`

## How to Run Mailpit:

### Option 1: Double-click the batch file
I've created `start-mailpit.bat` - just double-click it!

### Option 2: Run from Command Prompt
```cmd
cd C:\Users\ichom\mailpit-windows-amd64
mailpit.exe
```

### Option 3: Run from PowerShell
```powershell
cd C:\Users\ichom\mailpit-windows-amd64
.\mailpit.exe
```

## After Starting Mailpit:

1. **Open your browser:** http://localhost:8025
2. **Update your `.env` file:**
   ```env
   MAIL_MAILER=smtp
   MAIL_HOST=127.0.0.1
   MAIL_PORT=1025
   MAIL_USERNAME=null
   MAIL_PASSWORD=null
   MAIL_ENCRYPTION=null
   ```

3. **Clear config cache:**
   ```bash
   php artisan config:clear
   ```

4. **Test it:** Register a new user and check http://localhost:8025

## Keep Mailpit Running:

- Keep the command window open, OR
- Run it in the background (it will keep running)

## Alternative: Use Log Driver (Even Simpler!)

If you don't want to run Mailpit, just use the log driver:

**In `.env`:**
```env
MAIL_MAILER=log
```

Then run: `php artisan config:clear`

Emails will be in `storage/logs/laravel.log`

