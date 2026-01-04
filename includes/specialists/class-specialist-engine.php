<?php
/**
 * Specialist Engine
 * 
 * Handles specialist context injection into AI requests
 */

if (!defined('ABSPATH')) {
    exit;
}

class AIWP_Specialist_Engine {
    
    /**
     * Inject specialist context into messages
     *
     * @param array $messages Original messages
     * @param string $specialist_id Specialist ID
     * @return array Modified messages with specialist context
     */
    public static function inject_specialist_context($messages, $specialist_id = 'default') {
        // Get specialist
        $specialist = AIWP_Specialist_Registry::get($specialist_id);
        
        if (!$specialist || $specialist_id === 'default') {
            return $messages; // No modification for default
        }
        
        // Prepend system message with specialist prompt
        $specialist_message = array(
            'role' => 'system',
            'content' => $specialist['prompt']
        );
        
        // If first message is already system, merge prompts
        if (!empty($messages) && $messages[0]['role'] === 'system') {
            $messages[0]['content'] = $specialist['prompt'] . "\n\n" . $messages[0]['content'];
        } else {
            // Otherwise prepend specialist system message
            array_unshift($messages, $specialist_message);
        }
        
        return $messages;
    }
    
    /**
     * Get specialist badge HTML
     *
     * @param string $specialist_id Specialist ID
     * @return string HTML badge
     */
    public static function get_specialist_badge($specialist_id = 'default') {
        if ($specialist_id === 'default') {
            return '';
        }
        
        $specialist = AIWP_Specialist_Registry::get($specialist_id);
        
        if (!$specialist) {
            return '';
        }
        
        return sprintf(
            '<div class="aiwp-specialist-badge">
                <span class="specialist-icon">%s</span>
                <span class="specialist-name">%s Active</span>
            </div>',
            esc_html($specialist['icon']),
            esc_html($specialist['name'])
        );
    }
    
    /**
     * Get specialist description for UI
     *
     * @param string $specialist_id Specialist ID
     * @return string Description text
     */
    public static function get_specialist_description($specialist_id) {
        $specialist = AIWP_Specialist_Registry::get($specialist_id);
        return $specialist ? $specialist['description'] : '';
    }
    
    /**
     * Check if user has access to specialist
     *
     * @param string $specialist_id Specialist ID
     * @return bool Access granted
     */
    public static function has_access($specialist_id) {
        $specialist = AIWP_Specialist_Registry::get($specialist_id);
        
        if (!$specialist) {
            return false;
        }
        
        // Free tier: only 'default' and 'tier1'
        // For now, grant access to all tier1 specialists
        // In future, implement proper license checking
        
        $allowed_tiers = array('free', 'tier1');
        
        return in_array($specialist['tier'], $allowed_tiers);
    }
}
