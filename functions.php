<?php //子テーマ用関数
if ( !defined( 'ABSPATH' ) ) exit;

//子テーマ用のビジュアルエディタースタイルを適用
add_editor_style();

//以下に子テーマ用の関数を書く
function mytheme_admin_enqueue() {
    // cssファイルを追加
    wp_enqueue_style( 'my_admin_css', get_stylesheet_directory_uri() . '/my-admin-style.css' );
}
add_action( 'admin_enqueue_scripts', 'mytheme_admin_enqueue' );

require_once(get_stylesheet_directory().'/lib/ggp_game.php');
require_once(get_stylesheet_directory().'/lib/ggp_setup.php');
require_once(get_stylesheet_directory().'/lib/db.php');
require_once(get_stylesheet_directory().'/lib/ggp_graph.php');
require_once(get_stylesheet_directory().'/lib/ggp_ajax.php');
require_once(get_stylesheet_directory().'/lib/ggp_testcode.php');
require_once(get_stylesheet_directory().'/lib/ggp_common.php');



