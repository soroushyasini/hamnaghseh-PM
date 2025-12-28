# Security Best Practices for File Upload

**Version:** 1.2.0  
**Created by:** Soroush Yasini  
**Date:** 28/12/2025

## 🎯 Overview

This document outlines the security architecture and best practices for file upload functionality in the Hamnaghseh PM plugin. Understanding these measures is critical for maintaining a secure system.

## 🛡️ Threat Model

### Identified Threats

#### 1. ZIP Bomb (Compression Bomb)
**Description:** Malicious ZIP file with extreme compression ratio that expands to enormous size when extracted.

**Attack Scenario:**
```
Attacker uploads 1 MB ZIP file
→ Extracts to 100 GB
→ Fills server disk
→ System crashes
```

**Our Protection:** 
- Maximum compression ratio: 100:1
- Maximum uncompressed size: 2 GB
- Early detection before extraction

#### 2. Malicious File Uploads
**Description:** Uploading executable files or scripts disguised as legitimate files.

**Attack Scenario:**
```
Attacker renames shell.php → map.kml
→ Uploads to server
→ Executes via direct URL access
→ Server compromised
```

**Our Protection:**
- MIME type validation (checks actual content)
- Extension whitelist (not blacklist)
- No execution permissions on upload directory
- Files stored on isolated MinIO server

#### 3. Directory Traversal
**Description:** Using special characters in filenames to access parent directories.

**Attack Scenario:**
```
Attacker uploads: ../../wp-config.php
→ Overwrites WordPress config
→ Site compromised
```

**Our Protection:**
- Filename sanitization removes `../`, `./`, `\\`
- WordPress `sanitize_file_name()` integration
- MinIO isolated storage (not web root)

#### 4. XML External Entity (XXE) Attack
**Description:** Malicious XML entities in KML/GPX files that read local files.

**Attack Scenario:**
```xml
<!DOCTYPE kml [
  <!ENTITY xxe SYSTEM "file:///etc/passwd">
]>
<kml>&xxe;</kml>
```

**Our Protection:**
- Scans for `<!ENTITY` declarations
- Blocks SYSTEM references to local files
- Early detection before XML parsing

#### 5. Resource Exhaustion
**Description:** Uploading extremely large files to consume storage and bandwidth.

**Attack Scenario:**
```
Attacker creates 10 free accounts
→ Each uploads max size files
→ Storage exhausted
→ Legitimate users cannot upload
```

**Our Protection:**
- Per-tier file size limits
- Per-format size restrictions
- Storage quota per user
- Rate limiting (to be implemented)

#### 6. MIME Type Spoofing
**Description:** Changing file extension to bypass filters while keeping malicious content.

**Attack Scenario:**
```
Attacker creates virus.exe
→ Renames to map.kml
→ Extension check passes
→ But MIME type reveals executable
```

**Our Protection:**
- MIME type validation using PHP `finfo_file()`
- Checks actual file content, not extension
- Comprehensive MIME type database

## 🔒 Security Layers

The plugin implements **6 defense layers** (Defense in Depth):

### Layer 1: User Authentication & Authorization
**Location:** `class-file-validator.php` → `can_user_upload()`

**Checks:**
- User is logged in
- User has valid subscription (Premium/Enterprise)
- Trial status for free users
- User has upload permission for specific project

**Code Example:**
```php
$can_upload = Hamnaghsheh_File_Validator::can_user_upload($user_id);
if (!$can_upload['can_upload']) {
    return ['valid' => false, 'message' => $can_upload['message']];
}
```

**What it prevents:**
- Anonymous uploads
- Free users exceeding limits
- Unauthorized project access

### Layer 2: File Extension Validation
**Location:** `class-file-validator.php` → `validate_file_type()`

**Checks:**
- Extension is in whitelist for user's tier
- Extension is lowercase normalized
- Extension exists

**Code Example:**
```php
$type_check = Hamnaghsheh_File_Validator::validate_file_type($file['name'], $user_id);
if (!$type_check['valid']) {
    return ['valid' => false, 'message' => $type_check['message']];
}
```

**What it prevents:**
- Uploading disallowed file types
- Extension-based exploits
- Tier restriction bypass

### Layer 3: File Size Validation
**Location:** `class-file-security.php` → `validate_file_size()`

**Checks:**
- File size under tier limit
- File size under format-specific limit
- Respects whichever is more restrictive

**Code Example:**
```php
$size_check = Hamnaghsheh_File_Security::validate_file_size(
    $file['size'],
    $access_level,
    $file_ext
);
```

**What it prevents:**
- Resource exhaustion
- Storage overflow
- Bandwidth abuse

### Layer 4: MIME Type Validation
**Location:** `class-file-security.php` → `validate_mime_type()`

**Checks:**
- Actual file content type (using PHP fileinfo)
- MIME type matches extension
- MIME type is in allowed list

**Code Example:**
```php
$mime_check = Hamnaghsheh_File_Security::validate_mime_type(
    $file['tmp_name'],
    $file_ext
);
```

**What it prevents:**
- MIME type spoofing
- Malicious file disguise
- Executable uploads

### Layer 5: Format-Specific Security Checks
**Location:** `class-file-security.php` → Multiple methods

#### 5a. ZIP Bomb Detection
```php
$zip_check = Hamnaghsheh_File_Security::check_zip_bomb($file['tmp_name']);
```

**Process:**
1. Open ZIP without extraction
2. Sum all file sizes inside
3. Calculate compression ratio
4. Check against thresholds

#### 5b. XXE Vulnerability Scan (KML/GPX)
```php
$kml_check = Hamnaghsheh_File_Security::scan_kml_external_refs($file['tmp_name']);
```

**Process:**
1. Read first 8KB of file
2. Search for `<!ENTITY` patterns
3. Search for SYSTEM declarations
4. Block if found

#### 5c. DBF Header Validation (Shapefiles)
```php
$dbf_check = Hamnaghsheh_File_Security::validate_dbf_header($file['tmp_name']);
```

**Process:**
1. Read first byte (version marker)
2. Check against valid DBF versions
3. Reject if invalid

**What it prevents:**
- ZIP bombs
- XXE attacks
- Corrupted/malicious shapefiles

### Layer 6: Storage Quota Validation
**Location:** `class-file-validator.php` → `check_storage_quota()`

**Checks:**
- Current storage usage across all projects
- Available space for new file
- Respects tier storage limits

**Code Example:**
```php
$quota_check = Hamnaghsheh_File_Validator::check_storage_quota(
    $project_id,
    $new_file_size,
    $owner_id
);
```

**What it prevents:**
- Storage overflow
- One user consuming all space
- Resource exhaustion

## ⚙️ Configuration Recommendations

### File Size Limits

**Conservative (Default):**
```php
const MAX_FILE_SIZE_PREMIUM = 52428800;     // 50 MB
const MAX_FILE_SIZE_ENTERPRISE = 524288000;  // 500 MB
```

**Moderate:**
```php
const MAX_FILE_SIZE_PREMIUM = 104857600;     // 100 MB
const MAX_FILE_SIZE_ENTERPRISE = 1073741824; // 1 GB
```

**Aggressive (High-risk):**
```php
const MAX_FILE_SIZE_PREMIUM = 209715200;     // 200 MB
const MAX_FILE_SIZE_ENTERPRISE = 2147483648; // 2 GB
```

**Recommendation:** Start conservative, increase based on monitoring.

### ZIP Bomb Thresholds

**Conservative (Default):**
```php
const MAX_COMPRESSION_RATIO = 100;        // 100:1
const MAX_UNCOMPRESSED_SIZE = 2147483648; // 2 GB
```

**Moderate:**
```php
const MAX_COMPRESSION_RATIO = 200;        // 200:1
const MAX_UNCOMPRESSED_SIZE = 5368709120; // 5 GB
```

**Recommendation:** Keep conservative unless you have monitoring in place.

### Storage Quotas

**Per-Tier Allocation:**
```php
'premium' => [
    'storage' => 104857600,    // 100 MB
],
'enterprise' => [
    'storage' => 1073741824,   // 1 GB
]
```

**Recommendation:** Monitor actual usage monthly and adjust.

## 🔧 PHP Configuration Requirements

### Required PHP Extensions

```ini
; File info for MIME detection
extension=fileinfo

; ZIP handling
extension=zip

; XML processing (for KML/GPX)
extension=xml
extension=libxml

; Database
extension=mysqli
```

### Recommended php.ini Settings

```ini
; Upload limits (should match or exceed your limits)
upload_max_filesize = 500M
post_max_size = 550M
memory_limit = 256M

; Execution time for large files
max_execution_time = 300
max_input_time = 300

; Disable dangerous functions
disable_functions = exec,passthru,shell_exec,system,proc_open,popen

; Enable open_basedir restriction
open_basedir = /var/www/html:/tmp

; Disable allow_url_fopen for includes
allow_url_fopen = On
allow_url_include = Off
```

### WordPress Configuration

**wp-config.php:**
```php
// Disable file editing from admin
define('DISALLOW_FILE_EDIT', true);

// Limit post revisions (saves space)
define('WP_POST_REVISIONS', 3);

// Enable automatic updates
define('WP_AUTO_UPDATE_CORE', true);
```

## 📊 Monitoring and Logging

### What to Monitor

1. **Failed Upload Attempts**
   - Track by user
   - Track by reason (size, type, security)
   - Alert on patterns

2. **Storage Usage Trends**
   - Total usage per tier
   - Growth rate
   - Users near quota

3. **Security Violations**
   - ZIP bomb attempts
   - XXE attempts
   - MIME spoofing attempts

### Logging Implementation

**Add to `class-file-security.php`:**
```php
private static function log_security_event($type, $details) {
    error_log(sprintf(
        '[HAMNAGHSHEH-SECURITY] %s | User: %d | Details: %s',
        $type,
        get_current_user_id(),
        json_encode($details)
    ));
}
```

**Usage:**
```php
if (!$validation['valid']) {
    self::log_security_event('UPLOAD_BLOCKED', [
        'reason' => 'ZIP_BOMB',
        'file' => $filename,
        'ratio' => $compression_ratio
    ]);
}
```

### Log Analysis

**Check for patterns:**
```bash
# Find all security events
grep 'HAMNAGHSHEH-SECURITY' /var/log/apache2/error.log

# Count by type
grep 'HAMNAGHSHEH-SECURITY' error.log | cut -d'|' -f1 | sort | uniq -c

# Find suspicious users
grep 'ZIP_BOMB' error.log | grep -oP 'User: \K\d+' | sort | uniq -c
```

## 🚨 Incident Response Procedures

### Suspected Malicious Upload

**Immediate Actions:**
1. Identify the file ID and user
2. Block user account temporarily
3. Delete file from MinIO
4. Remove database record
5. Check if file was accessed/downloaded
6. Review user's other uploads

**Investigation:**
```sql
-- Find all files from suspicious user
SELECT * FROM wp_hamnaghsheh_files 
WHERE user_id = [SUSPICIOUS_USER_ID];

-- Check download history
SELECT * FROM wp_hamnaghsheh_file_logs 
WHERE user_id = [SUSPICIOUS_USER_ID] 
AND action_type = 'download';
```

### ZIP Bomb Detected

**Actions:**
1. File automatically blocked (no upload)
2. Log event with details
3. Review user's history
4. If pattern detected, ban user
5. Update thresholds if legitimate file

### XXE Attack Detected

**Actions:**
1. File automatically blocked
2. **CRITICAL:** Check server logs for file access attempts
3. Review all KML/GPX files from same user
4. Ban user immediately
5. Report to security team

### Storage Quota Exceeded

**Actions:**
1. Identify users over quota
2. Send notification emails
3. Temporarily block uploads
4. Offer cleanup or upgrade
5. Auto-delete oldest files (optional, with warning)

## 🔐 WordPress Security Integration

### User Capabilities

The plugin integrates with WordPress capabilities:

```php
// Admin can upload to any project
if (current_user_can('hamnaghsheh_admin')) {
    $has_permission = true;
}
```

**Custom Capabilities:**
- `hamnaghsheh_admin` - Full access
- `hamnaghsheh_upload` - Can upload files
- `hamnaghsheh_manage_projects` - Can manage own projects

### Nonce Verification

All AJAX/form submissions use WordPress nonces:

```php
// Generate nonce
$nonce = wp_create_nonce('hamnaghsheh_upload_nonce');

// Verify nonce
if (!wp_verify_nonce($_POST['nonce'], 'hamnaghsheh_upload_nonce')) {
    wp_die('Invalid nonce');
}
```

### Session Management

Uses WordPress sessions via PHP sessions:

```php
if (!session_id()) {
    session_start();
}

$_SESSION['alert'] = [
    'type' => 'error',
    'message' => 'Upload failed'
];
```

## 🛠️ Additional Hardening Measures

### 1. MinIO Bucket Policy

**Configure MinIO bucket as private:**
```json
{
    "Version": "2012-10-17",
    "Statement": [
        {
            "Effect": "Deny",
            "Principal": "*",
            "Action": "s3:GetObject",
            "Resource": "arn:aws:s3:::hamnaghsheh/*",
            "Condition": {
                "StringNotEquals": {
                    "aws:Referer": "https://hamnaghsheh.ir/*"
                }
            }
        }
    ]
}
```

### 2. Web Server Configuration

**Apache (.htaccess in uploads directory):**
```apache
# Disable PHP execution
<FilesMatch "\.ph(p[3-7]?|tml)$">
    Deny from all
</FilesMatch>

# Disable directory listing
Options -Indexes

# Force download for certain types
<FilesMatch "\.(kml|kmz|shp|dbf)$">
    Header set Content-Disposition "attachment"
</FilesMatch>
```

**Nginx:**
```nginx
location ~* /wp-content/uploads/hamnaghsheh/ {
    # Disable PHP execution
    location ~ \.php$ {
        deny all;
    }
    
    # Force download
    location ~* \.(kml|kmz|shp|dbf)$ {
        add_header Content-Disposition "attachment";
    }
}
```

### 3. Database Security

**Prepared Statements (Already Implemented):**
```php
// ✅ GOOD - Uses prepared statement
$wpdb->get_row($wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}hamnaghsheh_files WHERE id = %d",
    $file_id
));

// ❌ BAD - Vulnerable to SQL injection
$wpdb->get_row("SELECT * FROM files WHERE id = {$file_id}");
```

### 4. Content Security Policy (CSP)

**Add to wp-config.php:**
```php
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com; style-src 'self' 'unsafe-inline'");
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: SAMEORIGIN");
header("X-XSS-Protection: 1; mode=block");
```

### 5. Rate Limiting

**Implement with Redis (Future Enhancement):**
```php
function hamnaghsheh_check_rate_limit($user_id) {
    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379);
    
    $key = "upload_rate:{$user_id}";
    $count = $redis->incr($key);
    
    if ($count === 1) {
        $redis->expire($key, 3600); // 1 hour window
    }
    
    if ($count > 100) { // Max 100 uploads per hour
        return false;
    }
    
    return true;
}
```

## 🧪 Testing Security

### Test Cases

#### 1. Test ZIP Bomb Protection
```php
// Create test ZIP with high compression
$zip = new ZipArchive();
$zip->open('bomb.zip', ZipArchive::CREATE);

// Add large uncompressed data
$data = str_repeat('A', 10 * 1024 * 1024); // 10 MB of 'A'
$zip->addFromString('test.txt', $data);
$zip->close();

// Try to upload - should be blocked
```

#### 2. Test XXE Protection
```xml
<!-- malicious.kml -->
<!DOCTYPE kml [
  <!ENTITY xxe SYSTEM "file:///etc/passwd">
]>
<kml>
  <Placemark>
    <name>&xxe;</name>
  </Placemark>
</kml>
```
**Expected:** Upload blocked with XXE error message

#### 3. Test MIME Spoofing
```bash
# Create PHP file
echo "<?php phpinfo(); ?>" > shell.php

# Rename to KML
mv shell.php malicious.kml

# Try to upload - should be blocked by MIME check
```

#### 4. Test File Size Limits
```bash
# Create 100 MB file
dd if=/dev/zero of=large.kml bs=1M count=100

# Try to upload as premium user - should be blocked
# Try to upload as enterprise user - should succeed
```

### Automated Testing

**PHPUnit Test Example:**
```php
class FileSecurityTest extends WP_UnitTestCase {
    
    public function test_zip_bomb_detection() {
        $file_path = $this->create_zip_bomb();
        $result = Hamnaghsheh_File_Security::check_zip_bomb($file_path);
        
        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('مشکوک', $result['message']);
    }
    
    public function test_xxe_detection() {
        $file_path = $this->create_xxe_kml();
        $result = Hamnaghsheh_File_Security::scan_kml_external_refs($file_path);
        
        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('موجودیت خارجی', $result['message']);
    }
}
```

## 📋 OWASP Compliance Mapping

| OWASP Top 10 | Addressed By | Status |
|--------------|--------------|--------|
| A01: Broken Access Control | User authentication, project permissions | ✅ Implemented |
| A02: Cryptographic Failures | HTTPS, MinIO encryption | ✅ Implemented |
| A03: Injection | Prepared statements, XXE prevention | ✅ Implemented |
| A04: Insecure Design | Multi-layer validation | ✅ Implemented |
| A05: Security Misconfiguration | Secure defaults, documentation | ✅ Implemented |
| A06: Vulnerable Components | Regular updates, dependency scanning | ⚠️ Manual process |
| A07: Auth/Auth Failures | WordPress integration, session management | ✅ Implemented |
| A08: Data Integrity Failures | MIME validation, digital signatures | ⚠️ Partial |
| A09: Security Logging | Error logging, audit trail | ⚠️ Basic implementation |
| A10: SSRF | No external URL fetching | ✅ Not applicable |

## 🌍 GDPR Considerations

### Data Collection
- User IDs and upload history are logged
- File metadata stored in database
- IP addresses may be logged by web server

### User Rights
- **Right to Access:** Users can view their uploaded files
- **Right to Deletion:** Users can delete their files
- **Right to Portability:** Files can be downloaded

### Data Retention
```php
// Implement automatic cleanup (example)
function hamnaghsheh_cleanup_old_files() {
    global $wpdb;
    
    // Delete files older than 2 years for inactive users
    $wpdb->query("
        DELETE f FROM {$wpdb->prefix}hamnaghsheh_files f
        INNER JOIN {$wpdb->prefix}users u ON f.user_id = u.ID
        WHERE f.uploaded_at < DATE_SUB(NOW(), INTERVAL 2 YEAR)
        AND u.user_status = 0
    ");
}
```

## 🔄 Regular Security Maintenance

### Monthly Tasks
- [ ] Review failed upload logs
- [ ] Check storage usage trends
- [ ] Update file size limits if needed
- [ ] Review user activity patterns
- [ ] Check for plugin updates

### Quarterly Tasks
- [ ] Security audit of configuration
- [ ] Review and update MIME type list
- [ ] Test security controls
- [ ] Update documentation
- [ ] Train admins on security procedures

### Annual Tasks
- [ ] Full security assessment
- [ ] Penetration testing
- [ ] Update threat model
- [ ] Review and update policies
- [ ] Disaster recovery testing

## 📞 Security Contacts

**For security issues:**
- **Email:** security@hamnaghsheh.ir
- **Emergency:** [Admin phone number]
- **Reporting:** Use secure channel for vulnerabilities

**Do NOT:**
- Post security issues on public forums
- Share security details in tickets
- Discuss vulnerabilities with users

---

**Last Updated:** 28/12/2025  
**Document Version:** 1.0  
**Plugin Version:** 1.2.0  
**Security Framework:** OWASP Top 10 2021
