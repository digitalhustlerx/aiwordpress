<?php
/**
 * Error Handler
 * 
 * Handles errors with rate limiting and user-friendly messages
 */

if (!defined('ABSPATH')) {
    exit;
}

class AIWP_Error_Handler {
    
    /**
     * Rate limit: 100 requests per hour
     */
    const RATE_LIMIT = 100;
    const RATE_LIMIT_WINDOW = 3600; // 1 hour in seconds
    
    /**
     * Check rate limit
     */
    public static function check_rate_limit($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        $transient_key = 'aiwp_rate_limit_' . $user_id;
        $requests = get_transient($transient_key);
        
        if ($requests === false) {
            // First request in this window
            set_transient($transient_key, 1, self::RATE_LIMIT_WINDOW);
            return true;
        }
        
        if ($requests >= self::RATE_LIMIT) {
            return false;
        }
        
        // Increment counter
        set_transient($transient_key, $requests + 1, self::RATE_LIMIT_WINDOW);
        return true;
    }
    
    /**
     * Get user-friendly error message
     */
    public static function get_user_message($error) {
        if (!is_wp_error($error)) {
            return 'An unknown error occurred';
        }
        
        $error_code = $error->get_error_code();
        $error_message = $error->get_error_message();
        
        switch ($error_code) {
            case 'invalid_api_key':
            case 'no_api_key':
                return '🔑 API Key Error: ' . $error_message . ' Please check your settings.';
            
            case 'rate_limit':
                return '⏱️ Rate Limit: You\'ve hit the API rate limit. Please try again in a few moments.';
            
            case 'rate_limit_exceeded':
                return '⏱️ Too Many Requests: You\'ve exceeded 100 requests per hour. Please try again later.';
            
            case 'timeout':
                return '⏰ Timeout: The request took too long. Please try a shorter prompt or try again.';
            
            case 'connection_error':
                return '❌ Connection Error: ' . $error_message . ' Please check your internet connection.';
            
            case 'invalid_request':
                return '⚠️ Invalid Request: ' . $error_message;
            
            default:
                return '❌ Error: ' . $error_message;
        }
    }
    
    /**
     * Log error
     */
    public static function log_error($error, $context = array()) {
        if (!get_option('aiwp_debug_mode', false)) {
            return;
        }
        
        $log_message = sprintf(
            '[AIWP Error] Code: %s | Message: %s | Context: %s',
            is_wp_error($error) ? $error->get_error_code() : 'unknown',
            is_wp_error($error) ? $error->get_error_message() : $error,
            json_encode($context)
        );
        
        error_log($log_message);
    }
    
    /**
     * Format error response for API
     */
    public static function format_api_error($error) {
        return array(
            'success' => false,
            'error' => array(
                'code' => is_wp_error($error) ? $error->get_error_code() : 'unknown',
                'message' => self::get_user_message($error)
            )
        );
    }
}
