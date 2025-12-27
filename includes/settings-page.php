<?php
/**
 * Settings page for AIWP Copilot.
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1>AIWP Copilot Settings</h1>
    <form method="post" action="options.php">
        <?php
        settings_fields('aiwp_settings_group');
        do_settings_sections('aiwp-copilot');
        ?>

        <table class="form-table">
            <tr valign="top">
                <th scope="row">AI Provider</th>
                <td>
                    <select name="aiwp_provider">
                        <option value="openai" <?php selected(get_option('aiwp_provider'), 'openai'); ?>>OpenAI
                        </option>
                        <option value="gemini" <?php selected(get_option('aiwp_provider'), 'gemini'); ?>>Gemini
                        </option>
                        <option value="custom" <?php selected(get_option('aiwp_provider'), 'custom'); ?>>Custom
                            (OpenAI Compatible)</option>
                    </select>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">API Key</th>
                <td>
                    <input type="password" name="aiwp_api_key"
                        value="<?php echo esc_attr(get_option('aiwp_api_key')); ?>" class="regular-text" />
                    <p class="description">Your secret API key for the selected provider.</p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">Model Name</th>
                <td>
                    <input type="text" name="aiwp_model" value="<?php echo esc_attr(get_option('aiwp_model')); ?>"
                        class="regular-text" placeholder="e.g. gpt-4o or gemini-1.5-flash" />
                    <p class="description">The specific model identifier you wish to use.</p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">Custom Base URL</th>
                <td>
                    <input type="text" name="aiwp_base_url"
                        value="<?php echo esc_attr(get_option('aiwp_base_url')); ?>" class="regular-text"
                        placeholder="e.g. https://openrouter.ai/api/v1" />
                    <p class="description">Only required if using a custom/compatible endpoint (e.g. OpenRouter, Groq,
                        or self-hosted).</p>
                </td>
            </tr>
        </table>

        <?php submit_button(); ?>
    </form>
</div>