<?php
/**
 * Interface for LLM Providers.
 */

if (!defined('ABSPATH')) {
    exit;
}

interface AIWP_Provider_Interface
{
    /**
     * Process a request and return a structured response.
     *
     * @param string $message The user's prompt.
     * @param string $context The captured page context.
     * @param array $settings The provider configuration.
     * @return array { reply: string, action: string, data: object }
     */
    public function process($message, $context, $settings);
}
