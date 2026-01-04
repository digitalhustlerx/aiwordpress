<?php
/**
 * Provider Registry
 * 
 * Manages registered AI providers
 */

if (!defined('ABSPATH')) {
    exit;
}

class AIWP_Provider_Registry
{

    /**
     * Registered providers
     */
    private static $providers = array();

    /**
     * Initialize registry
     */
    public static function init()
    {
        self::register_default_providers();
    }

    /**
     * Register default providers
     */
    private static function register_default_providers()
    {
        self::register('openai', new AIWP_OpenAI_Provider());
        self::register('groq', new AIWP_Groq_Provider());
        self::register('openrouter', new AIWP_OpenRouter_Provider());
        self::register('gemini', new AIWP_Gemini_Provider());
    }

    /**
     * Register a provider
     */
    public static function register($id, $provider)
    {
        if (!$provider instanceof AIWP_Provider_Interface) {
            return false;
        }

        self::$providers[$id] = $provider;
        return true;
    }

    /**
     * Get a provider
     */
    public static function get($id)
    {
        return isset(self::$providers[$id]) ? self::$providers[$id] : null;
    }

    /**
     * Get active provider
     */
    public static function get_active()
    {
        $provider_id = get_option('aiwp_active_provider', 'groq');
        return self::get($provider_id);
    }

    /**
     * Get all providers
     */
    public static function get_all()
    {
        return self::$providers;
    }

    /**
     * Get all provider metadata
     */
    public static function get_all_metadata()
    {
        return array(
            AIWP_Groq_Provider::get_metadata(),
            AIWP_OpenRouter_Provider::get_metadata(),
            AIWP_Gemini_Provider::get_metadata(),
            AIWP_OpenAI_Provider::get_metadata()
        );
    }

    /**
     * Get provider metadata by ID
     */
    public static function get_metadata($id)
    {
        $all_metadata = self::get_all_metadata();

        foreach ($all_metadata as $metadata) {
            if ($metadata['id'] === $id) {
                return $metadata;
            }
        }

        return null;
    }
}
