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

**Symptom:** Process failed exception related to Node.js, or `SyntaxError: Unexpected token '?'` errors.

**⚠️ Important:** Browsershot requires **Node.js 14.0.0 or higher**. The nullish coalescing operator (`??`) used in Browsershot requires Node.js 14+.

**Check your current Node.js version:**
```bash
node --version
```

**If you see Node.js 12.x or lower, you MUST upgrade:**

**Solution:**
```bash
# Remove old Node.js (if installed via apt)
sudo apt-get remove nodejs npm

# Install Node.js 20.x LTS (recommended) using NodeSource repository
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt-get install -y nodejs

# Or install Node.js 18.x LTS
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs

# Verify installation (should show v14.x, v18.x, or v20.x)
node --version
npm --version
```

**If you have multiple Node.js versions installed, ensure the correct one is in PATH:**
```bash
# Check which node is being used
which node
/usr/bin/node --version

# If the wrong version is being used, update your PATH or use a Node version manager
```

**⚠️ Common Issue: Node.js is installed but wrong binary path is configured**

If `node -v` shows a correct version (14+) but you still get the syntax error, Browsershot might be using a different Node.js binary:

```bash
# 1. Find where your correct Node.js is installed
which node
# Example output: /usr/local/bin/node or /opt/nodejs/bin/node

# 2. Check the version at that path
/usr/local/bin/node --version  # or whatever path you found

# 3. Check what version /usr/bin/node is (this is often the default)
/usr/bin/node --version

# 4. If /usr/bin/node is old, find all Node.js installations
whereis node
# or
find /usr -name node -type f 2>/dev/null
find /usr/local -name node -type f 2>/dev/null

# 5. Test the nullish coalescing operator with the binary Browsershot will use
/usr/bin/node -e "console.log(1 ?? 2)"
# If this fails, /usr/bin/node is too old
```

**Configuration:**
Add to your `.env` file (use the path from `which node`):
```env
LARAVEL_PDF_NODE_BINARY=/usr/bin/node
LARAVEL_PDF_NPM_BINARY=/usr/bin/npm
```

**After upgrading Node.js:**
```bash
# Clear Laravel caches
php artisan config:clear
php artisan cache:clear

# Restart PHP-FPM to pick up new Node.js version
sudo systemctl restart php8.2-fpm
# or
sudo service php-fpm restart
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

### 6. Verify Node.js Version and Binary Path

```bash
# Check Node.js version (must be 14.0.0 or higher)
node --version

# Find where Node.js is installed
which node

# Check the Node.js binary that Browsershot will use (default is /usr/bin/node)
/usr/bin/node --version

# Check what Laravel is configured to use
php artisan tinker
# Then run: config('laravel-pdf.browsershot.node_binary')

# Test if Node.js can run the Browsershot script syntax
node -e "console.log(1 ?? 2)"
# Should output: 1
# If you get "SyntaxError: Unexpected token '?'", Node.js is too old

# Test with the specific binary path Browsershot uses
/usr/bin/node -e "console.log(1 ?? 2)"
# If this fails but `node -e` works, you need to configure LARAVEL_PDF_NODE_BINARY

# Find all Node.js installations
whereis node
find /usr -name node -type f 2>/dev/null | head -10
find /usr/local -name node -type f 2>/dev/null | head -10
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

### "SyntaxError: Unexpected token '?'" or "SyntaxError: Unexpected token '??'"
- **Cause:** Node.js version is too old (requires Node.js 14.0.0 or higher), OR the wrong Node.js binary path is configured
- **Solution:** 
  1. Check current version: `node --version`
  2. **If `node -v` shows v14+ but error persists**, check the binary path:
     ```bash
     # Find where your correct Node.js is
     which node
     /usr/local/bin/node --version  # or wherever it is
     
     # Check what /usr/bin/node is (Browsershot default)
     /usr/bin/node --version
     
     # Test the syntax with the binary Browsershot uses
     /usr/bin/node -e "console.log(1 ?? 2)"
     ```
  3. **If `/usr/bin/node` is old**, either:
     - **Option A:** Update `.env` to point to the correct Node.js:
       ```env
       LARAVEL_PDF_NODE_BINARY=/usr/local/bin/node  # or wherever your v20+ Node.js is
       ```
     - **Option B:** Create a symlink or replace the old binary:
       ```bash
       # Backup old binary
       sudo mv /usr/bin/node /usr/bin/node.old
       # Create symlink to correct Node.js
       sudo ln -s /usr/local/bin/node /usr/bin/node
       # Verify
       /usr/bin/node --version
       ```
  4. **If version is below 14.x**, upgrade Node.js:
     ```bash
     curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
     sudo apt-get install -y nodejs
     ```
  5. Verify: `node --version` (should show v14.x, v18.x, or v20.x)
  6. Clear Laravel caches and restart PHP-FPM:
     ```bash
     php artisan config:clear
     php artisan cache:clear
     sudo systemctl restart php8.2-fpm
     ```

### "View [templates.cv.xxx] not found"
- **Solution:** Verify template exists and clear view cache

### "Process failed with exit code 1"
- **Solution:** Check Chrome dependencies, permissions, sandbox settings, and Node.js version

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

# Remove old Node.js if present (to avoid conflicts)
sudo apt-get remove nodejs npm -y 2>/dev/null || true

# Install Node.js 20.x LTS (required: Node.js 14+ for Browsershot)
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt-get install -y nodejs

# Verify Node.js version (must be 14.0.0 or higher)
node --version
npm --version

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

