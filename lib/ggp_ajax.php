<?php
/** ggp_setup.php
* @author arakawa asuka
* @date 2021/11/20
*/

require_once(get_stylesheet_directory().'/lib/db.php');

//非同期通信（地球毎のメッセージ取得）
function my_wp_get_ggp_msg_ajax() {

  $earth_no = $_POST['earth_no'];
  $ggp_message = get_db_table_ggp_message($earth_no);

  $nonce = $_REQUEST['nonce'];
  if ( wp_verify_nonce( $nonce, 'get_ggp_msg-nonce' ) ) {
    $response_msg = "";
    for($i = 0; $i < count($ggp_message); $i++) {
      $response_msg .= '<p class="p-ggp-msg">' . $ggp_message[$i]->msg . '</p>';
    }
    echo $response_msg;
  }
  die();
}
add_action( 'wp_ajax_get_ggp_msg_ajax', 'my_wp_get_ggp_msg_ajax' );
add_action( 'wp_ajax_nopriv_get_ggp_msg_ajax', 'my_wp_get_ggp_msg_ajax' );

//非同期通信（地球毎の最新メッセージのみ取得）
function my_wp_get_ggp_msg_newest_ajax() {

  $earth_no = $_POST['earth_no'];
  $ggp_message = get_db_table_ggp_message($earth_no);

  $nonce = $_REQUEST['nonce'];
  if ( wp_verify_nonce( $nonce, 'get_ggp_msg_newest-nonce' ) ) {
    $response_msg = $ggp_message[0]->msg;
    echo $response_msg;
  }
  die();
}
add_action( 'wp_ajax_get_ggp_msg_newest_ajax', 'my_wp_get_ggp_msg_newest_ajax' );
add_action( 'wp_ajax_nopriv_get_ggp_msg_newest_ajax', 'my_wp_get_ggp_msg_newest_ajax' );

//非同期通信（地球情報の更新）
function my_wp_get_ggp_earth_info_ajax() {
  $earth_no = $_POST['earth_no'];
  define('TABLE_NAME_GGP_TEAM',  $wpdb->prefix . 'ggp_team');
  $ggp_team = get_db_table_records_ggp(TABLE_NAME_GGP_TEAM,"earth_no",$earth_no);
  $ggp_quota_turn = get_option('ggp_quota_turn');

  $nonce = $_REQUEST['nonce'];
  if ( wp_verify_nonce( $nonce, 'get_ggp_earth_info-nonce' ) ) {
    $response_msg = '<table class="team_info">
    <tr><th width="100px"></th>
    ';
    for($i=0; $i < count($ggp_team); $i++) {
    $response_msg .= '<th>' . $ggp_team[$i]->teamname . '</th>';
    }
    $response_msg .= '</tr>
    <tr><td><i class="fas fa-heart"></i>残り回数</td>
    ';
    for($i=0; $i < count($ggp_team); $i++) {
    $response_msg .=  '<td>'. (intval($ggp_quota_turn) - intval($ggp_team[$i]->turn) + 1) . '/' . $ggp_quota_turn . 'ターン</td>';
    }
    $response_msg .= '</tr>
    <tr><td><i class="fas fa-coins"></i>お金</td>
    ';
    for($i=0; $i < count($ggp_team); $i++) {
    $response_msg .= '<td>' . $ggp_team[$i]->money .'万円</td>';
    }
    $response_msg .= '</tr>
    <tr><td><i class="fas fa-skull-crossbones"></i>二酸化炭素</td>
    ';
    for($i=0; $i < count($ggp_team); $i++) {
    $response_msg .= '<td>' . $ggp_team[$i]->co2 . 'kg/' . $ggp_team[$i]->co2_quota . 'kg</td>';
    }
    $response_msg .= '
    </tr>
    </table>
    ';
    echo $response_msg;
  }
  die();
}
add_action( 'wp_ajax_get_ggp_earth_info_ajax', 'my_wp_get_ggp_earth_info_ajax' );
add_action( 'wp_ajax_nopriv_get_ggp_earth_info_ajax', 'my_wp_get_ggp_earth_info_ajax' );

//非同期通信（地球毎の二酸化炭素排出量取得）

function my_wp_get_ggp_earth_co2_ajax() {

  $earth_no = $_POST['earth_no']; 
  define('TABLE_NAME_GGP_EARTH',  $wpdb->prefix . 'ggp_earth');
  $ggp_earth = get_db_table_records_ggp(TABLE_NAME_GGP_EARTH,"earth_no",$earth_no);
  //error_log()

  $nonce = $_REQUEST['nonce'];
  if ( wp_verify_nonce( $nonce, 'get_ggp_earth_co2-nonce' ) ) {
    $return_param = [ 'co2' => $ggp_earth[0]->co2,
                      'quota_co2' => $ggp_earth[0]->co2_quota ];
    echo json_encode($return_param);
    exit;
  }
  die();
}
add_action( 'wp_ajax_get_ggp_earth_co2_ajax', 'my_wp_get_ggp_earth_co2_ajax' );
add_action( 'wp_ajax_nopriv_get_ggp_earth_co2_ajax', 'my_wp_get_ggp_earth_co2_ajax' );


//非同期通信（チームの詳細情報取得）
function my_wp_get_ggp_team_description_ajax() {
  $earth_no = $_POST['earth_no'];
  $team_no = $_POST['team_no'];
  define('TABLE_NAME_GGP_TEAM',  $wpdb->prefix . 'ggp_team');
  $ggp_team = get_db_table_records_ggp(TABLE_NAME_GGP_TEAM,"earth_no",$earth_no);

  $nonce = $_REQUEST['nonce'];
  if ( wp_verify_nonce( $nonce, 'get_ggp_team_description-nonce' ) ) {
    $response_msg = "";
    $response_msg .= '<i class="fas fa-heart"></i>ターン：'. $ggp_team[$team_no]->turn .'ターン目<br>';
    $response_msg .= '<i class="fas fa-coins"></i>お金：' . $ggp_team[$team_no]->money .'万円<br>';
    $response_msg .= '<i class="fas fa-skull-crossbones"></i>CO2：'. $ggp_team[$team_no]->co2 .'kg<br>';
    echo $response_msg;
  }
  die();
}
add_action( 'wp_ajax_get_ggp_team_description_ajax', 'my_wp_get_ggp_team_description_ajax' );
add_action( 'wp_ajax_nopriv_get_ggp_team_description_ajax', 'my_wp_get_ggp_team_description_ajax' );

//非同期通信（植樹イベント発生）
function my_wp_check_ggp_event_tree_ajax() {
  $earth_no = $_POST['earth_no'];
  $nonce = $_REQUEST['nonce'];
  $response_msg = "";
  $ggp_earth = get_db_table_records_ggp(TABLE_NAME_GGP_EARTH,"earth_no",$earth_no);

  if ( wp_verify_nonce( $nonce, 'check_ggp_event_tree-nonce' ) ) {
    $response_msg = $ggp_earth[0]->event_valid_tree;
    echo $response_msg;
  }
  die();
}
add_action( 'wp_ajax_check_ggp_event_tree_ajax', 'my_wp_check_ggp_event_tree_ajax' );
add_action( 'wp_ajax_nopriv_check_ggp_event_tree_ajax', 'my_wp_check_ggp_event_tree_ajax' );

//非同期通信（売り上げ増加イベント発生）
function my_wp_check_ggp_event_sales_ajax() {
  $earth_no = $_POST['earth_no'];
  $nonce = $_REQUEST['nonce'];
  $response_msg = "";
  $ggp_earth = get_db_table_records_ggp(TABLE_NAME_GGP_EARTH,"earth_no",$earth_no);

  if ( wp_verify_nonce( $nonce, 'check_ggp_event_sales-nonce' ) ) {
    $response_msg = $ggp_earth[0]->event_valid_sales;
    echo $response_msg;
  }
  die();
}
add_action( 'wp_ajax_check_ggp_event_sales_ajax', 'my_wp_check_ggp_event_sales_ajax' );
add_action( 'wp_ajax_nopriv_check_ggp_event_sales_ajax', 'my_wp_check_ggp_event_sales_ajax' );

