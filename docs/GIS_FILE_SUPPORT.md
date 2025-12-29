# GIS File Format Support Documentation

**Version:** 1.2.0  
**Created by:** Soroush Yasini  
**Date:** 28/12/2025

## 📋 Overview

The Hamnaghseh PM plugin now supports comprehensive GIS (Geographic Information System) file formats in addition to existing CAD formats. This enables survey operations to work with modern mapping and location data.

## 🗺️ Supported File Formats

| Format | Extension | Description | Max Size (Premium) | Max Size (Enterprise) | Viewer Support |
|--------|-----------|-------------|-------------------|---------------------|----------------|
| **KML** | `.kml` | Google Earth Keyhole Markup Language | 100 MB | 100 MB | ✅ GIS Viewer |
| **KMZ** | `.kmz` | Compressed KML (ZIP archive) | 100 MB | 100 MB | ✅ GIS Viewer |
| **Shapefile** | `.shp`, `.shx`, `.dbf`, `.prj` | ESRI Shapefile (multi-file) | 500 MB | 500 MB | ✅ GIS Viewer |
| **Shapefile Optional** | `.cpg`, `.sbn`, `.sbx` | Optional shapefile components | 500 MB | 500 MB | Enterprise only |
| **GPX** | `.gpx` | GPS Exchange Format | 50 MB | 50 MB | ✅ GIS Viewer |
| **GeoJSON** | `.geojson` | GeoJSON geographic data | 100 MB | 100 MB | ✅ GIS Viewer |
| **ZIP** | `.zip` | Archive files (for shapefiles) | 500 MB | 500 MB | N/A |

### Subscription Tier Comparison

#### Free Tier
- ❌ No file upload capability
- ✅ View-only access to shared projects
- ✅ Can activate 14-day trial for full access

#### Premium Tier (پرمیوم)
- ✅ All CAD formats: DWG, DXF, TXT
- ✅ All GIS formats: KML, KMZ, SHP (+ components), GPX, GeoJSON, ZIP
- ✅ 100 MB storage
- ✅ Max file size: 50 MB (default), format-specific overrides apply
- ✅ 100 shares per project

#### Enterprise Tier (سازمانی)
- ✅ All Premium formats
- ✅ Additional optional Shapefile components: CPG, SBN, SBX
- ✅ All document formats: PDF, PNG, JPG, JPEG
- ✅ 1 GB storage
- ✅ Max file size: 500 MB (default), format-specific overrides apply
- ✅ Unlimited shares

## 📦 Shapefile Multi-File Upload

Shapefiles consist of multiple files that work together. The **minimum required files** are:

1. **`.shp`** - Geometry data (REQUIRED)
2. **`.shx`** - Shapefile index (REQUIRED)
3. **`.dbf`** - Attribute data (REQUIRED)
4. **`.prj`** - Projection information (REQUIRED for proper display)

### Optional Shapefile Components

- `.cpg` - Code page for character encoding (Enterprise)
- `.sbn`, `.sbx` - Spatial index files (Enterprise)

### Upload Process

Users can upload shapefile components in two ways:

1. **Individual Files:** Upload each component separately (`.shp`, `.shx`, `.dbf`, `.prj`)
2. **ZIP Archive:** Package all components in a ZIP file and upload as one

**Important:** All shapefile components must have the **same base filename**:
```
✅ CORRECT:
- building.shp
- building.shx
- building.dbf
- building.prj

❌ INCORRECT:
- building.shp
- index.shx
- attributes.dbf
- projection.prj
```

## 🔒 Security Features

The plugin implements **6 layers of security validation** for GIS files:

### Layer 1: User Permission Check
- Validates user subscription tier
- Checks trial status for free users
- Blocks unauthorized upload attempts

### Layer 2: File Extension Validation
- Whitelist-based extension checking
- Tier-specific format restrictions
- Clear error messages in Persian

### Layer 3: File Size Validation
- Per-tier size limits
- Per-format size overrides
- Format-specific maximum sizes enforced

### Layer 4: MIME Type Validation
- Validates actual file content type
- Prevents file extension spoofing
- Comprehensive MIME type database

### Layer 5: Format-Specific Security Checks

#### ZIP/KMZ Files (ZIP Bomb Protection)
- **Compression ratio check:** Max 100:1 ratio
- **Uncompressed size check:** Max 2 GB uncompressed
- Prevents resource exhaustion attacks

#### KML Files (XXE Protection)
- **External entity scan:** Blocks `<!ENTITY` declarations
- **SYSTEM reference check:** Prevents local file access
- Protects against XML External Entity attacks

#### DBF Files (Header Validation)
- **Format verification:** Validates DBF version marker
- **Header integrity:** Checks file structure
- Prevents corrupted/malicious files

### Layer 6: Storage Quota Check
- Validates remaining storage space
- Accounts for all user's projects
- Prevents storage overflow

## ⚙️ Configuration Guide

### Changing File Size Limits

Edit `includes/class-file-limits.php`:

```php
// Tier-based limits
const MAX_FILE_SIZE_PREMIUM = 52428800;     // 50 MB
const MAX_FILE_SIZE_ENTERPRISE = 524288000; // 500 MB

// Format-specific overrides
const FORMAT_LIMITS = [
    'kml' => 104857600,      // 100 MB
    'kmz' => 104857600,      // 100 MB
    'shp' => 524288000,      // 500 MB
    'zip' => 524288000,      // 500 MB
    'gpx' => 52428800,       // 50 MB
    'geojson' => 104857600,  // 100 MB
];
```

**Common size conversions:**
- 10 MB = 10485760 bytes
- 50 MB = 52428800 bytes
- 100 MB = 104857600 bytes
- 200 MB = 209715200 bytes
- 500 MB = 524288000 bytes
- 1 GB = 1073741824 bytes
- 2 GB = 2147483648 bytes

### Modifying Security Thresholds

Edit `includes/class-file-security.php`:

```php
// ZIP bomb protection
const MAX_COMPRESSION_RATIO = 100;     // 100:1 ratio
const MAX_UNCOMPRESSED_SIZE = 2147483648; // 2 GB
```

### Adding New GIS Formats

To add a new format:

1. **Update File Validator** (`class-file-validator.php`):
```php
'premium' => [
    'dwg', 'dxf', 'txt',
    'kml', 'kmz', 'shp', 'shx', 'dbf', 'prj', 'gpx', 'geojson', 'zip',
    'newformat' // Add here
],
```

2. **Add MIME Types** (`class-file-security.php`):
```php
const MIME_TYPES = [
    'newformat' => ['application/newformat', 'application/octet-stream'],
];
```

3. **Update Viewer** (`templates/project-show.php`):
```php
elseif ($ext === 'newformat') {
    $viewer_url = 'https://hamnaghsheh.ir/newformat-viewer/?file=' . $f['file_path'];
    $viewer_label = 'مشاهده فرمت جدید';
}
```

4. **Update Utils** (`class-utils.php`):
```php
'formats' => ['dwg', 'dxf', 'txt', 'kml', 'kmz', 'shp', 'gpx', 'geojson', 'zip', 'newformat'],
'formats_label' => 'DWG, DXF, TXT, KML, KMZ, SHP, GPX, GeoJSON, ZIP, NEWFORMAT',
```

## 🖥️ Viewer Integration

### GIS Viewer URL Format

The plugin routes GIS files to: `https://hamnaghsheh.ir/gis-viewer/`

**URL Parameters:**
- `file` - Full path to the file on MinIO storage
- `type` - File extension (kml, kmz, geojson, gpx, shp)

**Example:**
```
https://hamnaghsheh.ir/gis-viewer/?file=https://storage.hamnaghsheh.ir/bucket/file.kml&type=kml
```

### Viewer Button Labels

| Format | Button Label (Persian) |
|--------|----------------------|
| KML, KMZ, GeoJSON, GPX | مشاهده نقشه |
| Shapefile (SHP) | مشاهده Shapefile |
| DWG, DXF | مشاهده CAD |
| TXT | مشاهده متن |

### Supported Viewers

1. **GIS Viewer** - Handles KML, KMZ, GeoJSON, GPX, Shapefile
2. **CAD Viewer** - Handles DWG, DXF
3. **Text Viewer** - Handles TXT

## 💾 Database Storage

Files are stored in the `wp_hamnaghsheh_files` table:

```sql
CREATE TABLE wp_hamnaghsheh_files (
    id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id BIGINT(20) UNSIGNED NOT NULL,
    user_id BIGINT(20) UNSIGNED NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    key_file VARCHAR(500) NOT NULL,
    file_path TEXT NOT NULL,
    file_size BIGINT(20) UNSIGNED NOT NULL,
    file_type VARCHAR(100),
    uploaded_at DATETIME NOT NULL,
    INDEX idx_project (project_id),
    INDEX idx_user (user_id)
);
```

**Key Fields:**
- `file_name` - Original filename (sanitized, Persian-safe)
- `key_file` - MinIO object key
- `file_path` - Full URL to file on MinIO
- `file_size` - Size in bytes
- `file_type` - MIME type

## 👤 User Experience Flows

### Premium User Uploading GIS File

1. User navigates to project page
2. Clicks "آپلود فایل جدید" (Upload New File)
3. Selects GIS file (e.g., `survey.kml`)
4. System validates:
   - ✅ User has premium access
   - ✅ File extension allowed (KML)
   - ✅ File size within limit (< 100 MB)
   - ✅ MIME type valid (application/vnd.google-earth.kml+xml)
   - ✅ No XXE vulnerabilities detected
   - ✅ Storage quota available
5. File uploaded to MinIO
6. Database record created
7. Success message: "✅ فایل با موفقیت آپلود شد"
8. User sees file in list with "مشاهده نقشه" button

### Enterprise User Uploading Shapefile

1. User selects multiple files: `road.shp`, `road.shx`, `road.dbf`, `road.prj`
2. Uploads each file individually **OR** uploads as ZIP
3. System validates each component
4. All files stored separately
5. Viewer combines components for display

### Free User Attempting Upload

1. User clicks upload button
2. System checks user tier
3. Shows message: "⚠️ کاربران رایگان امکان آپلود فایل ندارند. برای آپلود فایل، دوره آزمایشی 14 روزه را فعال کنید یا اشتراک تهیه نمایید."
4. User can activate trial or upgrade

## 🔍 Troubleshooting

### Common Issues

#### Issue: "نوع فایل نامعتبر است"
**Cause:** MIME type mismatch  
**Solution:** Ensure file is not corrupted and has correct extension

#### Issue: "حجم فایل بیشتر از حد مجاز است"
**Cause:** File exceeds size limit  
**Solution:** 
- Compress the file if possible
- Upgrade to Enterprise for larger limits
- Admin can increase limits in `class-file-limits.php`

#### Issue: "نسبت فشرده‌سازی فایل مشکوک است"
**Cause:** ZIP bomb detected  
**Solution:** File has suspicious compression ratio (>100:1). This is a security measure. Re-export the file or contact support.

#### Issue: "فایل KML حاوی تعریف موجودیت خارجی است"
**Cause:** XXE vulnerability detected in KML  
**Solution:** Remove external entity declarations from KML file. Clean the XML.

#### Issue: "فضای ذخیره‌سازی کافی نیست"
**Cause:** Storage quota exceeded  
**Solution:**
- Delete old files
- Upgrade to higher tier
- Contact admin to increase quota

### Debug Mode

To enable detailed error logging:

1. Enable WordPress debug mode in `wp-config.php`:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

2. Check logs at: `wp-content/debug.log`

## 📊 API Reference

### Class: `Hamnaghsheh_File_Limits`

**Purpose:** Centralized file size limit configuration

**Methods:**

#### `get_max_size($access_level, $file_ext = null)`
Returns maximum file size in bytes for user's access level and file type.

**Parameters:**
- `$access_level` (string) - User tier: 'free', 'premium', 'enterprise'
- `$file_ext` (string|null) - File extension for format-specific limit

**Returns:** (int) Maximum size in bytes

**Example:**
```php
$max_size = Hamnaghsheh_File_Limits::get_max_size('premium', 'kml');
// Returns: 104857600 (100 MB)
```

#### `get_max_size_human($access_level, $file_ext = null)`
Returns human-readable size limit.

**Returns:** (string) e.g., "100 MB"

### Class: `Hamnaghsheh_File_Security`

**Purpose:** Security validation for uploaded files

**Methods:**

#### `sanitize_filename($filename)`
Sanitizes filename while preserving Persian characters.

**Parameters:**
- `$filename` (string) - Original filename

**Returns:** (string) Sanitized filename

**Example:**
```php
$safe_name = Hamnaghsheh_File_Security::sanitize_filename('نقشه شهری-2024.kml');
// Returns: 'نقشه-شهری-2024.kml'
```

#### `validate_mime_type($file_path, $extension)`
Validates file MIME type against allowed types.

**Returns:** array `['valid' => bool, 'message' => string]`

#### `check_zip_bomb($file_path)`
Detects ZIP bomb attacks.

**Returns:** array `['valid' => bool, 'message' => string]`

#### `scan_kml_external_refs($file_path)`
Scans KML for XXE vulnerabilities.

**Returns:** array `['valid' => bool, 'message' => string]`

#### `validate_dbf_header($file_path)`
Validates DBF file header structure.

**Returns:** array `['valid' => bool, 'message' => string]`

#### `validate_file_size($file_size, $access_level, $file_ext)`
Validates file size against limits.

**Returns:** array `['valid' => bool, 'message' => string]`

### Class: `Hamnaghsheh_File_Validator`

**Purpose:** Comprehensive file validation orchestration

**Methods:**

#### `validate_file_comprehensive($file, $user_id = null)`
Runs all validation checks on uploaded file.

**Parameters:**
- `$file` (array) - $_FILES array element
- `$user_id` (int|null) - User ID (defaults to current user)

**Returns:** array `['valid' => bool, 'message' => string]`

**Example:**
```php
$validation = Hamnaghsheh_File_Validator::validate_file_comprehensive($_FILES['file'], $user_id);
if (!$validation['valid']) {
    echo $validation['message']; // Persian error message
}
```

## 🚀 Future Enhancements

Potential features for future versions:

1. **Batch Upload** - Upload multiple files at once
2. **Format Conversion** - Convert between GIS formats
3. **Metadata Extraction** - Auto-extract and display file metadata
4. **Preview Thumbnails** - Generate map thumbnails for quick preview
5. **Coordinate System Detection** - Auto-detect and display CRS info
6. **Validation Reports** - Detailed validation reports for files
7. **WebGIS Integration** - Embedded map viewer in project page
8. **Real-time Collaboration** - Multiple users editing same GIS data

## 📞 Support

For issues or questions:
- **Email:** support@hamnaghsheh.ir
- **Documentation:** https://hamnaghsheh.ir/docs/
- **Admin Panel:** Contact your system administrator

---

**Last Updated:** 28/12/2025  
**Document Version:** 1.0  
**Plugin Version:** 1.2.0
