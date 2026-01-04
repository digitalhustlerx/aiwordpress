<?php
/**
 * Gemini Provider
 * 
 * Google Gemini API integration
 */

if (!defined('ABSPATH')) {
    exit;
}

class AIWP_Gemini_Provider implements AIWP_Provider_Interface
{

    /**
     * API endpoint
     */
    private $endpoint = 'https://generativelanguage.googleapis.com/v1beta';

    /**
     * API key
     */
    private $api_key;

    /**
     * Model
     */
    private $model;

    /**
     * Constructor
     */
    public function __construct()
    {
        $providers = get_option('aiwp_providers', array());
        $this->api_key = isset($providers['gemini']['api_key']) ? $providers['gemini']['api_key'] : '';
        $this->model = isset($providers['gemini']['model']) ? $providers['gemini']['model'] : 'gemini-1.5-flash';
    }

    /**
     * Send completion request
     */
    public function complete($messages, $options = array())
    {
        if (empty($this->api_key)) {
            return new WP_Error('no_api_key', 'Gemini API key not configured');
        }

        // Inject specialist context
        $specialist_id = get_option('aiwp_specialist', 'default');
        $messages = AIWP_Specialist_Engine::inject_specialist_context($messages, $specialist_id);

        // Convert OpenAI format to Gemini format
        $gemini_messages = $this->convert_messages($messages);

        // Prepare request
        $model = isset($options['model']) ? $options['model'] : $this->model;
        $body = array(
            'contents' => $gemini_messages,
            'generationConfig' => array(
                'temperature' => isset($options['temperature']) ? $options['temperature'] : 0.7,
                'maxOutputTokens' => isset($options['max_tokens']) ? $options['max_tokens'] : 2000
            )
        );

        // Debug logging
        if (get_option('aiwp_debug_mode', false)) {
            error_log('AIWP Gemini Request: ' . json_encode($body));
        }

        // Send request
        $url = sprintf(
            '%s/models/%s:generateContent?key=%s',
            $this->endpoint,
            $model,
            $this->api_key
        );

        $response = wp_remote_post($url, array(
            'headers' => array(
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode($body),
            'timeout' => 60
        ));

        // Handle errors
        if (is_wp_error($response)) {
            return $response;
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $body_response = json_decode(wp_remote_retrieve_body($response), true);

        // Debug logging
        if (get_option('aiwp_debug_mode', false)) {
            error_log('AIWP Gemini Response Status: ' . $status_code);
            error_log('AIWP Gemini Response Body: ' . json_encode($body_response));
        }

        // Error handling
        if ($status_code !== 200) {
            return $this->parse_error($body_response, $status_code);
        }

        // Convert Gemini response to OpenAI format for consistency
        return $this->convert_response($body_response);
    }

    /**
     * Convert OpenAI messages to Gemini format
     */
    private function convert_messages($messages)
    {
        $gemini_messages = array();
        $system_instruction = '';

        foreach ($messages as $message) {
            if ($message['role'] === 'system') {
                // Gemini doesn't have system role, prepend to first user message
                $system_instruction .= $message['content'] . "\n\n";
            } else {
                $role = $message['role'] === 'assistant' ? 'model' : 'user';

                $content = $message['content'];
                if ($role === 'user' && !empty($system_instruction)) {
                    $content = $system_instruction . $content;
                    $system_instruction = '';
                }

                $gemini_messages[] = array(
                    'role' => $role,
                    'parts' => array(
                        array('text' => $content)
                    )
                );
            }
        }

        return $gemini_messages;
    }

    /**
     * Convert Gemini response to OpenAI format
     */
    private function convert_response($gemini_response)
    {
        if (!isset($gemini_response['candidates'][0]['content']['parts'][0]['text'])) {
            return new WP_Error('invalid_response', 'Invalid Gemini response format');
        }

        $text = $gemini_response['candidates'][0]['content']['parts'][0]['text'];

        return array(
            'choices' => array(
                array(
                    'message' => array(
                        'role' => 'assistant',
                        'content' => $text
                    ),
                    'finish_reason' => 'stop'
                )
            ),
            'usage' => isset($gemini_response['usageMetadata']) ? array(
                'prompt_tokens' => $gemini_response['usageMetadata']['promptTokenCount'] ?? 0,
                'completion_tokens' => $gemini_response['usageMetadata']['candidatesTokenCount'] ?? 0,
                'total_tokens' => $gemini_response['usageMetadata']['totalTokenCount'] ?? 0
            ) : null
        );
    }

    /**
     * Parse API error
     */
    private function parse_error($response, $status_code)
    {
        $error_message = 'Unknown error';
        $error_code = 'api_error';

        if (isset($response['error'])) {
            $error = $response['error'];

            if (isset($error['message'])) {
                $error_message = $error['message'];
            }

            if (isset($error['code'])) {
                $error_code = $error['code'];
            }
        }

        return new WP_Error($error_code, 'Gemini API: ' . $error_message, array('status' => $status_code));
    }

    /**
     * Validate credentials
     */
    public function validate_credentials()
    {
        if (empty($this->api_key)) {
            return new WP_Error('no_api_key', 'Gemini API key not configured');
        }

        // Test with a simple request
        $test_response = $this->complete(array(
            array('role' => 'user', 'content' => 'Hi')
        ), array('max_tokens' => 5));

        if (is_wp_error($test_response)) {
            return $test_response;
        }

        return true;
    }

    /**
     * Get provider name
     */
    public function get_name()
    {
        return 'Google Gemini';
    }

    /**
     * Get provider metadata
     */
    public static function get_metadata()
    {
        return array(
            'id' => 'gemini',
            'name' => 'Google Gemini',
            'description' => 'Google\'s multimodal AI with generous free tier',
            'base_url' => 'https://generativelanguage.googleapis.com/v1beta',
            'signup_url' => 'https://aistudio.google.com/app/apikey',
            'models' => array(
                array(
                    'id' => 'gemini-1.5-flash',
                    'name' => 'Gemini 1.5 Flash',
                    'free' => true,
                    'context' => '1M tokens',
                    'recommended' => true
                ),
                array(
                    'id' => 'gemini-1.5-pro',
                    'name' => 'Gemini 1.5 Pro',
                    'free' => true,
                    'context' => '2M tokens'
                ),
                array(
                    'id' => 'gemini-2.0-flash-exp',
                    'name' => 'Gemini 2.0 Flash (Experimental)',
                    'free' => true,
                    'context' => '1M tokens'
                )
            )
        );
    }
}
