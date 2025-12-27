<?php
/**
 * Handles direct file reading and writing via WP_Filesystem.
 */

if (!defined('ABSPATH')) {
    exit;
}

class AIWP_Copilot_Filesystem
{

    public function __construct()
    {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        WP_Filesystem();
    }

    /**
     * Read file content.
     */
    public static function read_file($path)
    {
        global $wp_filesystem;

        // Security: Ensure path is within WP Content
        if (strpos($path, WP_CONTENT_DIR) === false) {
            return new WP_Error('forbidden_path', 'Access denied to paths outside wp-content.');
        }

        if ($wp_filesystem->exists($path)) {
            return $wp_filesystem->get_contents($path);
        }
        return false;
    }

    /**
     * Write content to file.
     */
    public static function write_file($path, $content)
    {
        global $wp_filesystem;

        // Security: Ensure path is within WP Content
        if (strpos($path, WP_CONTENT_DIR) === false) {
            return new WP_Error('forbidden_path', 'Access denied to paths outside wp-content.');
        }

        // Create backup first (simple version)
        if ($wp_filesystem->exists($path)) {
            $backup_path = $path . '.bak.' . time();
            $wp_filesystem->copy($path, $backup_path);
        }

        return $wp_filesystem->put_contents($path, $content, FS_CHMOD_FILE);
    }
}
