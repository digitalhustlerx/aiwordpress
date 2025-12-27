<?php
/**
 * Gemini Provider implementation.
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once AIWP_COPILOT_DIR . 'includes/providers/interface-provider.php';

class AIWP_Gemini_Provider implements AIWP_Provider_Interface
{

    public function process($message, $context, $settings)
    {
        $api_key = $settings['api_key'];
        $model = $settings['model'] ?: 'gemini-1.5-flash';
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$api_key}";

        $system_prompt = "You are a WordPress Expert Agent.
You are currently looking at a page on a WordPress site.
CONTEXT:
$context";

        if (!empty($settings['selector'])) {
            $system_prompt .= "\nTARGET ELEMENT SELECTOR: " . $settings['selector'];
            if (!empty($settings['html'])) {
                $system_prompt .= "\nTARGET ELEMENT HTML: " . $settings['html'];
            }
        }

        $system_prompt .= "\n\nMISSION:
1) If the user asks to rewrite content (e.g., hero, header, section), generate improved HTML and return action \"update_content\" with the selector and new html.
2) If the user asks for visual changes, return action \"apply_css\" with selector and css map.
3) If the user asks to scan or edit files, you may use \"read_file\" or \"write_file\" with absolute path inside the active theme.
4) Always keep responses concise and proactive.

STRICT RESPONSE FORMAT (JSON ONLY):
{
  \"reply\": \"Concise response for the user\",
  \"action\": \"apply_css\" | \"update_content\" | \"write_file\" | \"read_file\" | \"none\",
  \"data\": {
    \"selector\": \"CSS selector to target element (use provided selector when present)\",
    \"css\": { \"property\": \"value\" },
    \"html\": \"full HTML to replace inner content when action is update_content\",
    \"path\": \"full/path/inside/wp-content\"
  }
}";

        $body = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $system_prompt . "\n\nUser request: " . $message]
                    ]
                ]
            ],
            'generationConfig' => [
                'response_mime_type' => 'application/json',
            ],
        ];

        $response = wp_remote_post($url, [
            'headers' => ['Content-Type' => 'application/json'],
            'body' => wp_json_encode($body),
            'timeout' => 30,
        ]);

        if (is_wp_error($response)) {
            return [
                'reply' => 'API Error: ' . $response->get_error_message(),
                'action' => 'none',
                'data' => (object) [],
            ];
        }

        $result = json_decode(wp_remote_retrieve_body($response), true);

        if (isset($result['error'])) {
            return [
                'reply' => 'Gemini Error: ' . $result['error']['message'],
                'action' => 'none',
                'data' => (object) [],
            ];
        }

        $content = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';
        $parsed = json_decode(trim($content), true);

        if (!$parsed) {
            return [
                'reply' => 'Error: Could not parse Gemini response.',
                'action' => 'none',
                'data' => (object) [],
            ];
        }

        return [
            'reply' => $parsed['reply'] ?? 'Success.',
            'action' => $parsed['action'] ?? 'none',
            'data' => (object) ($parsed['data'] ?? []),
        ];
    }
}
