<?php
if (!defined('ABSPATH'))
    exit;

/**
 * File Size Limits Configuration
 * Created by soroush - 28/12/2025
 * 
 * ⚙️ ADMIN: Change these values as needed for your requirements
 */
class Hamnaghsheh_File_Limits 
{
    // Tier-based limits (in bytes)
    const MAX_FILE_SIZE_FREE = 0;  // No upload for free users
    
    const MAX_FILE_SIZE_PREMIUM = 52428800;  // 50 MB
    // To change to 100 MB: 104857600
    // To change to 200 MB: 209715200
    
    const MAX_FILE_SIZE_ENTERPRISE = 524288000;  // 500 MB
    // To change to 1 GB: 1073741824
    // To change to 2 GB: 2147483648
    
    // Per-format limits (optional - overrides tier limits for specific formats)
    const FORMAT_LIMITS = [
        // GIS formats
        'kml' => 104857600,      // 100 MB - can be large with embedded images
        'kmz' => 104857600,      // 100 MB - compressed KML
        'shp' => 524288000,      // 500 MB - shapefiles can be very large
        'zip' => 524288000,      // 500 MB - archive files
        'gpx' => 52428800,       // 50 MB - GPS tracks
        'geojson' => 104857600,  // 100 MB - GeoJSON files
        
        // CAD formats
        'dwg' => 104857600,      // 100 MB
        'dxf' => 104857600,      // 100 MB
        
        // Documents
        'pdf' => 52428800,       // 50 MB
        'txt' => 10485760,       // 10 MB
    ];
    
    /**
     * Get maximum file size for user's access level and file type
     * 
     * @param string $access_level User's subscription tier
     * @param string|null $file_ext File extension to check format-specific limit
     * @return int Maximum file size in bytes
     */
    public static function get_max_size($access_level, $file_ext = null) 
    {
        // Check format-specific limit first
        if ($file_ext && isset(self::FORMAT_LIMITS[$file_ext])) {
            return self::FORMAT_LIMITS[$file_ext];
        }
        
        // Otherwise use tier limit
        switch ($access_level) {
            case 'free':
                return self::MAX_FILE_SIZE_FREE;
            case 'premium':
                return self::MAX_FILE_SIZE_PREMIUM;
            case 'enterprise':
                return self::MAX_FILE_SIZE_ENTERPRISE;
            default:
                return 0;
        }
    }
    
    /**
     * Get human-readable size limit
     * 
     * @param string $access_level User's subscription tier
     * @param string|null $file_ext File extension
     * @return string Human-readable size (e.g., "50 MB")
     */
    public static function get_max_size_human($access_level, $file_ext = null)
    {
        $bytes = self::get_max_size($access_level, $file_ext);
        return size_format($bytes);
    }
}
