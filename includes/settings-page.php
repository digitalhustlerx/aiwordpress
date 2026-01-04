<?php
/**
 * Settings Page
 * 
 * Admin settings page with multi-provider support
 */

if (!defined('ABSPATH')) {
    exit;
}

class AIWP_Settings_Page {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_settings_page'));
        add_action('admin_init', array($this, 'register_settings'));
    }
    
    /**
     * Add settings page
     */
    public function add_settings_page() {
        add_options_page(
            'AIWP Copilot Settings',
            'AIWP Copilot',
            'manage_options',
            'aiwp-copilot',
            array($this, 'render_settings_page')
        );
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('aiwp_settings', 'aiwp_providers');
        register_setting('aiwp_settings', 'aiwp_active_provider');
        register_setting('aiwp_settings', 'aiwp_specialist');
        register_setting('aiwp_settings', 'aiwp_debug_mode');
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Handle form submission
        if (isset($_POST['aiwp_settings_submit'])) {
            check_admin_referer('aiwp_settings');
            
            // Save provider configurations
            $providers = array();
            $all_providers_meta = AIWP_Provider_Registry::get_all_metadata();
            
            foreach ($all_providers_meta as $provider_meta) {
                $provider_id = $provider_meta['id'];
                $providers[$provider_id] = array(
                    'api_key' => isset($_POST["aiwp_api_key_{$provider_id}"]) ? 
                        sanitize_text_field($_POST["aiwp_api_key_{$provider_id}"]) : '',
                    'model' => isset($_POST["aiwp_model_{$provider_id}"]) ? 
                        sanitize_text_field($_POST["aiwp_model_{$provider_id}"]) : $provider_meta['models'][0]['id']
                );
            }
            
            update_option('aiwp_providers', $providers);
            update_option('aiwp_active_provider', sanitize_text_field($_POST['aiwp_active_provider']));
            update_option('aiwp_specialist', sanitize_text_field($_POST['aiwp_specialist']));
            update_option('aiwp_debug_mode', isset($_POST['aiwp_debug_mode']) ? 1 : 0);
            
            echo '<div class="notice notice-success"><p>Settings saved successfully!</p></div>';
        }
        
        // Get current values
        $providers_config = get_option('aiwp_providers', array());
        $active_provider = get_option('aiwp_active_provider', 'groq');
        $specialist = get_option('aiwp_specialist', 'default');
        $debug_mode = get_option('aiwp_debug_mode', false);
        
        // Get all providers metadata
        $providers_meta = AIWP_Provider_Registry::get_all_metadata();
        
        // Get all specialists
        $specialists = AIWP_Specialist_Registry::get_all();
        
        ?>
        <div class="wrap">
            <h1>🚀 AIWP Copilot Settings</h1>
            <p>Configure your AI providers and specialist modes for WordPress content assistance.</p>
            
            <form method="post" action="">
                <?php wp_nonce_field('aiwp_settings'); ?>
                
                <style>
                    .aiwp-provider-card {
                        background: #fff;
                        border: 2px solid #ddd;
                        border-radius: 8px;
                        padding: 20px;
                        margin-bottom: 20px;
                        transition: all 0.3s;
                    }
                    .aiwp-provider-card.active {
                        border-color: #2271b1;
                        box-shadow: 0 2px 8px rgba(34, 113, 177, 0.2);
                    }
                    .aiwp-provider-header {
                        display: flex;
                        align-items: center;
                        justify-content: space-between;
                        margin-bottom: 15px;
                    }
                    .aiwp-provider-title {
                        font-size: 18px;
                        font-weight: 600;
                        margin: 0;
                    }
                    .aiwp-provider-badge {
                        display: inline-block;
                        padding: 4px 12px;
                        border-radius: 12px;
                        font-size: 12px;
                        font-weight: 600;
                    }
                    .aiwp-badge-free {
                        background: #00a32a;
                        color: white;
                    }
                    .aiwp-badge-paid {
                        background: #f0b849;
                        color: #000;
                    }
                    .aiwp-badge-configured {
                        background: #00a32a;
                        color: white;
                    }
                    .aiwp-badge-not-configured {
                        background: #ddd;
                        color: #666;
                    }
                    .aiwp-provider-desc {
                        color: #666;
                        margin: 5px 0 15px 0;
                    }
                    .aiwp-form-row {
                        margin-bottom: 15px;
                    }
                    .aiwp-form-row label {
                        display: block;
                        font-weight: 600;
                        margin-bottom: 5px;
                    }
                    .aiwp-form-row input[type="text"],
                    .aiwp-form-row input[type="password"],
                    .aiwp-form-row select {
                        width: 100%;
                        max-width: 500px;
                        padding: 8px;
                        font-size: 14px;
                    }
                    .aiwp-api-key-wrapper {
                        position: relative;
                        display: inline-block;
                        width: 100%;
                        max-width: 500px;
                    }
                    .aiwp-api-key-toggle {
                        position: absolute;
                        right: 10px;
                        top: 50%;
                        transform: translateY(-50%);
                        background: none;
                        border: none;
                        cursor: pointer;
                        font-size: 16px;
                    }
                    .aiwp-get-key-btn {
                        display: inline-block;
                        margin-top: 5px;
                        padding: 6px 12px;
                        background: #2271b1;
                        color: white;
                        text-decoration: none;
                        border-radius: 4px;
                        font-size: 13px;
                    }
                    .aiwp-get-key-btn:hover {
                        background: #135e96;
                        color: white;
                    }
                    .aiwp-model-option-free {
                        color: #00a32a;
                        font-weight: 600;
                    }
                    .aiwp-section-header {
                        background: #f0f0f1;
                        padding: 15px 20px;
                        margin: 30px 0 20px 0;
                        border-left: 4px solid #2271b1;
                    }
                    .aiwp-section-header h2 {
                        margin: 0;
                        font-size: 20px;
                    }
                </style>
                
                <!-- PROVIDER CONFIGURATION -->
                <div class="aiwp-section-header">
                    <h2>🔌 AI Provider Configuration</h2>
                    <p style="margin: 8px 0 0 0;">Configure your AI provider API keys and select models. We recommend starting with free providers like Groq or Gemini.</p>
                </div>
                
                <?php foreach ($providers_meta as $provider): 
                    $provider_id = $provider['id'];
                    $api_key = isset($providers_config[$provider_id]['api_key']) ? $providers_config[$provider_id]['api_key'] : '';
                    $model = isset($providers_config[$provider_id]['model']) ? $providers_config[$provider_id]['model'] : $provider['models'][0]['id'];
                    $is_configured = !empty($api_key);
                    $is_active = ($provider_id === $active_provider);
                    $has_free = false;
                    foreach ($provider['models'] as $m) {
                        if ($m['free']) {
                            $has_free = true;
                            break;
                        }
                    }
                ?>
                
                <div class="aiwp-provider-card <?php echo $is_active ? 'active' : ''; ?>">
                    <div class="aiwp-provider-header">
                        <div>
                            <h3 class="aiwp-provider-title">
                                <input type="radio" 
                                       name="aiwp_active_provider" 
                                       value="<?php echo esc_attr($provider_id); ?>" 
                                       <?php checked($active_provider, $provider_id); ?>>
                                <?php echo esc_html($provider['name']); ?>
                            </h3>
                            <p class="aiwp-provider-desc"><?php echo esc_html($provider['description']); ?></p>
                        </div>
                        <div>
                            <?php if ($has_free): ?>
                                <span class="aiwp-provider-badge aiwp-badge-free">🆓 FREE TIER</span>
                            <?php else: ?>
                                <span class="aiwp-provider-badge aiwp-badge-paid">💳 PAID</span>
                            <?php endif; ?>
                            <br>
                            <span class="aiwp-provider-badge <?php echo $is_configured ? 'aiwp-badge-configured' : 'aiwp-badge-not-configured'; ?>">
                                <?php echo $is_configured ? '✅ Configured' : '⚠️ Not Configured'; ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="aiwp-form-row">
                        <label for="aiwp_api_key_<?php echo esc_attr($provider_id); ?>">API Key</label>
                        <div class="aiwp-api-key-wrapper">
                            <input type="password" 
                                   name="aiwp_api_key_<?php echo esc_attr($provider_id); ?>" 
                                   id="aiwp_api_key_<?php echo esc_attr($provider_id); ?>" 
                                   value="<?php echo esc_attr($api_key); ?>" 
                                   placeholder="Enter your API key"
                                   class="aiwp-api-key-input">
                            <button type="button" 
                                    class="aiwp-api-key-toggle" 
                                    onclick="toggleApiKey('<?php echo esc_js($provider_id); ?>')">
                                👁️
                            </button>
                        </div>
                        <a href="<?php echo esc_url($provider['signup_url']); ?>" 
                           target="_blank" 
                           class="aiwp-get-key-btn">
                            🔑 Get API Key
                        </a>
                    </div>
                    
                    <div class="aiwp-form-row">
                        <label for="aiwp_model_<?php echo esc_attr($provider_id); ?>">Model</label>
                        <select name="aiwp_model_<?php echo esc_attr($provider_id); ?>" 
                                id="aiwp_model_<?php echo esc_attr($provider_id); ?>">
                            <?php foreach ($provider['models'] as $model_option): ?>
                                <option value="<?php echo esc_attr($model_option['id']); ?>" 
                                        <?php selected($model, $model_option['id']); ?>
                                        class="<?php echo $model_option['free'] ? 'aiwp-model-option-free' : ''; ?>">
                                    <?php echo esc_html($model_option['name']); ?>
                                    <?php if ($model_option['free']): ?>🆓<?php endif; ?>
                                    - <?php echo esc_html($model_option['context']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <?php endforeach; ?>
                
                <!-- SPECIALIST MODE -->
                <div class="aiwp-section-header">
                    <h2>🎯 AI Specialist Mode</h2>
                    <p style="margin: 8px 0 0 0;">Choose an AI specialist to optimize responses for specific tasks.</p>
                </div>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="aiwp_specialist">Active Specialist</label>
                        </th>
                        <td>
                            <select name="aiwp_specialist" id="aiwp_specialist" class="regular-text" style="font-size: 16px; padding: 8px;">
                                <?php
                                // Group by tier
                                $tier1 = AIWP_Specialist_Registry::get_by_tier('tier1');
                                $tier2 = AIWP_Specialist_Registry::get_by_tier('tier2');
                                $default_specialist = AIWP_Specialist_Registry::get('default');
                                
                                // Default option
                                if ($default_specialist) {
                                    printf(
                                        '<option value="default" %s>%s %s</option>',
                                        selected($specialist, 'default', false),
                                        esc_html($default_specialist['icon']),
                                        esc_html($default_specialist['name'])
                                    );
                                }
                                
                                // Tier 1 specialists
                                if (!empty($tier1)) {
                                    echo '<optgroup label="━━━ ⭐ CORE SPECIALISTS ━━━">';
                                    foreach ($tier1 as $s) {
                                        printf(
                                            '<option value="%s" %s>%s %s</option>',
                                            esc_attr($s['id']),
                                            selected($specialist, $s['id'], false),
                                            esc_html($s['icon']),
                                            esc_html($s['name'])
                                        );
                                    }
                                    echo '</optgroup>';
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    
                    <!-- Debug Mode -->
                    <tr style="border-top: 3px solid #2271b1;">
                        <th scope="row">
                            <label for="aiwp_debug_mode">Debug Mode</label>
                        </th>
                        <td>
                            <label>
                                <input type="checkbox" 
                                       name="aiwp_debug_mode" 
                                       id="aiwp_debug_mode" 
                                       value="1" 
                                       <?php checked($debug_mode, 1); ?>>
                                Enable debug logging
                            </label>
                            <p class="description">Logs API requests/responses to debug.log for troubleshooting.</p>
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <input type="submit" 
                           name="aiwp_settings_submit" 
                           class="button button-primary" 
                           value="Save Settings">
                </p>
            </form>
            
            <!-- Quick Start Guide -->
            <div style="margin-top: 40px; padding: 20px; background: #fff; border: 1px solid #ccc;">
                <h2>🚀 Quick Start Guide</h2>
                
                <h3>1. Choose a Provider</h3>
                <p><strong>Recommended for beginners:</strong> Groq (free, fast) or Google Gemini (free, high quality)</p>
                <ul>
                    <li><strong>Groq:</strong> Ultra-fast, free tier, great for testing</li>
                    <li><strong>Google Gemini:</strong> High quality, generous free tier, 1M tokens context</li>
                    <li><strong>OpenRouter:</strong> Access multiple models through one API</li>
                    <li><strong>OpenAI:</strong> Industry standard, paid only</li>
                </ul>
                
                <h3>2. Get Your API Key</h3>
                <p>Click the "🔑 Get API Key" button for your chosen provider and follow their signup process (usually free).</p>
                
                <h3>3. Configure & Activate</h3>
                <p>Paste your API key, select a model (🆓 = free), then click the radio button to activate that provider.</p>
                
                <h3>4. Use in Editor</h3>
                <p>Open any post/page, and the AI Copilot widget will appear. You can switch providers on-the-fly from the widget!</p>
            </div>
        </div>
        
        <script>
        function toggleApiKey(providerId) {
            var input = document.getElementById('aiwp_api_key_' + providerId);
            if (input.type === 'password') {
                input.type = 'text';
            } else {
                input.type = 'password';
            }
        }
        </script>
        <?php
    }
}
