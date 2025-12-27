<?php
/**
 * OpenAI / Compatible Provider implementation.
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once AIWP_COPILOT_DIR . 'includes/providers/interface-provider.php';

class AIWP_OpenAI_Provider implements AIWP_Provider_Interface
{

    public function process($message, $context, $settings)
    {
        $api_key = $settings['api_key'];
        $model = $settings['model'] ?: 'gpt-4o-mini';
        $base_url = $settings['base_url'] ?: 'https://api.openai.com/v1';
        $endpoint = rtrim($base_url, '/') . '/chat/completions';

        $system_prompt = "You are a Senior WordPress Auditor & Developer Agent (God Mode).
You are working on a live site. You must be PROACTIVE and high-end.

CONTEXT (Site Structure & Content):
$context";

        if (!empty($settings['selector'])) {
            $system_prompt .= "\nUSER TARGET ELEMENT: " . $settings['selector'];
            if (!empty($settings['html'])) {
                $system_prompt .= "\nELEMENT HTML: " . $settings['html'];
            }
        }

        $system_prompt .= "

MISSION:
1. **Audit Mode**: If the user's message is an 'AUDIT_REQUEST', analyze the CONTEXT and suggest 3 high-impact improvements (UI, UX, or Copy). Provide them as a numbered list in the 'reply'.
2. **God Mode (Development)**: You can edit code directly to improve the site.
   - Use 'write_file' to edit theme files (functions.php, style.css) for deep logic changes.
   - Use 'apply_css' for visual/style changes.
   - Use 'update_content' to rewrite text or HTML on the fly.

STRICT RESPONSE FORMAT (JSON ONLY):
{
  \"reply\": \"Proactive advice, suggestion, or status update.\",
  \"action\": \"apply_css\" | \"update_content\" | \"write_file\" | \"list_files\" | \"read_file\" | \"none\",
  \"data\": {
     \"selector\": \"css-selector\",
     \"css\": { \"prop\": \"val\" },
     \"html\": \"new html content\",
     \"path\": \"full/path/to/file.php\",
     \"content\": \"FULL_NEW_FILE_CONTENT (if write_file)\"
  }
}
Always use futuristic, premium dark-mode aesthetics in your design suggestions.";

        $body = [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => $system_prompt],
                ['role' => 'user', 'content' => $message]
            ]
        ];

        if (strpos($base_url, 'openai.com') !== false) {
            $body['response_format'] = ['type' => 'json_object'];
        }

        $headers = [
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type' => 'application/json',
        ];

        if (strpos($base_url, 'openrouter.ai') !== false) {
            $headers['HTTP-Referer'] = home_url();
            $headers['X-Title'] = 'AIWP Copilot';
        }

        $response = wp_remote_post($endpoint, [
            'headers' => $headers,
            'body' => wp_json_encode($body),
            'timeout' => 45,
        ]);

        if (is_wp_error($response)) {
            return [
                'reply' => 'API Error: ' . $response->get_error_message(),
                'action' => 'none',
                'data' => (object) [],
            ];
        }

        $raw_body = wp_remote_retrieve_body($response);
        $result = json_decode($raw_body, true);

        if (isset($result['error'])) {
            $error_msg = is_array($result['error']) ? ($result['error']['message'] ?? 'Unknown Error') : $result['error'];
            return [
                'reply' => 'Provider Error: ' . $error_msg,
                'action' => 'none',
                'data' => (object) [],
            ];
        }

        $content = $result['choices'][0]['message']['content'] ?? '';
        $clean_content = preg_replace('/^```(?:json)?\s*|\s*```$/i', '', trim($content));
        $parsed = json_decode($clean_content, true);

        if (!$parsed) {
            return [
                'reply' => 'Error: AI returned non-JSON.',
                'action' => 'none',
                'data' => (object) [],
                'debug' => substr($content, 0, 100)
            ];
        }

        return [
            'reply' => !empty($parsed['reply']) ? $parsed['reply'] : 'Action applied.',
            'action' => $parsed['action'] ?? 'none',
            'data' => (object) ($parsed['data'] ?? []),
        ];
    }
}
