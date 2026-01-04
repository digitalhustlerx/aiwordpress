<?php
/**
 * Copilot Filesystem
 * 
 * Handles file operations for AI-generated content
 */

if (!defined('ABSPATH')) {
    exit;
}

class AIWP_Copilot_Filesystem {
    
    /**
     * Save generated content
     */
    public static function save_generated_content($post_id, $content, $type = 'html') {
        $upload_dir = wp_upload_dir();
        $aiwp_dir = $upload_dir['basedir'] . '/aiwp-copilot/';
        
        // Create directory if it doesn't exist
        if (!file_exists($aiwp_dir)) {
            wp_mkdir_p($aiwp_dir);
        }
        
        $filename = sprintf('%s-%s-%s.%s', 
            $post_id,
            $type,
            time(),
            $type === 'json' ? 'json' : 'html'
        );
        
        $filepath = $aiwp_dir . $filename;
        
        file_put_contents($filepath, $content);
        
        return array(
            'path' => $filepath,
            'url' => $upload_dir['baseurl'] . '/aiwp-copilot/' . $filename
        );
    }
    
    /**
     * Get history
     */
    public static function get_history($post_id, $limit = 10) {
        $upload_dir = wp_upload_dir();
        $aiwp_dir = $upload_dir['basedir'] . '/aiwp-copilot/';
        
        if (!file_exists($aiwp_dir)) {
            return array();
        }
        
        $files = glob($aiwp_dir . $post_id . '-*.html');
        rsort($files);
        
        return array_slice($files, 0, $limit);
    }
}
