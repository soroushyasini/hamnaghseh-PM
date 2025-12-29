<?php
if (!defined('ABSPATH'))
    exit;

/**
 * File Security Validation Class
 * Created by soroush - 28/12/2025
 * 
 * Provides comprehensive security validation for uploaded files
 */
class Hamnaghsheh_File_Security 
{
    // ZIP bomb thresholds
    const MAX_COMPRESSION_RATIO = 100; // 100:1 compression ratio
    const MAX_UNCOMPRESSED_SIZE = 2147483648; // 2 GB
    
    // MIME type mappings for validation
    const MIME_TYPES = [
        // GIS formats
        'kml' => ['application/vnd.google-earth.kml+xml', 'application/xml', 'text/xml'],
        'kmz' => ['application/vnd.google-earth.kmz', 'application/zip'],
        'shp' => ['application/x-esri-shape', 'application/octet-stream'],
        'shx' => ['application/x-esri-shape', 'application/octet-stream'],
        'dbf' => ['application/x-dbf', 'application/dbase', 'application/octet-stream'],
        'prj' => ['text/plain', 'application/octet-stream'],
        'cpg' => ['text/plain', 'application/octet-stream'],
        'sbn' => ['application/octet-stream'],
        'sbx' => ['application/octet-stream'],
        'gpx' => ['application/gpx+xml', 'application/xml', 'text/xml'],
        'geojson' => ['application/geo+json', 'application/json', 'text/plain'],
        'zip' => ['application/zip', 'application/x-zip-compressed'],
        
        // CAD formats
        'dwg' => ['application/acad', 'application/x-acad', 'application/dwg', 'image/vnd.dwg', 'application/octet-stream'],
        'dxf' => ['application/dxf', 'application/x-dxf', 'text/plain', 'application/octet-stream'],
        
        // Documents
        'txt' => ['text/plain'],
        // PDF: Multiple MIME types to support different PDF generators and server configurations
        'pdf' => ['application/pdf', 'application/x-pdf', 'application/acrobat', 'application/vnd.pdf', 'text/pdf', 'text/x-pdf', 'application/octet-stream'],
        // FIXED - Multiple MIME types ✅
        'png' => ['image/png', 'image/x-png', 'image/vnd.mozilla.apng', 'application/octet-stream'],
        'jpg' => ['image/jpeg', 'image/pjpeg', 'image/jpg', 'application/octet-stream'],
        'jpeg' => ['image/jpeg', 'image/pjpeg', 'image/jpg', 'application/octet-stream'],
    ];
    
    /**
     * Sanitize filename - preserve Persian characters but remove dangerous ones
     * 
     * @param string $filename Original filename
     * @return string Sanitized filename
     */
    public static function sanitize_filename($filename)
    {
        // Get the file extension
        $path_info = pathinfo($filename);
        $extension = isset($path_info['extension']) ? $path_info['extension'] : '';
        $basename = $path_info['filename'];
        
        // Remove dangerous characters but preserve Persian
        $basename = preg_replace('/[^\p{L}\p{N}\s\-_]/u', '', $basename);
        
        // Remove multiple spaces
        $basename = preg_replace('/\s+/', '-', $basename);
        
        // Consolidate multiple dashes and underscores
        $basename = preg_replace('/[-_]+/', '-', $basename);
        
        // Trim dashes from edges
        $basename = trim($basename, '-_.');
        
        // If basename is empty after sanitization, use timestamp
        if (empty($basename)) {
            $basename = 'file-' . time();
        }
        
        // Reconstruct filename
        return $extension ? $basename . '.' . $extension : $basename;
    }
    
    /**
     * Validate MIME type against allowed types for extension
     * 
     * @param string $file_path Path to uploaded file
     * @param string $extension File extension
     * @return array ['valid' => bool, 'message' => string]
     */
    public static function validate_mime_type($file_path, $extension)
    {
        $extension = strtolower($extension);
        
        // Check if we have MIME types defined for this extension
        if (!isset(self::MIME_TYPES[$extension])) {
            return [
                'valid' => true, // Allow if no specific MIME check defined
                'message' => ''
            ];
        }
        
        // Get actual MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo === false) {
            return [
                'valid' => false,
                'message' => '⚠️ خطا در بررسی نوع فایل. لطفاً با مدیر تماس بگیرید.'
            ];
        }
        
        $mime = finfo_file($finfo, $file_path);
        finfo_close($finfo);
        
        if ($mime === false) {
            return [
                'valid' => false,
                'message' => '⚠️ خطا در تشخیص نوع فایل.'
            ];
        }
        
        $allowed_mimes = self::MIME_TYPES[$extension];
        
        if (!in_array($mime, $allowed_mimes)) {
            return [
                'valid' => false,
                'message' => sprintf(
                    '⚠️ نوع فایل نامعتبر است. فایل %s باید از نوع %s باشد، اما نوع %s تشخیص داده شد.',
                    strtoupper($extension),
                    implode(' یا ', $allowed_mimes),
                    $mime
                )
            ];
        }
        
        return [
            'valid' => true,
            'message' => ''
        ];
    }
    
    /**
     * Check for ZIP bomb (compression bomb attack)
     * 
     * @param string $file_path Path to ZIP file
     * @return array ['valid' => bool, 'message' => string]
     */
    public static function check_zip_bomb($file_path)
    {
        if (!class_exists('ZipArchive')) {
            // If ZipArchive not available, skip check
            return ['valid' => true, 'message' => ''];
        }
        
        $zip = new ZipArchive();
        if ($zip->open($file_path) !== true) {
            return [
                'valid' => false,
                'message' => '⚠️ فایل ZIP معتبر نیست یا آسیب دیده است.'
            ];
        }
        
        $compressed_size = filesize($file_path);
        $uncompressed_size = 0;
        
        // Calculate total uncompressed size
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $stat = $zip->statIndex($i);
            $uncompressed_size += $stat['size'];
        }
        
        $zip->close();
        
        // Check uncompressed size limit
        if ($uncompressed_size > self::MAX_UNCOMPRESSED_SIZE) {
            return [
                'valid' => false,
                'message' => sprintf(
                    '⚠️ فایل ZIP بیش از حد بزرگ است. حداکثر اندازه مجاز بعد از استخراج: %s',
                    size_format(self::MAX_UNCOMPRESSED_SIZE)
                )
            ];
        }
        
        // Check compression ratio
        if ($compressed_size > 0) {
            $ratio = $uncompressed_size / $compressed_size;
            if ($ratio > self::MAX_COMPRESSION_RATIO) {
                return [
                    'valid' => false,
                    'message' => sprintf(
                        '⚠️ نسبت فشرده‌سازی فایل مشکوک است (%.1f:1). این ممکن است یک ZIP bomb باشد.',
                        $ratio
                    )
                ];
            }
        }
        
        return ['valid' => true, 'message' => ''];
    }
    
    /**
     * Scan KML file for external references (XXE attack prevention)
     * 
     * @param string $file_path Path to KML file
     * @return array ['valid' => bool, 'message' => string]
     */
    public static function scan_kml_external_refs($file_path)
    {
        $content = file_get_contents($file_path, false, null, 0, 8192); // Read first 8KB
        
        if ($content === false) {
            return [
                'valid' => false,
                'message' => '⚠️ خطا در خواندن فایل KML.'
            ];
        }
        
        if (empty($content)) {
            return [
                'valid' => false,
                'message' => '⚠️ فایل KML خالی است.'
            ];
        }
        
        // Check for external entity declarations (XXE)
        if (preg_match('/<!ENTITY/i', $content)) {
            return [
                'valid' => false,
                'message' => '⚠️ فایل KML حاوی تعریف موجودیت خارجی است که مجاز نیست (امنیت XXE).'
            ];
        }
        
        // Check for SYSTEM declarations
        if (preg_match('/SYSTEM\s+["\'](?!http)/i', $content)) {
            return [
                'valid' => false,
                'message' => '⚠️ فایل KML حاوی مرجع SYSTEM محلی است که مجاز نیست.'
            ];
        }
        
        return ['valid' => true, 'message' => ''];
    }
    
    /**
     * Validate DBF file header (for shapefiles)
     * 
     * @param string $file_path Path to DBF file
     * @return array ['valid' => bool, 'message' => string]
     */
    public static function validate_dbf_header($file_path)
    {
        $handle = fopen($file_path, 'rb');
        if (!$handle) {
            return [
                'valid' => false,
                'message' => '⚠️ خطا در خواندن فایل DBF.'
            ];
        }
        
        // Read first byte - should be DBF version marker
        $data = fread($handle, 1);
        $version = $data !== false ? ord($data) : 0;
        fclose($handle);
        
        if ($data === false) {
            return [
                'valid' => false,
                'message' => '⚠️ خطا در خواندن هدر فایل DBF.'
            ];
        }
        
        // Valid DBF version markers: 0x02, 0x03, 0x04, 0x05, 0x30, 0x31, 0x83, 0x8B, 0x8E, 0xF5
        $valid_versions = [0x02, 0x03, 0x04, 0x05, 0x30, 0x31, 0x83, 0x8B, 0x8E, 0xF5];
        
        if (!in_array($version, $valid_versions)) {
            return [
                'valid' => false,
                'message' => '⚠️ فایل DBF معتبر نیست. هدر فایل اشتباه است.'
            ];
        }
        
        return ['valid' => true, 'message' => ''];
    }
    
    /**
     * Validate file size against limits
     * 
     * @param int $file_size File size in bytes
     * @param string $access_level User access level
     * @param string $file_ext File extension
     * @return array ['valid' => bool, 'message' => string]
     */
    public static function validate_file_size($file_size, $access_level, $file_ext)
    {
        $max_size = Hamnaghsheh_File_Limits::get_max_size($access_level, $file_ext);
        
        if ($max_size === 0) {
            return [
                'valid' => false,
                'message' => '⚠️ کاربران رایگان امکان آپلود فایل ندارند.'
            ];
        }
        
        if ($file_size > $max_size) {
            return [
                'valid' => false,
                'message' => sprintf(
                    '⚠️ حجم فایل (%s) بیشتر از حد مجاز است. حداکثر مجاز: %s',
                    size_format($file_size),
                    size_format($max_size)
                )
            ];
        }
        
        return ['valid' => true, 'message' => ''];
    }
}
