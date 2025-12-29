# Document & Image Viewer Support Documentation

**Version:** 1.3.0  
**Created by:** Soroush Yasini & Arash  
**Date:** 29/12/2025

## 📋 Overview

The Hamnaghseh PM plugin now supports comprehensive document and image viewing capabilities for Enterprise tier users. This feature enables users to view uploaded PDF documents and image files (PNG, JPG, JPEG) directly in their browser with a mobile-optimized, touch-friendly interface.

Enterprise users can now:
- 📄 View PDF documents with page navigation and zoom controls
- 🖼️ View images with zoom, pan, and rotation capabilities
- 📱 Use touch gestures on mobile devices (swipe, pinch-to-zoom)
- 💾 Download files directly from the viewer
- 🎨 Experience a Persian-localized, branded interface

## 📁 Supported File Formats

| Format | Extension | Description | Max Size (Enterprise) | Viewer Support |
|--------|-----------|-------------|----------------------|----------------|
| **PDF** | `.pdf` | Portable Document Format | 500 MB | ✅ Document Viewer |
| **PNG** | `.png` | Portable Network Graphics | 500 MB | ✅ Image Viewer |
| **JPG** | `.jpg` | Joint Photographic Experts Group | 500 MB | ✅ Image Viewer |
| **JPEG** | `.jpeg` | Joint Photographic Experts Group | 500 MB | ✅ Image Viewer |

## 🎯 Subscription Tier Access

### Free Tier
- ❌ No document/image upload capability
- ❌ No viewer access
- ✅ View-only access to shared projects (if shared by Enterprise user)
- ✅ Can activate 14-day trial for full access

### Premium Tier (پرمیوم)
- ❌ No document/image formats supported
- ✅ CAD formats only: DWG, DXF, TXT
- ✅ GIS formats: KML, KMZ, SHP, GPX, GeoJSON

### Enterprise Tier (سازمانی)
- ✅ **Full document/image viewing support**
- ✅ All Premium formats
- ✅ Document formats: **PDF, PNG, JPG, JPEG**
- ✅ 1 GB storage
- ✅ Max file size: 500 MB per file
- ✅ Unlimited shares

**Note:** Document and image viewing is an **Enterprise-exclusive feature** designed for organizations that need to share survey reports, site photos, and documentation with clients and field workers.

## 🖥️ Viewer Integration

### Document Viewer URL Format

The plugin routes document and image files to: `https://hamnaghsheh.ir/document-viewer/`

**URL Parameters:**
- `file` - Full path to the file on MinIO storage
- `type` - File extension (pdf, png, jpg, jpeg)

**Examples:**
```
PDF: https://hamnaghsheh.ir/document-viewer/?file=https://storage.hamnaghsheh.ir/bucket/report.pdf&type=pdf
PNG: https://hamnaghsheh.ir/document-viewer/?file=https://storage.hamnaghsheh.ir/bucket/site-photo.png&type=png
```

### Viewer Button Labels

| Format | Button Label (Persian) |
|--------|----------------------|
| **PDF** | مشاهده PDF |
| **PNG, JPG, JPEG** | مشاهده تصویر |

### Supported Viewers

1. **Document Viewer (PDF.js)** - Handles PDF files
2. **Image Viewer (Viewer.js)** - Handles PNG, JPG, JPEG files
3. **GIS Viewer** - Handles KML, KMZ, GeoJSON, GPX, Shapefile
4. **CAD Viewer** - Handles DWG, DXF
5. **Text Viewer** - Handles TXT

## 📄 PDF Viewer Features

Built with **PDF.js** (Mozilla Foundation), the PDF viewer provides a robust and secure document viewing experience.

### Core Features
- 📖 **Page Navigation** - Previous/Next buttons with page counter
- 🔍 **Zoom Controls** - Zoom in/out with adjustable levels
- 📱 **Touch Gestures** - Swipe left/right for page navigation on mobile
- 💾 **Download Button** - Save PDF to device
- 🎨 **Responsive Design** - Adapts to all screen sizes
- 🇮🇷 **Persian UI** - Fully localized interface
- 🔒 **Secure Rendering** - No external dependencies, sandboxed execution

### Mobile Optimization
- Touch-friendly swipe gestures for page turning
- Pinch-to-zoom support
- Optimized button sizes for touch (minimum 44x44px)
- Responsive toolbar that adapts to screen orientation
- Smooth page transitions

### Technical Details
- **Library:** PDF.js v3.11.174 or later
- **Size:** ~500KB (minified)
- **Browser Support:** All modern browsers (Chrome, Firefox, Safari, Edge)
- **RTL Support:** Full right-to-left layout compatibility

## 🖼️ Image Viewer Features

Built with **Viewer.js**, the image viewer delivers a professional image viewing experience optimized for survey photos and site documentation.

### Core Features
- 🔍 **Zoom & Pan** - Smooth zoom in/out with mouse/touch
- 🔄 **Rotation** - Rotate left/right for proper orientation
- 📱 **Pinch-to-Zoom** - Native pinch gesture support on mobile
- 🖥️ **Full-Screen Mode** - Immersive viewing experience
- 💾 **Download Button** - Save image to device
- 🎨 **Touch-Optimized** - Designed for mobile field workers
- 🇮🇷 **Persian UI** - Localized controls and labels
- 🎯 **Quick Controls** - Toolbar with common actions

### Mobile Optimization
- Pinch-to-zoom with smooth scaling
- Double-tap to zoom in/out
- Swipe to pan around large images
- Touch-optimized toolbar buttons
- Landscape/portrait orientation support
- Minimal UI for maximum viewing area

### Technical Details
- **Library:** Viewer.js v1.11.6 or later
- **Size:** ~50KB (minified)
- **Browser Support:** All modern browsers
- **Touch Support:** Full gesture recognition
- **Image Formats:** PNG, JPG, JPEG (others can be added)

## 📱 Mobile Optimization

The document and image viewers are specifically optimized for field workers using mobile devices:

### Touch Gestures
- **PDF Viewer:**
  - Swipe left → Next page
  - Swipe right → Previous page
  - Pinch to zoom
  - Double-tap to zoom
  
- **Image Viewer:**
  - Pinch to zoom in/out
  - Drag to pan
  - Double-tap to toggle zoom
  - Rotate gestures (device dependent)

### Responsive Design
- Adapts to screen sizes from 320px to 4K displays
- Optimized for portrait and landscape orientations
- Touch-friendly buttons (44x44px minimum)
- Large, clear typography
- Simplified UI on small screens

### Performance
- Lazy loading for large PDFs
- Progressive image rendering
- Minimal bandwidth usage
- Cached resources for repeat views
- Hardware-accelerated rendering

## 🛠️ Installation Guide for Document Viewer Module

The document viewer is hosted as a **separate PHP module** outside the WordPress plugin directory, allowing for independent deployment and updates.

### Directory Structure

```
/public_html/document-viewer/
  ├── index.php              # Router - determines viewer type
  ├── pdf-viewer.php         # PDF display page
  ├── image-viewer.php       # Image display page
  └── assets/
      ├── css/
      │   ├── viewer-theme.css       # Custom Hamnaghseh branding
      │   └── viewer.min.css         # Viewer.js styles
      ├── js/
      │   ├── pdfjs/
      │   │   ├── pdf.min.js         # PDF.js library
      │   │   └── pdf.worker.min.js  # PDF.js web worker
      │   └── viewerjs/
      │       └── viewer.min.js      # Viewer.js library
      └── img/
          └── logo.png               # Hamnaghseh logo
```

### Installation Steps

1. **Create Directory:**
   ```bash
   mkdir -p /public_html/document-viewer/assets/{css,js/pdfjs,js/viewerjs,img}
   ```

2. **Download Libraries:**
   - PDF.js: Download from https://github.com/mozilla/pdf.js/releases
   - Viewer.js: Download from https://github.com/fengyuanchen/viewerjs/releases

3. **Place Files:**
   - Copy PDF.js files to `assets/js/pdfjs/`
   - Copy Viewer.js files to `assets/js/viewerjs/`
   - Copy Viewer.js CSS to `assets/css/`
   - Add custom CSS to `assets/css/viewer-theme.css`

4. **Create Router (index.php):**
   ```php
   <?php
   $file = $_GET['file'] ?? '';
   $type = $_GET['type'] ?? '';
   
   if ($type === 'pdf') {
       require_once 'pdf-viewer.php';
   } elseif (in_array($type, ['png', 'jpg', 'jpeg'])) {
       require_once 'image-viewer.php';
   } else {
       http_response_code(400);
       die('Invalid file type');
   }
   ```

5. **Configure Web Server:**
   - Ensure directory is accessible via HTTPS
   - Set appropriate permissions (755 for directories, 644 for files)
   - Configure CORS headers if needed

### Security Considerations

- **Input Validation:** Validate `file` and `type` parameters
- **URL Whitelisting:** Only allow MinIO storage URLs
- **HTTPS Only:** Force HTTPS for all viewer requests
- **No Direct Execution:** Viewers should not execute uploaded content
- **CSP Headers:** Implement Content Security Policy
- **Rate Limiting:** Protect against abuse

## 📚 Required Libraries

### PDF.js (Mozilla Foundation)

**Version:** 3.11.174 or later  
**License:** Apache License 2.0  
**Download:** https://github.com/mozilla/pdf.js/releases

**Required Files:**
- `pdf.min.js` (~450KB) - Main library
- `pdf.worker.min.js` (~1.5MB) - Web worker for rendering

**CDN Alternative:**
```html
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js"></script>
```

**Why PDF.js?**
- ✅ Open source and well-maintained
- ✅ No server-side dependencies
- ✅ Secure sandboxed rendering
- ✅ Mobile-optimized
- ✅ Supports all modern browsers

### Viewer.js (fengyuanchen)

**Version:** 1.11.6 or later  
**License:** MIT License  
**Download:** https://github.com/fengyuanchen/viewerjs/releases

**Required Files:**
- `viewer.min.js` (~35KB) - Main library
- `viewer.min.css` (~15KB) - Stylesheet

**CDN Alternative:**
```html
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/viewerjs/1.11.6/viewer.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/viewerjs/1.11.6/viewer.min.js"></script>
```

**Why Viewer.js?**
- ✅ Lightweight and fast
- ✅ Touch-optimized
- ✅ No dependencies
- ✅ Easy integration
- ✅ Customizable UI

### Custom Branding CSS

The `viewer-theme.css` file contains Hamnaghseh-specific styling:

```css
/* Hamnaghseh Brand Colors */
:root {
    --hamnaghseh-primary: #09375B;
    --hamnaghseh-accent: #FFCF00;
    --hamnaghseh-text: #1a202c;
}

/* Toolbar styling */
.viewer-toolbar {
    background: var(--hamnaghseh-primary);
}

/* Button styling */
.viewer-button {
    color: var(--hamnaghseh-accent);
}
```

## 👤 User Experience Flow

### Enterprise User Uploading PDF Document

1. User navigates to project page
2. Clicks "آپلود فایل جدید" (Upload New File)
3. Selects PDF file (e.g., `survey-report.pdf`)
4. System validates:
   - ✅ User has Enterprise access
   - ✅ File extension allowed (PDF)
   - ✅ File size within limit (< 500 MB)
   - ✅ MIME type valid (application/pdf)
   - ✅ Storage quota available
5. File uploaded to MinIO
6. Database record created
7. Success message: "✅ فایل با موفقیت آپلود شد"
8. User sees file in list with **"مشاهده PDF"** button

### Viewing PDF Document

1. User clicks **"مشاهده PDF"** button
2. Viewer opens in new browser tab
3. PDF loads with page navigation controls
4. User can:
   - Navigate pages with arrows or swipe
   - Zoom in/out
   - Download file
5. Mobile users can use touch gestures

### Enterprise User Uploading Image

1. User selects image file (e.g., `site-photo.jpg`)
2. System validates file (same checks as PDF)
3. File uploaded successfully
4. User sees **"مشاهده تصویر"** button in file list

### Viewing Image

1. User clicks **"مشاهده تصویر"** button
2. Image viewer opens in new tab
3. Image displays with controls
4. User can:
   - Zoom and pan
   - Rotate image
   - Enter full-screen mode
   - Download image
5. Mobile users can pinch-to-zoom and drag

### Premium User Attempting Document Upload

1. User tries to upload PDF file
2. System checks user tier
3. Validation fails: Extension not allowed for Premium
4. Error message: "⚠️ فرمت PDF فقط برای کاربران سازمانی پشتیبانی می‌شود"
5. User can upgrade to Enterprise tier

## 🔍 Troubleshooting

### Common Issues

#### Issue: "فرمت فایل پشتیبانی نمی‌شود"
**Cause:** User tier does not support document formats  
**Solution:** 
- Upgrade to Enterprise tier
- Check if file extension is correct (pdf, png, jpg, jpeg)

#### Issue: "حجم فایل بیشتر از حد مجاز است"
**Cause:** File exceeds 500 MB limit  
**Solution:** 
- Compress PDF file using online tools
- Reduce image resolution/quality
- Admin can increase limits in `class-file-limits.php`

#### Issue: "نوع فایل نامعتبر است"
**Cause:** MIME type mismatch  
**Solution:** 
- Ensure file is not corrupted
- Re-save file in correct format
- Check file extension matches content

#### Issue: Viewer shows blank page
**Cause:** CORS error or missing library  
**Solution:**
- Check browser console for errors
- Verify PDF.js/Viewer.js files are loaded
- Check CORS headers on MinIO storage
- Verify file URL is accessible

#### Issue: PDF renders slowly on mobile
**Cause:** Large file size or limited bandwidth  
**Solution:**
- Optimize PDF before uploading (reduce images, compress)
- Use WiFi instead of mobile data
- Consider splitting large PDFs into sections

#### Issue: Touch gestures not working
**Cause:** Browser compatibility or JavaScript error  
**Solution:**
- Update to latest browser version
- Clear browser cache
- Check browser console for JavaScript errors
- Ensure touch events are not blocked

### Debug Mode

To enable detailed error logging:

1. Enable WordPress debug mode in `wp-config.php`:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('SCRIPT_DEBUG', true);
```

2. Check logs at: `wp-content/debug.log`

3. Enable browser developer tools:
   - Chrome: F12 → Console tab
   - Firefox: F12 → Console tab
   - Safari: Develop → Show JavaScript Console

4. Check MinIO access logs:
```bash
mc admin trace minio --verbose
```

## 📊 API Reference

### Existing Classes (No New Classes Required)

Document and image viewing uses the existing validation infrastructure:

#### Class: `Hamnaghsheh_File_Validator`

**Purpose:** Validates file uploads including document formats

**Relevant Methods:**

##### `get_allowed_extensions($access_level, $user_id = null)`
Returns allowed file extensions for user tier.

**Enterprise tier includes:** `['pdf', 'png', 'jpg', 'jpeg', ...]`

##### `validate_file_type($filename, $user_id = null)`
Validates file extension against user's allowed formats.

**Returns:** `['valid' => bool, 'message' => string, 'extension' => string]`

**Example:**
```php
$result = Hamnaghsheh_File_Validator::validate_file_type('report.pdf', $user_id);
if ($result['valid']) {
    // Extension allowed for this user
}
```

#### Class: `Hamnaghsheh_File_Security`

**Purpose:** Security validation for all file types

**Relevant Methods:**

##### `validate_mime_type($file_path, $extension)`
Validates MIME type for document/image files.

**Supported MIME types:**
- PDF: `application/pdf`
- PNG: `image/png`
- JPG/JPEG: `image/jpeg`

**Returns:** `['valid' => bool, 'message' => string]`

##### `sanitize_filename($filename)`
Sanitizes filename while preserving Persian characters.

**Example:**
```php
$safe = Hamnaghsheh_File_Security::sanitize_filename('گزارش-نقشه‌برداری.pdf');
// Returns: 'گزارش-نقشه-برداری.pdf'
```

#### Class: `Hamnaghsheh_File_Limits`

**Purpose:** File size limit management

**Relevant Constants:**

```php
const MAX_FILE_SIZE_ENTERPRISE = 524288000; // 500 MB
```

**Methods:**

##### `get_max_size($access_level, $file_ext = null)`
Returns maximum file size for Enterprise users.

**For documents:** 500 MB (default Enterprise limit)

##### `validate_file_size($file_size, $access_level, $file_ext)`
Validates uploaded file size.

**Example:**
```php
$valid = Hamnaghsheh_File_Limits::validate_file_size(
    $file_size, 
    'enterprise', 
    'pdf'
);
```

#### Class: `Hamnaghsheh_Utils`

**Purpose:** Utility functions

**Relevant Methods:**

##### `get_allowed_formats($access_level)`
Returns human-readable format list.

**Enterprise tier:** `'DWG, DXF, TXT, PDF, PNG, JPG'`

### Integration Points

#### Template: `templates/project-show.php`

The viewer integration is in the file list section (lines 222-255):

```php
// Detect file extension
$ext = strtolower(pathinfo($f['file_path'], PATHINFO_EXTENSION));

// Build viewer URL for documents/images
if (in_array($ext, ['pdf', 'png', 'jpg', 'jpeg'])) {
    $doc_url = add_query_arg(
        array('file' => $f['file_path'], 'type' => $ext),
        'https://hamnaghsheh.ir/document-viewer/'
    );
    
    if ($ext === 'pdf') {
        $viewer_url = $doc_url;
        $viewer_label = 'مشاهده PDF';
    } else {
        $viewer_url = $doc_url;
        $viewer_label = 'مشاهده تصویر';
    }
}

// Display viewer button
if ($viewer_url): ?>
    <a target="_blank" href="<?php echo esc_url($viewer_url); ?>" 
       class="bg-slate-800 hover:bg-slate-900 text-white px-3 py-1 rounded-lg">
        <?php echo esc_html($viewer_label); ?>
    </a>
<?php endif; ?>
```

## 🚀 Future Enhancements

Potential features for future versions:

1. **Annotation Support** - Add comments and markup to PDFs
2. **OCR Integration** - Extract text from scanned PDFs
3. **Format Conversion** - Convert between document formats
4. **Thumbnail Generation** - Auto-generate preview thumbnails
5. **Batch Processing** - Process multiple documents at once
6. **Version Comparison** - Compare two PDF versions side-by-side
7. **Digital Signatures** - Sign PDFs digitally
8. **Print Optimization** - Optimize PDFs for printing
9. **Watermarking** - Add watermarks to documents
10. **Metadata Extraction** - Auto-extract document metadata

## 📞 Support

For issues or questions about document and image viewing:

- **Email:** support@hamnaghsheh.ir
- **Documentation:** https://hamnaghsheh.ir/docs/
- **Enterprise Support:** Priority support available for Enterprise tier users
- **Admin Panel:** Contact your system administrator for viewer configuration

### Reporting Issues

When reporting viewer issues, please include:
- Browser and version (e.g., Chrome 120)
- Device type (desktop/mobile)
- File size and format
- Error message (if any)
- Screenshot of the issue
- Browser console errors

---

**Last Updated:** 29/12/2025  
**Document Version:** 1.0  
**Plugin Version:** 1.3.0
