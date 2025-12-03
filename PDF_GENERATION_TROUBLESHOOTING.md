# PDF Generation Troubleshooting Guide

This guide helps diagnose and fix PDF generation issues, especially when the endpoint works locally but fails in production.

## Common Issues

### 1. Chrome/Chromium Not Installed

**Symptom:** Process failed exception or "Command not found" errors in logs.

**Solution:**
```bash
# Ubuntu/Debian
sudo apt-get update
sudo apt-get install -y chromium-browser

# Or for Chromium
sudo apt-get install -y chromium

# CentOS/RHEL
sudo yum install chromium

# Verify installation
which chromium-browser
# or
which chromium
```

**Configuration:**
Add to your `.env` file:
```env
LARAVEL_PDF_CHROME_PATH=/usr/bin/chromium-browser
# or
LARAVEL_PDF_CHROME_PATH=/usr/bin/chromium
```

### 2. Node.js Not Installed or Wrong Version

**Symptom:** Process failed exception related to Node.js.

**Solution:**
```bash
# Install Node.js (using NodeSource repository for Ubuntu/Debian)
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt-get install -y nodejs

# Verify installation
node --version
npm --version
```

**Configuration:**
Add to your `.env` file:
```env
LARAVEL_PDF_NODE_BINARY=/usr/bin/node
LARAVEL_PDF_NPM_BINARY=/usr/bin/npm
```

### 3. Missing System Dependencies

**Symptom:** Various errors during PDF generation.

**Solution:**
```bash
# Install required libraries for Chromium
sudo apt-get install -y \
    libnss3 \
    libatk1.0-0 \
    libatk-bridge2.0-0 \
    libcups2 \
    libdrm2 \
    libxkbcommon0 \
    libxcomposite1 \
    libxdamage1 \
    libxfixes3 \
    libxrandr2 \
    libgbm1 \
    libasound2 \
    libpango-1.0-0 \
    libcairo2
```

### 4. File Permissions Issues

**Symptom:** Cannot write temporary files or access resources.

**Solution:**
```bash
# Ensure storage directory is writable
sudo chown -R www-data:www-data storage/
sudo chmod -R 775 storage/

# Ensure temp directory exists and is writable
mkdir -p storage/app/temp
chmod 775 storage/app/temp
```

**Configuration:**
Add to your `.env` file:
```env
LARAVEL_PDF_TEMP_PATH=/path/to/your/storage/app/temp
```

### 5. View/Template Not Found

**Symptom:** "Template view not found" error in logs.

**Solution:**
1. Verify the template view exists:
   ```bash
   ls -la resources/views/templates/cv/
   ```

2. Check template name in database matches view filename:
   - Template name in DB: "Modern Professional"
   - Expected view: `modern-professional.blade.php`
   - View path: `templates.cv.modern-professional`

3. Clear view cache:
   ```bash
   php artisan view:clear
   php artisan config:clear
   php artisan cache:clear
   ```

### 6. Memory or Timeout Issues

**Symptom:** PDF generation times out or runs out of memory.

**Solution:**

**Increase PHP memory limit:**
Edit `php.ini`:
```ini
memory_limit = 512M
max_execution_time = 300
```

**Or in `.env`:**
```env
PHP_MEMORY_LIMIT=512M
```

**Increase nginx/php-fpm timeout:**
```nginx
# In nginx config
fastcgi_read_timeout 300;
```

```ini
# In php-fpm pool config
request_terminate_timeout = 300
```

### 7. Sandbox Mode Issues

**Symptom:** Chrome crashes or permission denied errors.

**Solution:**
Add to your `.env` file:
```env
LARAVEL_PDF_NO_SANDBOX=true
```

**⚠️ Warning:** Disabling sandbox reduces security. Only use in controlled environments.

### 8. Path Resolution Issues

**Symptom:** Binary paths not found.

**Solution:**

Find the actual paths on your server:
```bash
which node
which npm
which chromium-browser
which chromium
which google-chrome
```

Then configure in `.env`:
```env
LARAVEL_PDF_NODE_BINARY=/usr/bin/node
LARAVEL_PDF_NPM_BINARY=/usr/bin/npm
LARAVEL_PDF_CHROME_PATH=/usr/bin/chromium-browser
```

## Debugging Steps

### 1. Check Logs

```bash
# Laravel logs
tail -f storage/logs/laravel.log

# Look for PDF generation errors
grep -i "pdf\|browsershot\|chromium" storage/logs/laravel.log
```

### 2. Test PDF Generation Manually

Create a test route or use tinker:
```php
php artisan tinker

use Spatie\LaravelPdf\Facades\Pdf;
Pdf::view('templates.cv.modern-professional', ['cv' => ['test' => true]])
    ->format('a4')
    ->save('test.pdf');
```

### 3. Verify Configuration

```bash
php artisan config:show laravel-pdf
```

### 4. Check Environment Variables

```bash
php artisan tinker
config('laravel-pdf.browsershot.chrome_path')
config('laravel-pdf.browsershot.node_binary')
```

### 5. Test Chrome Installation

```bash
# Test if Chrome can run
chromium-browser --version
chromium-browser --headless --disable-gpu --dump-dom https://example.com

# Test with no sandbox (if needed)
chromium-browser --headless --disable-gpu --no-sandbox --dump-dom https://example.com
```

## Production Environment Variables

Add these to your production `.env` file:

```env
# PDF Generation Configuration
LARAVEL_PDF_CHROME_PATH=/usr/bin/chromium-browser
LARAVEL_PDF_NODE_BINARY=/usr/bin/node
LARAVEL_PDF_NPM_BINARY=/usr/bin/npm
LARAVEL_PDF_TEMP_PATH=/var/www/your-app/storage/app/temp
LARAVEL_PDF_NO_SANDBOX=true

# Optional: If using a custom Chrome installation
# LARAVEL_PDF_BIN_PATH=/custom/path/to/bin
# LARAVEL_PDF_INCLUDE_PATH=/custom/path/to/include
```

## Common Error Messages and Solutions

### "Command not found: node"
- **Solution:** Install Node.js and set `LARAVEL_PDF_NODE_BINARY`

### "Command not found: chromium"
- **Solution:** Install Chromium and set `LARAVEL_PDF_CHROME_PATH`

### "View [templates.cv.xxx] not found"
- **Solution:** Verify template exists and clear view cache

### "Process failed with exit code 1"
- **Solution:** Check Chrome dependencies, permissions, and sandbox settings

### "Maximum execution time exceeded"
- **Solution:** Increase PHP `max_execution_time` and web server timeout

### "Memory exhausted"
- **Solution:** Increase PHP `memory_limit`

## Server Setup Script

Here's a complete setup script for Ubuntu/Debian servers:

```bash
#!/bin/bash

# Update system
sudo apt-get update

# Install Node.js
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt-get install -y nodejs

# Install Chromium and dependencies
sudo apt-get install -y \
    chromium-browser \
    libnss3 \
    libatk1.0-0 \
    libatk-bridge2.0-0 \
    libcups2 \
    libdrm2 \
    libxkbcommon0 \
    libxcomposite1 \
    libxdamage1 \
    libxfixes3 \
    libxrandr2 \
    libgbm1 \
    libasound2 \
    libpango-1.0-0 \
    libcairo2

# Find paths
echo "Node path: $(which node)"
echo "NPM path: $(which npm)"
echo "Chromium path: $(which chromium-browser)"

# Set permissions
sudo chown -R www-data:www-data /var/www/your-app/storage
sudo chmod -R 775 /var/www/your-app/storage
```

## After Making Changes

Always run these commands after configuration changes:

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Restart PHP-FPM (if applicable)
sudo systemctl restart php8.2-fpm
# or
sudo service php-fpm restart

# Restart web server (if needed)
sudo systemctl restart nginx
# or
sudo systemctl restart apache2
```

## Testing

After fixing the issue, test with the Postman endpoint:

1. Use "Create CV (Returns PDF - Unauthenticated)" request
2. Set `Accept: application/pdf` header
3. Include `template_id` in the request body
4. Check the response - should return PDF file

If it still fails, check the logs for the specific error message and refer to the solutions above.

## Additional Resources

- [Spatie Laravel PDF Documentation](https://spatie.be/docs/laravel-pdf)
- [Browsershot Documentation](https://github.com/spatie/browsershot)
- [Chrome Headless Documentation](https://developer.chrome.com/docs/chromium/new-headless)

