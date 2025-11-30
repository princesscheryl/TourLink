<?php
/**
 * Hosted Upload Service Class
 * Handles file uploads to the hosted uploads server
 */
class HostedUpload {
    // Base URL for the hosted uploads service
    private static $upload_service_url = 'http://169.239.251.102:442/~princess.donkor/upload.php';
    
    // Base URL for accessing uploaded files
    private static $uploads_base_url = 'http://169.239.251.102:442/~princess.donkor/uploads/';
    
    /**
     * Upload a file to the hosted uploads service
     * @param string $file_path - Path to the temporary uploaded file
     * @param string $original_filename - Original filename
     * @return array - ['success' => bool, 'url' => string|null, 'message' => string]
     */
    public static function uploadFile($file_path, $original_filename) {
        if (!file_exists($file_path)) {
            return [
                'success' => false,
                'url' => null,
                'message' => 'File does not exist'
            ];
        }
        
        // Generate unique filename to avoid conflicts
        $file_extension = strtolower(pathinfo($original_filename, PATHINFO_EXTENSION));
        $unique_filename = uniqid('file_', true) . '_' . time() . '.' . $file_extension;
        
        // Prepare CURL request
        $ch = curl_init();
        
        // Create CURLFile object
        $cfile = new CURLFile($file_path, mime_content_type($file_path), $unique_filename);
        
        $post_data = [
            'uploadedFile' => $cfile
        ];
        
        curl_setopt_array($ch, [
            CURLOPT_URL => self::$upload_service_url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $post_data,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ]);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);
        
        // Check for CURL errors
        if (!empty($curl_error)) {
            return [
                'success' => false,
                'url' => null,
                'message' => 'Upload failed: ' . $curl_error
            ];
        }
        
        // Check HTTP response code
        if ($http_code !== 200) {
            return [
                'success' => false,
                'url' => null,
                'message' => 'Upload failed: HTTP ' . $http_code
            ];
        }
        
        // Parse HTML response to check for success
        // The upload.php returns HTML with success/error messages
        if (stripos($response, 'uploaded successfully') !== false || 
            stripos($response, 'class=\'success\'') !== false) {
            
            // Construct the file URL
            $file_url = self::$uploads_base_url . $unique_filename;
            
            return [
                'success' => true,
                'url' => $file_url,
                'message' => 'File uploaded successfully'
            ];
        } else {
            // Try to extract error message from HTML
            $error_message = 'Upload failed';
            if (preg_match('/class=[\'"]error[\'"]>([^<]+)/i', $response, $matches)) {
                $error_message = trim($matches[1]);
            } elseif (stripos($response, 'already exists') !== false) {
                // If file exists, try with a different unique name
                $new_unique_filename = uniqid('file_', true) . '_' . time() . '_' . rand(1000, 9999) . '.' . $file_extension;
                // Retry with new filename (but keep original filename for the URL construction)
                $retry_result = self::uploadFile($file_path, $new_unique_filename);
                if ($retry_result['success']) {
                    return $retry_result;
                }
            }
            
            return [
                'success' => false,
                'url' => null,
                'message' => $error_message
            ];
        }
    }
    
    /**
     * Get the image URL - handles both local paths and hosted URLs
     * @param string $image_path - Path from database (could be local path or full URL)
     * @param string $base_path - Base path for local files (default: '../')
     * @return string - Full URL or local path for image
     */
    public static function getImageUrl($image_path, $base_path = '../') {
        if (empty($image_path)) {
            return null;
        }
        
        // If it's already a full URL (starts with http:// or https://), return as-is
        if (preg_match('/^https?:\/\//i', $image_path)) {
            return $image_path;
        }
        
        // If it's a local path (starts with 'uploads/'), return with base path
        // This maintains backward compatibility with existing local images
        if (strpos($image_path, 'uploads/') === 0) {
            return $base_path . $image_path;
        }
        
        // Default: assume it's a local path
        return $base_path . $image_path;
    }
    
    /**
     * Check if an image path is a hosted URL
     * @param string $image_path
     * @return bool
     */
    public static function isHostedUrl($image_path) {
        return !empty($image_path) && preg_match('/^https?:\/\//i', $image_path);
    }
}

