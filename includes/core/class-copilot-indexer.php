<?php
/**
 * Handles file system scanning and indexing.
 */

if (!defined('ABSPATH')) {
    exit;
}

class AIWP_Copilot_Indexer
{

    /**
     * Scan the active theme directory and return the structure.
     */
    public static function scan_theme()
    {
        $theme = wp_get_theme();
        $theme_dir = $theme->get_stylesheet_directory();

        return self::scan_dir_recursive($theme_dir);
    }

    /**
     * Recursive scan avoiding dangerous/useless directories.
     */
    private static function scan_dir_recursive($dir)
    {
        $files = [];
        $ignored = ['.', '..', '.git', 'node_modules', 'vendor', 'cache'];

        if (is_dir($dir)) {
            if ($dh = opendir($dir)) {
                while (($file = readdir($dh)) !== false) {
                    if (in_array($file, $ignored))
                        continue;

                    $path = $dir . '/' . $file;
                    $relative_path = str_replace(get_theme_root(), '', $path);

                    if (is_dir($path)) {
                        $files[$file] = self::scan_dir_recursive($path);
                    } else {
                        // Only index safe code files
                        $ext = pathinfo($path, PATHINFO_EXTENSION);
                        if (in_array($ext, ['php', 'css', 'js', 'html', 'json'])) {
                            $files[] = $file;
                        }
                    }
                }
                closedir($dh);
            }
        }
        return $files;
    }
}
