<?php
/**
 * Provider Interface
 * 
 * Interface for AI providers (OpenAI, Groq, etc.)
 */

if (!defined('ABSPATH')) {
    exit;
}

interface AIWP_Provider_Interface {
    
    /**
     * Send completion request
     *
     * @param array $messages Messages array
     * @param array $options Provider options
     * @return array|WP_Error Response or error
     */
    public function complete($messages, $options = array());
    
    /**
     * Validate API credentials
     *
     * @return bool|WP_Error True if valid, WP_Error if invalid
     */
    public function validate_credentials();
    
    /**
     * Get provider name
     *
     * @return string Provider name
     */
    public function get_name();
}
