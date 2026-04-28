# Server Specifications & Upload Configuration

**Document Created:** April 28, 2026  
**Server:** glassbooth.wowbynow.com.my  
**Purpose:** Reference for upload configuration, limits, and troubleshooting

---

## 🖥️ Server Environment

### Operating System
- **OS:** Ubuntu/Debian Linux
- **Filesystem:** /dev/vda1 (58GB total, 9.4GB available)
- **Web Root:** `/var/www/glassbooth.wowbynow.com.my`

### Web Server
- **Apache Version:** 2.4
- **Modules:** mod_php, mod_ssl, mod_rewrite
- **Service:** `apache2.service` (systemd)
- **User:** `www-data:www-data`

### PHP Configuration
- **PHP Version:** 8.1
- **SAPI:** Apache Module (mod_php)
- **Config File:** `/etc/php/8.1/apache2/php.ini`

### Framework
- **Framework:** Laravel
- **Environment:** Production
- **Log Location:** `/var/www/glassbooth.wowbynow.com.my/storage/logs/laravel.log`

---

## 📊 Upload Limits & Timeouts

### PHP Settings (Active)

**Primary Configuration** (`/etc/php/8.1/apache2/php.ini`):
```ini
upload_max_filesize = 50M
post_max_size = 60M
memory_limit = -1                    ; Unlimited
max_execution_time = 30000           ; 8.3 hours
max_input_time = 30000               ; 8.3 hours
upload_tmp_dir = /var/www/glassbooth.wowbynow.com.my/storage/tmp
```

**.htaccess Overrides** (`public/.htaccess` - TAKES PRECEDENCE):
```apache
php_value upload_max_filesize 512M
php_value post_max_size 512M
php_value memory_limit 512M
php_value max_execution_time 300
php_value max_input_time 300
```

**Effective Limits:**
- ✅ Upload Max: **512MB** (from .htaccess)
- ✅ POST Max: **512MB** (from .htaccess)
- ✅ Memory: **512MB** (from .htaccess)
- ✅ Execution Timeout: **300 seconds** (5 minutes, from .htaccess)

### Apache Settings

**Virtual Host Config** (`/etc/apache2/sites-enabled/glassbooth.wowbynow.com.my-le-ssl.conf`):
```apache
LimitRequestBody 536870912              # 512MB max request size
Timeout 600                             # 10 minutes connection timeout
RequestReadTimeout header=20-600,minrate=500 body=20-600,minrate=10
    # Fixed for tauri-plugin-upload + laravel-chunk-upload
    # Allows slow/unstable network connections (min 10 bytes/s instead of 500 bytes/s)
    # Max 600 seconds for body upload (was 10 seconds which killed slow uploads)
```

**Global Config** (`/etc/apache2/apache2.conf`):
```apache
Timeout 600
KeepAliveTimeout 60
```

**mod_reqtimeout** (Default - overridden by VirtualHost):
```apache
RequestReadTimeout header=20-40,minrate=500
RequestReadTimeout body=10,minrate=500    # Too aggressive for uploads - OVERRIDDEN
```

### Firewall (UFW)
```
22/tcp (SSH) - LIMIT
80/tcp (HTTP) - ALLOW (Apache Full)
443/tcp (HTTPS) - ALLOW (Apache Full)
3306/tcp (MySQL) - ALLOW
```

---

## 🚨 Known Upload Issues

### UPLOAD_ERR_PARTIAL (Error Code 3) - FIXED ✅

**Status:** RESOLVED (April 28, 2026)  
**Root Cause:** Apache's `mod_reqtimeout` was too aggressive  
**Solution Applied:** Increased RequestReadTimeout and lowered minimum data rate

**What Was Wrong:**
```apache
# Old setting (killed slow uploads):
RequestReadTimeout body=10,minrate=500
# Required 500 bytes/sec minimum - killed Tauri uploads over slow connections
```

**Fix Applied:**
```apache
# New setting (allows slow/unstable connections):
RequestReadTimeout header=20-600,minrate=500 body=20-600,minrate=10
# Allows minimum 10 bytes/sec, waits up to 600 seconds
```

**For Windows Client Using:**
- **tauri-plugin-upload** (Tauri desktop framework)
- **laravel-chunk-upload** (backend package)
- Multipart/form-data uploads should now work ✅

---

## ✅ Upload Solutions

### Solution 1: Multipart Chunked Upload (PRIMARY - Now Fixed)

**For tauri-plugin-upload + laravel-chunk-upload:**
```
POST /api/upload-video/chunked     (multipart/form-data)
POST /api/upload_video_resumable   (alias)
```

**Uses:** ChunkedVideoUploadController with laravel-chunk-upload package

**Status:** ✅ Now working after mod_reqtimeout fix

---

### Solution 2: Base64 JSON Uploads (FALLBACK)

**Advantages:**
- ✅ More reliable over very unstable connections
- ✅ Bypasses multipart parsing entirely
- ✅ Already tested and working
- ⚠️ 33% size overhead (acceptable for reliability)

**Implementation:**

#### For Video Chunks
```javascript
// Endpoint: POST /api/upload-video/chunk
// Content-Type: application/json

{
  "upload_id": "vid_xxx",
  "chunk_index": 0,
  "total_chunks": 10,
  "filename": "video.mp4",
  "chunk_data": "<base64-encoded-binary-data>"
}

// After all chunks uploaded:
// POST /api/upload-video/assemble

{
  "upload_id": "vid_xxx",
  "total_chunks": 10,
  "filename": "video.mp4"
}
```

**Client Example (JavaScript):**
```javascript
// Read file chunk as base64
const chunkBlob = file.slice(start, end);
const reader = new FileReader();
reader.readAsDataURL(chunkBlob);
reader.onload = async () => {
  const base64Data = reader.result.split(',')[1]; // Remove data:mime;base64, prefix
  
  await fetch('/api/upload-video/chunk', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      upload_id: uploadId,
      chunk_index: i,
      total_chunks: totalChunks,
      filename: file.name,
      chunk_data: base64Data
    })
  });
};
```

**Recommended Chunk Size:** 1MB (1,048,576 bytes)

---

## 🔌 Available API Endpoints

### Video Upload Endpoints

#### 1. Single Video Upload (Original)
```
POST /api/upload-video
Content-Type: multipart/form-data

Form Data:
  - file: (binary video file)
  - OR video: (binary video file)
```

**Status:** ⚠️ Affected by UPLOAD_ERR_PARTIAL  
**Max Size:** 512MB (theoretical), 2MB (practical due to error 3)

#### 2. Chunked Video Upload
```
POST /api/upload-video/chunk
Content-Type: application/json

Body:
{
  "upload_id": "string",
  "chunk_index": number,
  "total_chunks": number,
  "filename": "string",
  "chunk_data": "base64-string"
}

Response (Success):
{
  "message": "Chunk uploaded successfully",
  "chunk_index": 0,
  "upload_id": "vid_xxx"
}

Response (Error):
{
  "error": "error_description"
}
```

#### 3. Assemble Chunks
```
POST /api/upload-video/assemble
Content-Type: application/json

Body:
{
  "upload_id": "string",
  "total_chunks": number,
  "filename": "string"
}

Response (Success):
{
  "message": "Video assembled successfully",
  "video_url": "/storage/videos/filename.mp4"
}
```

### Image Capture Endpoint
```
POST /api/capture
Content-Type: multipart/form-data (legacy)
Content-Type: application/json (recommended)

Multipart Form Data:
  - image: (binary image file)

JSON Body (for base64):
{
  "image": "data:image/jpeg;base64,..."
}
```

**Note:** Currently experiencing JSON parsing issues with base64 data. Investigating control character encoding issue.

---

## 📝 Logging & Debugging

### Log File Location
```
/var/www/glassbooth.wowbynow.com.my/storage/logs/laravel.log
```

### View Recent Logs
```bash
# Last 50 lines
tail -50 /var/www/glassbooth.wowbynow.com.my/storage/logs/laravel.log

# Follow logs in real-time
tail -f /var/www/glassbooth.wowbynow.com.my/storage/logs/laravel.log

# Filter video upload logs
grep -E "VideoChunk|VideoUpload|VideoAssemble" laravel.log | tail -20
```

### Key Log Markers

**Successful Base64 Chunk Upload:**
```
VideoChunk: request received {"has_file":false, "content_length":"..."}
VideoChunk: decoding from base64 {"chunk_size":1048576}
VideoChunk: chunk stored successfully from base64
```

**Failed Multipart Upload:**
```
VideoChunk: request received {"has_file":false, "content_length":"2097973"}
VideoChunk: missing fields {"chunk_index":0, "has_file":false}
```

**Upload Error 3:**
```
raw_FILES":{"file":{"error":3, "size":0, "tmp_name":""}}
```

---

## 🔧 Maintenance Commands

### Clear Laravel Caches
```bash
cd /var/www/glassbooth.wowbynow.com.my

# Clear all caches
php artisan optimize:clear

# Individual cache clear
php artisan config:clear
php artisan route:clear
php artisan cache:clear
php artisan view:clear
```

### Restart Apache
```bash
sudo systemctl restart apache2
sudo systemctl status apache2
```

### Check PHP Configuration
```bash
# Via CLI (may differ from Apache)
php -i | grep -E "upload_max_filesize|post_max_size"

# Via Apache (accurate)
curl -s http://localhost/phpinfo.php | grep upload_max_filesize
```

### Check Disk Space
```bash
df -h /var/www/glassbooth.wowbynow.com.my
```

### Check Temp Directory
```bash
ls -la /var/www/glassbooth.wowbynow.com.my/storage/tmp/
du -sh /var/www/glassbooth.wowbynow.com.my/storage/tmp/
```

---

## 🐛 Troubleshooting Guide

### Issue: "Upload failed with error 3"

**Diagnosis:**
```bash
tail -100 storage/logs/laravel.log | grep "error"
```

**Solution:**
1. Switch client to base64 JSON uploads (see Working Solutions above)
2. Client should NOT use multipart/form-data
3. Use chunking with 1MB chunks for large files

### Issue: "413 Request Entity Too Large"

**Diagnosis:**
```bash
# Check Apache limit
grep LimitRequestBody /etc/apache2/sites-enabled/*.conf
```

**Solution:**
```bash
# Increase in virtual host config
sudo nano /etc/apache2/sites-enabled/glassbooth.wowbynow.com.my-le-ssl.conf
# Add: LimitRequestBody 536870912

sudo systemctl restart apache2
```

### Issue: "Maximum execution time exceeded"

**Solution:**
```bash
# Edit .htaccess
nano /var/www/glassbooth.wowbynow.com.my/public/.htaccess
# Increase: php_value max_execution_time 600

# Or edit php.ini
sudo nano /etc/php/8.1/apache2/php.ini
# Increase: max_execution_time = 600

sudo systemctl restart apache2
```

### Issue: JSON parsing error (Control character error)

**Symptoms:**
```
json_error:3, json_error_msg:"Control character error, possibly incorrectly encoded"
```

**Current Status:** Under investigation  
**Temporary Workaround:** Ensure client sends clean base64 without extra whitespace or line breaks

---

## 📚 Reference Files

### Configuration Files
- PHP Config: `/etc/php/8.1/apache2/php.ini`
- Apache Main: `/etc/apache2/apache2.conf`
- Virtual Host: `/etc/apache2/sites-enabled/glassbooth.wowbynow.com.my-le-ssl.conf`
- .htaccess: `/var/www/glassbooth.wowbynow.com.my/public/.htaccess`

### Controllers
- Single Upload: `app/Http/Controllers/VideoUploadController.php`
- Chunked Upload: `app/Http/Controllers/VideoChunkController.php`
- Image Capture: `app/Http/Controllers/CaptureController.php`

### Routes
- API Routes: `routes/api.php`

### Documentation
- Chunked Upload Guide: `CHUNKED_UPLOAD_CLIENT.md`
- This Document: `SERVER_SPECS_UPLOAD.md`

---

## 💡 Best Practices

### For Client Development

1. **Always use chunking** for files > 5MB
2. **Use base64 JSON** instead of multipart/form-data
3. **Keep chunks at 1MB** for optimal reliability
4. **Implement retry logic** for failed chunks
5. **Show progress indicators** (chunk X of Y)
6. **Validate base64 encoding** before sending
7. **Set appropriate timeouts** (at least 60 seconds per chunk)

### For Server Maintenance

1. **Monitor log file size** (rotate if > 100MB)
2. **Clean up temp directories** periodically
3. **Check disk space** weekly (keep > 10GB free)
4. **Review error logs** daily during active development
5. **Test limits** after any configuration changes

---

## 📞 Support Information

**Server IP:** (Contact system administrator)  
**Domain:** glassbooth.wowbynow.com.my  
**Repository:** whei2419/Wowsome-micorsite  
**Branch:** iproperty-camera-server

**Last Updated:** April 28, 2026  
**Maintained By:** Development Team
