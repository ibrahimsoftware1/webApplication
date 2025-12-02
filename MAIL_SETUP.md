# Free Email Setup - No Limits

## Option 1: Log Driver (Simplest - No Setup Required)
Emails will be saved to `storage/logs/laravel.log` instead of being sent.

**Update your `.env` file:**
```env
MAIL_MAILER=log
```

That's it! All emails will be logged to the file. You can view them in `storage/logs/laravel.log`.

---

## Option 2: Mailpit (Recommended - Local Email Testing Server)
Mailpit is a free, local email testing server with NO limits. It provides a web interface to view all emails.

### Installation (Windows):

**Method 1: Using Chocolatey (if you have it):**
```powershell
choco install mailpit
```

**Method 2: Download Binary:**
1. Go to: https://github.com/axllent/mailpit/releases
2. Download `mailpit-windows-amd64.exe` (or `mailpit-windows-arm64.exe` for ARM)
3. Rename it to `mailpit.exe`
4. Place it in a folder (e.g., `C:\mailpit\`)
5. Add that folder to your PATH, OR run it directly

**Method 3: Using Docker (if you have Docker):**
```bash
docker run -d -p 8025:8025 -p 1025:1025 axllent/mailpit
```

### Running Mailpit:

**If installed via Chocolatey or PATH:**
```powershell
mailpit
```

**If using downloaded binary:**
```powershell
C:\path\to\mailpit.exe
```

**If using Docker:**
The container will start automatically.

### Access Mailpit Web Interface:
Open your browser: http://localhost:8025

### Update your `.env` file:
```env
MAIL_MAILER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"
```

---

## Option 3: Array Driver (For Testing)
Emails are stored in memory (lost on page refresh).

**Update your `.env` file:**
```env
MAIL_MAILER=array
```

You can access emails in your code:
```php
Mail::fake(); // In tests
// or
$emails = Mail::getSentMessages(); // In development
```

---

## Quick Setup (Recommended: Log Driver)

Just update your `.env`:
```env
MAIL_MAILER=log
```

Then clear config cache:
```bash
php artisan config:clear
```

All emails will appear in `storage/logs/laravel.log` - search for "Message-ID" to find emails.

