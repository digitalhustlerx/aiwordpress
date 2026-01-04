<?php
/**
 * Copilot Indexer
 * 
 * Indexes WordPress content for AI context
 */

if (!defined('ABSPATH')) {
    exit;
}

class AIWP_Copilot_Indexer {
    
    /**
     * Get page/post context
     */
    public static function get_page_context($post_id) {
        $post = get_post($post_id);
        
        if (!$post) {
            return null;
        }
        
        $context = array(
            'id' => $post->ID,
            'title' => $post->post_title,
            'content' => $post->post_content,
            'excerpt' => $post->post_excerpt,
            'type' => $post->post_type,
            'status' => $post->post_status,
            'url' => get_permalink($post->ID),
            'meta' => array(
                'description' => get_post_meta($post->ID, '_yoast_wpseo_metadesc', true),
                'keywords' => get_post_meta($post->ID, '_yoast_wpseo_focuskw', true)
            )
        );
        
        return $context;
    }
    
    /**
     * Get Elementor structure
     */
    public static function get_elementor_structure($post_id) {
        if (!defined('ELEMENTOR_VERSION')) {
            return null;
        }
        
        $elementor_data = get_post_meta($post_id, '_elementor_data', true);
        
        if (!$elementor_data) {
            return null;
        }
        
        return json_decode($elementor_data, true);
    }
    
    /**
     * Get site structure
     */
    public static function get_site_structure() {
        return array(
            'name' => get_bloginfo('name'),
            'description' => get_bloginfo('description'),
            'url' => get_site_url(),
            'theme' => wp_get_theme()->get('Name'),
            'posts_count' => wp_count_posts()->publish,
            'pages_count' => wp_count_posts('page')->publish
        );
    }
}
