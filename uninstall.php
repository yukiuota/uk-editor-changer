<?php
/**
 * UK Editor Changer Uninstall
 * 
 * プラグインのアンインストール時に実行される処理
 */

// セキュリティチェック
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// プラグインの設定を削除
delete_option('uk_editor_changer_options');

// マルチサイトの場合の処理
if (is_multisite()) {
    global $wpdb;
    
    $blog_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
    $original_blog_id = get_current_blog_id();
    
    foreach ($blog_ids as $blog_id) {
        switch_to_blog($blog_id);
        delete_option('uk_editor_changer_options');
    }
    
    switch_to_blog($original_blog_id);
}

// 一時的なキャッシュがあれば削除
wp_cache_delete('uk_editor_changer_options', 'options');
