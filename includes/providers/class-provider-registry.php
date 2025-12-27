<?php
/**
 * Factory class for LLM Providers.
 */

if (!defined('ABSPATH')) {
    exit;
}

class AIWP_Provider_Registry
{

    /**
     * Get the active provider instance.
     *
     * @return AIWP_Provider_Interface|null
     */
    public static function get_active_provider()
    {
        $provider_type = get_option('aiwp_provider', 'openai');

        // Allow external developers to register custom providers.
        $providers = apply_filters('aiwp_register_providers', [
            'gemini' => 'AIWP_Gemini_Provider',
            'openai' => 'AIWP_OpenAI_Provider',
            'custom' => 'AIWP_OpenAI_Provider', // Custom uses OpenAI implementation with different base URL
        ]);

        if (isset($providers[$provider_type])) {
            $class_name = $providers[$provider_type];

            // Load core providers if they are selected
            if ($provider_type === 'gemini') {
                require_once AIWP_COPILOT_DIR . 'includes/providers/class-gemini-provider.php';
            } elseif ($provider_type === 'openai' || $provider_type === 'custom') {
                require_once AIWP_COPILOT_DIR . 'includes/providers/class-openai-provider.php';
            }

            if (class_exists($class_name)) {
                return new $class_name();
            }
        }

        // Fallback to OpenAI
        require_once AIWP_COPILOT_DIR . 'includes/providers/class-openai-provider.php';
        return new AIWP_OpenAI_Provider();
    }

    /**
     * Get provider settings.
     *
     * @return array
     */
    public static function get_settings()
    {
        return [
            'api_key' => get_option('aiwp_api_key'),
            'model' => get_option('aiwp_model'),
            'base_url' => get_option('aiwp_base_url'),
        ];
    }
}
