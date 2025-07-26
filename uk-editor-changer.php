<?php
/**
 * Plugin Name: UK Editor Changer
 * Description: 投稿タイプごとにクラシックエディタとGutenbergエディタを選択できるプラグイン
 * Version: 1.0.0
 * Author: Y.U.
 * License: GPL v2
 * Text Domain: uk-editor-changer
 */

// セキュリティチェック
if (!defined('ABSPATH')) {
    exit;
}

// プラグインの定数定義
define('UK_EDITOR_CHANGER_VERSION', '1.0.0');
define('UK_EDITOR_CHANGER_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('UK_EDITOR_CHANGER_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * UK Editor Changer Main Class
 */
class UK_Editor_Changer {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('admin_init', array($this, 'admin_init'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('use_block_editor_for_post_type', array($this, 'use_block_editor_for_post_type'), 10, 2);
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        load_plugin_textdomain('uk-editor-changer', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    
    /**
     * Admin initialize
     */
    public function admin_init() {
        register_setting('uk_editor_changer_settings', 'uk_editor_changer_options');
        
        add_settings_section(
            'uk_editor_changer_section',
            __('エディタ設定', 'uk-editor-changer'),
            array($this, 'settings_section_callback'),
            'uk_editor_changer_settings'
        );
        
        // 投稿タイプごとの設定フィールドを追加
        $post_types = get_post_types(array('public' => true), 'objects');
        foreach ($post_types as $post_type) {
            // メディア（attachment）は除外
            if ($post_type->name === 'attachment') {
                continue;
            }
            
            add_settings_field(
                'uk_editor_' . $post_type->name,
                sprintf(__('%s のエディタ', 'uk-editor-changer'), $post_type->label),
                array($this, 'editor_field_callback'),
                'uk_editor_changer_settings',
                'uk_editor_changer_section',
                array('post_type' => $post_type->name, 'label' => $post_type->label)
            );
        }
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_options_page(
            __('UK Editor Changer', 'uk-editor-changer'),
            __('各エディタ設定', 'uk-editor-changer'),
            'manage_options',
            'uk-editor-changer',
            array($this, 'admin_page')
        );
    }
    
    /**
     * Settings section callback
     */
    public function settings_section_callback() {
        echo '<p>' . __('投稿タイプごとに使用するエディタを選択してください。', 'uk-editor-changer') . '</p>';
    }
    
    /**
     * Editor field callback
     */
    public function editor_field_callback($args) {
        $options = get_option('uk_editor_changer_options');
        $post_type = $args['post_type'];
        $current_value = isset($options[$post_type]) ? $options[$post_type] : 'gutenberg';
        
        echo '<select name="uk_editor_changer_options[' . $post_type . ']">';
        echo '<option value="gutenberg"' . selected($current_value, 'gutenberg', false) . '>' . __('Gutenberg (ブロックエディタ)', 'uk-editor-changer') . '</option>';
        echo '<option value="classic"' . selected($current_value, 'classic', false) . '>' . __('クラシックエディタ', 'uk-editor-changer') . '</option>';
        echo '</select>';
        
        echo '<p class="description">' . sprintf(__('%s の編集に使用するエディタを選択してください。', 'uk-editor-changer'), $args['label']) . '</p>';
    }
    
    /**
     * Admin page
     */
    public function admin_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields('uk_editor_changer_settings');
                do_settings_sections('uk_editor_changer_settings');
                submit_button();
                ?>
            </form>
            
            <div class="uk-editor-changer-info">
                <h2><?php _e('使用方法', 'uk-editor-changer'); ?></h2>
                <ul>
                    <li><?php _e('各投稿タイプでGutenbergエディタまたはクラシックエディタを選択できます。', 'uk-editor-changer'); ?></li>
                    <li><?php _e('設定を変更した後は、新しい投稿・編集画面で反映されます。', 'uk-editor-changer'); ?></li>
                </ul>
            </div>
        </div>
        
        <style>
        .uk-editor-changer-info {
            margin-top: 30px;
            padding: 20px;
            background: #f9f9f9;
            border-left: 4px solid #0073aa;
        }
        .uk-editor-changer-info h2 {
            margin-top: 0;
        }
        .uk-editor-changer-info ul {
            margin-left: 20px;
        }
        </style>
        <?php
    }
    
    /**
     * Control which editor to use for post types
     */
    public function use_block_editor_for_post_type($use_block_editor, $post_type) {
        $options = get_option('uk_editor_changer_options');
        
        if (isset($options[$post_type])) {
            return $options[$post_type] === 'gutenberg';
        }
        
        // デフォルトではGutenbergを使用
        return $use_block_editor;
    }
    
    /**
     * Enqueue frontend scripts
     */
    public function enqueue_scripts() {
        // フロントエンド用のスクリプトが必要な場合はここに追加
    }
    
    /**
     * Enqueue admin scripts
     */
    public function admin_enqueue_scripts($hook) {
        if ('settings_page_uk-editor-changer' !== $hook) {
            return;
        }
        
        wp_enqueue_script(
            'uk-editor-changer-admin',
            UK_EDITOR_CHANGER_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            UK_EDITOR_CHANGER_VERSION,
            true
        );
        
        wp_enqueue_style(
            'uk-editor-changer-admin',
            UK_EDITOR_CHANGER_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            UK_EDITOR_CHANGER_VERSION
        );
    }
}

// プラグインのインスタンス化
new UK_Editor_Changer();

/**
 * Activation hook
 */
register_activation_hook(__FILE__, 'uk_editor_changer_activate');
function uk_editor_changer_activate() {
    // デフォルト設定を保存
    $default_options = array();
    $post_types = get_post_types(array('public' => true), 'names');
    
    foreach ($post_types as $post_type) {
        // メディア（attachment）は除外
        if ($post_type === 'attachment') {
            continue;
        }
        $default_options[$post_type] = 'gutenberg';
    }
    
    add_option('uk_editor_changer_options', $default_options);
}

/**
 * Deactivation hook
 */
register_deactivation_hook(__FILE__, 'uk_editor_changer_deactivate');
function uk_editor_changer_deactivate() {
    // 必要に応じてクリーンアップ処理
}
