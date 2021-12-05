<?php
/** db.php
* @author arakawa asuka
* @date 2021/05/03
*/

// DBの親テーマの関数の読み込み
require_once(get_template_directory().'/lib/db.php');

define('TABLE_NAME_GGP_EARTH',  $wpdb->prefix . 'ggp_earth');
define('TABLE_NAME_GGP_TEAM',  $wpdb->prefix . 'ggp_team');
define('TABLE_NAME_GGP_ACTION',  $wpdb->prefix . 'ggp_action');
define('TABLE_NAME_GGP_ACTION_RICE',  $wpdb->prefix . 'ggp_action_rice');
define('TABLE_NAME_GGP_ACTION_TREE',  $wpdb->prefix . 'ggp_action_tree');
define('TABLE_NAME_GGP_MESSAGE',  $wpdb->prefix . 'ggp_message');
define('TABLE_NAME_GGP_OPTIONS',  $wpdb->prefix . 'options');

add_action('admin_head', 'create_table_ggp');
if( !function_exists('create_table_ggp') ) :
function create_table_ggp(){
  create_table_ggp_earth();
  create_table_ggp_team();
  create_table_ggp_action();
  create_table_ggp_action_rice();
  create_table_ggp_action_tree();
  create_table_ggp_message();
}
endif;

//汎用的なテーブルからレコードの取得
if ( !function_exists( 'get_db_table_records_ggp' ) ):
function get_db_table_records_ggp( $table_name, $column, $keyword = null, $order_by = null){
  global $wpdb;
  $where = null;
  if ($column) {
    $where = $wpdb->prepare(' WHERE '.$column.' LIKE %s', '%'.$keyword.'%');
  }
  if ($order_by) {
    $order_by = esc_sql(' ORDER BY '.$order_by);
  }
  $query = "SELECT * FROM {$table_name}".
              $where.
              $order_by;

  $records = $wpdb->get_results( $query );
  //_v($query);

  return $records;
}
endif;


/****************************************************************
* テーブル名：wp_ggp_earth
* 役割：地球の設定の保持
*
* <<テーブル情報>>
*  *地球番号            earth_no          int 地球番号
*  二酸化炭素排出量     co2               int 地球ごとの二酸化炭素の排出量合計
*  二酸化炭素排出量上限 co2_quota         int 地球ごとの二酸化炭素排出量上限
*  植樹効果倍増         event_valid_tree  int 0ならfalse, 1ならtrue
*  売り上げアップ       event_valid_sales int 0ならfalse, 1ならtrue
*  イベントカード       event_card_turn   int イベントルーレットが発生したターンを記録
*****************************************************************/

// テーブルを作成する
if( !function_exists( 'create_table_ggp_earth' ) ):
function create_table_ggp_earth() {
  if (is_db_table_exist(TABLE_NAME_GGP_EARTH) ){
    return;
  }

  $sql = "CREATE TABLE " .TABLE_NAME_GGP_EARTH." (
        earth_no int PRIMARY KEY,
        co2 int,
        co2_quota int,
        event_valid_tree int,
        event_valid_sales int,
        event_card_turn int
        );";
  $res = create_db_table($sql);
  return $res;
}
endif;

//テーブルに初期値を設定する
if (! function_exists( 'reset_table_ggp_earth' ) ):
function reset_table_ggp_earth(){

  uninstall_db_table(TABLE_NAME_GGP_EARTH);
  create_table_ggp_earth();

  $ggp_quota_co2 = get_option('ggp_quota_co2');
  $ggp_init_earth = get_option('ggp_init_earth');

  for($i =0; $i < $ggp_init_earth; $i++){
    $table = TABLE_NAME_GGP_EARTH;
    $data = array(
      'earth_no' => $i,
      'co2' => 0,
      'co2_quota' => $ggp_quota_co2,
      'event_valid_tree' => 0,
      'event_valid_sales' => 0,
      'event_card_turn' => 0
    );

    $format = array(
      '%d',
      '%d',
      '%d',
      '%d',
      '%d',
      '%d'
    );
    insert_db_table_record($table, $data, $format);
  }
}
endif;

if (! function_exists( 'update_table_ggp_earth' ) ):
function update_table_ggp_earth($earth_no, $co2){
  $table = TABLE_NAME_GGP_EARTH;
  $data = array('co2' => $co2);
  $where = array('earth_no'=>$earth_no);
  $format = array('%d');
  $where_format = array('%d');
  return update_db_table_record($table, $data, $where, $format, $where_format);
}
endif;

if (! function_exists( 'update_table_ggp_earth_quota' ) ):
function update_table_ggp_earth_quota($earth_no, $co2_quota){
  $table = TABLE_NAME_GGP_EARTH;
  $data = array('co2_quota' => $co2_quota);
  $where = array('earth_no'=>$earth_no);
  $format = array('%d');
  $where_format = array('%d');
  return update_db_table_record($table, $data, $where, $format, $where_format);
}
endif;

if (! function_exists( 'update_table_ggp_earth_event_tree' ) ):
function update_table_ggp_earth_event_tree($earth_no){
  $table = TABLE_NAME_GGP_EARTH;
  $data = array('event_valid_tree' => 1);
  $where = array('earth_no'=>$earth_no);
  $format = array('%d');
  $where_format = array('%d');
  return update_db_table_record($table, $data, $where, $format, $where_format);
}
endif;

if (! function_exists( 'update_table_ggp_earth_event_sales' ) ):
function update_table_ggp_earth_event_sales($earth_no){
  $table = TABLE_NAME_GGP_EARTH;
  $data = array('event_valid_sales' => 1);
  $where = array('earth_no'=>$earth_no);
  $format = array('%d');
  $where_format = array('%d');
  return update_db_table_record($table, $data, $where, $format, $where_format);
}
endif;

if (! function_exists( 'check_event_arise') ):
function check_event_arise($earth_no, $num_sales_activity){

  $table = TABLE_NAME_GGP_ACTION_RICE;
  global $wpdb;
  $query = "SELECT to_store FROM {$table} WHERE earth_no LIKE {$earth_no}";
  $records = $wpdb->get_results( $query );
  $ggp_init_event_tree = get_option('ggp_event_tree');
  $ggp_init_event_sales = get_option('ggp_event_sales');

  if($num_sales_activity == "1"){
    if($ggp_init_event_tree == NULL) return "";
    foreach($records as $team){
      if($team->to_store < 5){
        return "";
      }
    }
    return true;
  }

  if($num_sales_activity == "2"){
    if($ggp_init_event_sales == NULL) return "";
    foreach($records as $team){
      if($team->to_store < 10){
        return "";
      }
    }
    return true;
  }
}
endif;

if( !function_exists( ' get_table_ggp_earth_all') ):
function get_table_ggp_earth_all(){
  $table = TABLE_NAME_GGP_EARTH;
  global $wpdb;
  $query = "SELECT * FROM {$table}";

  $records = $wpdb->get_results( $query );

  return $records;

}
endif;

if( !function_exists( 'get_table_ggp_earth_event_card_turn') ):
function get_table_ggp_earth_event_card_turn($earth_no){
  $table = TABLE_NAME_GGP_EARTH;
  global $wpdb;
  $query = "SELECT event_card_turn FROM {$table} WHERE earth_no = {$earth_no}";
  $records = $wpdb->get_results( $query );

  return $records;

}
endif;


if( !function_exists( 'update_table_ggp_earth_event_card_turn') ):
function update_table_ggp_earth_event_card_turn($earth_no, $turn){
  $table = TABLE_NAME_GGP_EARTH;
  global $wpdb;
  $query = "UPDATE {$table} SET event_card_turn = {$turn} WHERE earth_no = {$earth_no}";
  $records = $wpdb->query( $query );

  return $records;

}
endif;
/****************************************************************
* テーブル名：wp_ggp_team
* 役割：チーム設定の保持
*
* <<テーブル情報>>
* *地球番号     earth_no  int         チームを表す通し番号
* *チーム番号   team_no   int         チーム名
* チーム名      teamname      varchar(50) チーム名
* 現在のターン  turn      int         初期値は1
* お金          money     int
* Co2排出量     co2       int
*****************************************************************/
if( !function_exists( 'create_table_ggp_team' ) ):
function create_table_ggp_team() {
  if (is_db_table_exist(TABLE_NAME_GGP_TEAM) ){
    return;
  }

  $sql = "CREATE TABLE " .TABLE_NAME_GGP_TEAM." (
          earth_no int NOT NULL,
          team_no int NOT NULL,
          teamname varchar(50),
          turn int,
          money int,
          co2 int,
          co2_quota int,
          PRIMARY KEY(earth_no, team_no)
          );";

  $res = create_db_table($sql);
  return $res;
}
endif;

if(! function_exists( 'reset_table_ggp_team' ) ):
function reset_table_ggp_team(){
  uninstall_db_table(TABLE_NAME_GGP_TEAM);
  create_table_ggp_team();

  $ggp_init_earth = get_option('ggp_init_earth');
  $ggp_init_perteam = get_option('ggp_init_perteam');
  $ggp_init_money = get_option('ggp_init_money');
  $ggp_init_quota_co2_perteam = get_option('ggp_init_quota_co2_perteam');

  for($i =0; $i < $ggp_init_earth; $i++){
  for($j = 0; $j < $ggp_init_perteam; $j++){
    $table = TABLE_NAME_GGP_TEAM;
    $data = array(
      'earth_no' => $i,
      'team_no' => $j,
      'turn' => 1,
      'teamname' => $i ."-" . $j . "チーム",
      'money' => $ggp_init_money,
      'co2' => 0,
      'co2_quota' => $ggp_init_quota_co2_perteam
    );

    $format = array(
      '%d',
      '%d',
      '%d',
      '%s',
      '%d',
      '%d',
      '%d'
    );
    insert_db_table_record($table, $data, $format);
  }
  }

}
endif;

if(! function_exists( 'reset_table_ggp_team_transaction' ) ):
function reset_table_ggp_team_transaction(){

  $ggp_init_earth = get_option('ggp_init_earth');
  $ggp_init_perteam = get_option('ggp_init_perteam');
  $ggp_init_money = get_option('ggp_init_money');
  $ggp_init_quota_co2_perteam = get_option('ggp_init_quota_co2_perteam');

  for($i =0; $i < $ggp_init_earth; $i++){
  for($j = 0; $j < $ggp_init_perteam; $j++){
    $table = TABLE_NAME_GGP_TEAM;
    $data = array(
      'turn' => 1,
      'money' => $ggp_init_money,
      'co2' => 0,
      'co2_quota' => $ggp_init_quota_co2_perteam
    );

    $where = array(
      'earth_no' => $i,
      'team_no' => $j
    );

    $format = array(
      '%d',
      '%d',
      '%d',
      '%d'
    );

    $where_format = array(
      '%d',
      '%d'
    );

    update_db_table_record($table, $data, $where, $format, $where_format);
  }
  }

}
endif;


if(! function_exists( 'update_table_ggp_team' ) ):
function update_table_ggp_team($earth_no, $team_no, $teamname, $money, $co2){
// var_dump("in update_table_ggp_team: earth_no:".$earth_no." team_no:".$team_no." teamname:".$teamname." money:".$money." co2:".$co2." <br>");
  $table = TABLE_NAME_GGP_TEAM;
  $data = array(
            'teamname' => $teamname,
            'money' => $money,
            'co2' => $co2
            );
  $where = array(
            'earth_no' => $earth_no,
            'team_no' => $team_no
            );

  $format = array(
            '%s',
            '%d',
            '%d'
            );
  $where_format = array(
            '%d',
            '%d'
            );
  return update_db_table_record($table, $data, $where, $format, $where_format);

}
endif;

if(! function_exists( 'update_table_ggp_team_transaction' ) ):
function update_table_ggp_team_transaction($earth_no, $team_no, $turn, $money, $co2){
  $table = TABLE_NAME_GGP_TEAM;
  $ggp_team = get_db_table_records_ggp(TABLE_NAME_GGP_TEAM,"earth_no",$earth_no);

  $data = array(
            'turn' => $ggp_team[$team_no]->turn+$turn,
            'money' => $ggp_team[$team_no]->money-$money,
            'co2' => $ggp_team[$team_no]->co2+$co2
            );
  $where = array(
            'earth_no' => $earth_no,
            'team_no' => $team_no
            );

  $format = array(
            '%d',
            '%d',
            '%d'
            );
  $where_format = array(
            '%d',
            '%d'
            );
  return update_db_table_record($table, $data, $where, $format, $where_format);

}
endif;

if(! function_exists( 'update_table_ggp_team_co2_quota' ) ):
function update_table_ggp_team_co2_quota($earth_no, $team_no, $co2_quota){
  $table = TABLE_NAME_GGP_TEAM;

  $data = array(
            'co2_quota' => $co2_quota
            );
  $where = array(
            'earth_no' => $earth_no,
            'team_no' => $team_no
            );

  $format = array(
            '%d'
            );
  $where_format = array(
            '%d',
            '%d'
            );
  return update_db_table_record($table, $data, $where, $format, $where_format);

}
endif;

if(! function_exists( 'get_ggp_team_all') ):
function get_table_ggp_team_all(){
  $table = TABLE_NAME_GGP_TEAM;
  global $wpdb;
  $query = "SELECT * FROM {$table}";
  $records = $wpdb->get_results( $query );
  return $records;
}
endif;


/****************************************************************
* テーブル名：wp_ggp_action
* 役割：各チームの活動情報の保持
*
* <<テーブル情報>>
* *ID             id            int           auto_increment
* 地球No          earth_no      int           地球の番号
* チームNo        team_no       int           チーム番号
* フェーズ        phase         varchar(50)
* ターン          turn          int           何ターン目か
* カードの名称    cardname      varchar(50)   カードの名称
* アイコンURL     url           varchar(50)   アイコンのURL
* 特殊カードKey   keyword       varchar(50)   特殊カードのキー値
* お金の増減      money         int
* Co2の増減       co2           int
* 必要なターン数  require_turn  int
*****************************************************************/
if( !function_exists( 'create_table_ggp_action' ) ):
function create_table_ggp_action() {
  if (is_db_table_exist(TABLE_NAME_GGP_ACTION) ){
    return;
  }

  $sql = "CREATE TABLE " .TABLE_NAME_GGP_ACTION." (
          id int AUTO_INCREMENT NOT NULL PRIMARY KEY,
          token varchar(100),
          earth_no int,
          team_no int,
          phase varchar(50),
          turn int,
          cardname varchar(50),
          url varchar(50),
          keyword varchar(50),
          money int,
          co2 int,
          require_turn int
          );";
  $res = create_db_table($sql);
  return $res;
}
endif;

if( !function_exists( 'reset_table_ggp_action') ):
function reset_table_ggp_action(){
  uninstall_db_table(TABLE_NAME_GGP_ACTION);
  create_table_ggp_action();

}
endif;

if( !function_exists( 'insert_table_ggp_action' ) ):
function insert_table_ggp_action($token, $earth_no, $team_no, $phase, $cardname, $url, $key, $money, $co2, $require_turn ){
  $table = TABLE_NAME_GGP_ACTION;
  $ggp_team = get_db_table_records_ggp(TABLE_NAME_GGP_TEAM,"earth_no",$earth_no);
  $data = array(
          'token' => $token,
          'earth_no' => $earth_no,
          'team_no' => $team_no,
          'phase' => $phase,
          'turn' => $ggp_team[$team_no]->turn,
          'cardname' => $cardname,
          'url' => $url,
          'keyword' => $key,
          'money' => $money,
          'co2' => $co2,
          'require_turn' => $require_turn
          );
  $format = array(
            '%s',
            '%d',
            '%d',
            '%s',
            '%d',
            '%s',
            '%s',
            '%s',
            '%d',
            '%d',
            '%d',
          );
  return insert_db_table_record($table, $data, $format);

}
endif;

//テーブルからレコードの取得
if ( !function_exists( 'get_db_table_ggp_action' ) ):
function get_db_table_ggp_action($earth_no, $team_no){
  $table = TABLE_NAME_GGP_ACTION;
  global $wpdb;
  $where = $wpdb->prepare(' WHERE earth_no LIKE %s AND team_no LIKE %s', $earth_no, $team_no);
  $query = "SELECT * FROM {$table}".
              $where.
              "ORDER BY id DESC";

  $records = $wpdb->get_results( $query );
  //_v($query);

  return $records;
}
endif;

if( !function_exists( ' allow_insert_table_ggp_action ') ):
function allow_insert_table_ggp_action($token){
  $result = get_db_table_records(TABLE_NAME_GGP_ACTION, 'token', $token);
  if(count($result) > 0 ) return false;
  return true;
}
endif;

if( !function_exists( 'check_co2_over' ) ):
function check_co2_over($earth_no, $team_no, $phase, $card_no){
  $ggp_earth = get_db_table_records_ggp(TABLE_NAME_GGP_EARTH,"earth_no",$earth_no);
  $ggp_team = get_db_table_records_ggp(TABLE_NAME_GGP_TEAM,"earth_no",$earth_no);
  $reduction_quantity_tree = (int)(get_table_ggp_action_tree($earth_no, $team_no))[0]->reduction_co2;
  $ggp_cardinfo = get_option('ggp_cardinfo');

  if($phase == 'reduction'){
    return false;
  }else if($ggp_earth[0]->co2 + $ggp_cardinfo[$phase][$card_no]['co2'] + $reduction_quantity_tree > $ggp_earth[0]->co2_quota){
    return true;
  }else if($ggp_team[$team_no]->co2 + $ggp_cardinfo[$phase][$card_no]['co2'] + $reduction_quantity_tree > $ggp_team[$team_no]->co2_quota){
    return true;
  }else{
    return false;
  }
  return true;
}
endif;

if( !function_exists( 'get_table_action_latest_id' ) ):
function get_table_action_latest_id($earth_no, $team_no){
  $table = TABLE_NAME_GGP_ACTION;
  global $wpdb;
  $query = $wpdb->prepare("SELECT MAX(id) as id FROM {$table} WHERE earth_no LIKE %s AND team_no LIKE %s", $earth_no, $team_no);
  $records = $wpdb->get_results( $query );
  return (int)$records[0]->id;
}
endif;

if( !function_exists( 'check_allow_transaction' ) ):
function check_allow_transaction($earth_no, $team_no, $id){
  $latest_id = get_table_action_latest_id($earth_no, $team_no);
  if($latest_id > $id){
    return false;
  }else if($latest_id == $id){
    return true;
  }

  return false;
}
endif;



/****************************************************************
* テーブル名：wp_ggp_action_tree
*****************************************************************/
if( !function_exists( 'create_table_ggp_action_tree' ) ):
function create_table_ggp_action_tree() {
  if (is_db_table_exist(TABLE_NAME_GGP_ACTION_TREE) ){
    return;
  }

  $sql = "CREATE TABLE " .TABLE_NAME_GGP_ACTION_TREE." (
          earth_no int,
          team_no int,
          tree_num int,
          reduction_co2 int,
          PRIMARY KEY(earth_no, team_no)
          );";
  $res = create_db_table($sql);
  return $res;
}
endif;

if( !function_exists( 'reset_table_ggp_action_tree') ):
function reset_table_ggp_action_tree(){
  uninstall_db_table(TABLE_NAME_GGP_ACTION_TREE);
  create_table_ggp_action_tree();

  $ggp_init_earth = get_option('ggp_init_earth');
  $ggp_init_perteam = get_option('ggp_init_perteam');

  for($i =0; $i < $ggp_init_earth; $i++){
  for($j = 0; $j < $ggp_init_perteam; $j++){
    $table = TABLE_NAME_GGP_ACTION_TREE;
    $data = array(
      'earth_no' => $i,
      'team_no' => $j,
      'tree_num' => 0,
      'reduction_co2' => 0
    );

    $format = array(
      '%d',
      '%d',
      '%d',
      '%d'
    );
    insert_db_table_record($table, $data, $format);
  }
  }

}
endif;

if( !function_exists(' insert_db_table_ggp_action_tree' ) ):
function update_table_ggp_action_tree($earth_no, $team_no, $reduction_tree){
  $reduction_tree_co2 = get_table_ggp_action_tree($earth_no, $team_no);
  $table = TABLE_NAME_GGP_ACTION_TREE;

  $data = array(
            'tree_num'=>(int)$reduction_tree_co2[0]->tree_num + 1,
            'reduction_co2'=>(int)$reduction_tree_co2[0]->reduction_co2 + $reduction_tree,
            );
  $where = array(
            'earth_no' => $earth_no,
            'team_no' => $team_no
            );
  $format = array(
            '%d',
            '%d'
            );
  $where_format = array(
            '%d',
            '%d'
            );
  return update_db_table_record($table, $data, $where, $format, $where_format);

}
endif;

//テーブルからレコードの取得
if ( !function_exists( 'get_table_ggp_action_tree' ) ):
function get_table_ggp_action_tree($earth_no, $team_no){
  $table = TABLE_NAME_GGP_ACTION_TREE;
  global $wpdb;
  $query = "SELECT tree_num, reduction_co2 FROM {$table} WHERE earth_no LIKE {$earth_no} AND team_no LIKE {$team_no}";

  $records = $wpdb->get_results( $query );

  return $records;
}
endif;

if ( !function_exists( 'get_table_ggp_action_tree_all' ) ):
function get_table_ggp_action_tree_all(){
  $table = TABLE_NAME_GGP_ACTION_TREE;
  global $wpdb;
  $query = "SELECT * FROM {$table}";

  $records = $wpdb->get_results( $query );

  return $records;
}
endif;


/****************************************************************
* テーブル名：wp_ggp_message
*****************************************************************/
if( !function_exists( 'create_table_ggp_message' ) ):
function create_table_ggp_message() {
  if (is_db_table_exist(TABLE_NAME_GGP_MESSAGE) ){
    return;
  }

  $sql = "CREATE TABLE " .TABLE_NAME_GGP_MESSAGE." (
          id int AUTO_INCREMENT NOT NULL PRIMARY KEY,
          earth_no int,
          msg varchar(200)
          );";
  $res = create_db_table($sql);
  return $res;
}
endif;

if( !function_exists( 'reset_table_ggp_message') ):
function reset_table_ggp_message(){
  uninstall_db_table(TABLE_NAME_GGP_MESSAGE);
  create_table_ggp_message();

}
endif;

if( !function_exists(' insert_db_table_ggp_message' ) ):
function insert_db_table_ggp_message($earth_no, $msg){
  $table = TABLE_NAME_GGP_MESSAGE;
  $data = array('earth_no'=>$earth_no,
                'msg'=>$msg
                );
  $format = array('%d','%s');
  return insert_db_table_record($table, $data, $format);

}
endif;

//テーブルからレコードの取得
if ( !function_exists( 'get_db_table_ggp_message' ) ):
function get_db_table_ggp_message($earth_no){
  $table = TABLE_NAME_GGP_MESSAGE;
  global $wpdb;
  $query = "SELECT * FROM {$table} WHERE earth_no LIKE {$earth_no} ORDER BY id DESC LIMIT 30";

  $records = $wpdb->get_results( $query );
  //_v($query);

  return $records;
}
endif;

/****************************************************************
* テーブル名：wp_ggp_action_rice
*****************************************************************/
if( !function_exists( 'create_table_ggp_action_rice' ) ):
function create_table_ggp_action_rice() {
  if (is_db_table_exist(TABLE_NAME_GGP_ACTION_RICE) ){
    return;
  }

  $sql = "CREATE TABLE " .TABLE_NAME_GGP_ACTION_RICE." (
          earth_no int NOT NULL,
          team_no int NOT NULL,
          grow int,
          to_factory int,
          make int,
          to_store int,
          PRIMARY KEY(earth_no, team_no)
          );";
  $res = create_db_table($sql);
  return $res;
}
endif;

if( !function_exists( 'reset_table_ggp_action_rice') ):
function reset_table_ggp_action_rice(){
  uninstall_db_table(TABLE_NAME_GGP_ACTION_RICE);
  create_table_ggp_action_rice();

  $ggp_init_earth = get_option('ggp_init_earth');
  $ggp_init_perteam = get_option('ggp_init_perteam');

  for($i =0; $i < $ggp_init_earth; $i++){
  for($j = 0; $j < $ggp_init_perteam; $j++){
    $table = TABLE_NAME_GGP_ACTION_RICE;
    $data = array(
      'earth_no' => $i,
      'team_no' => $j,
      'grow' => 0,
      'to_factory' => 0,
      'make' => 0,
      'to_store' => 0
    );

    $format = array(
      '%d',
      '%d',
      '%d',
      '%d',
      '%d',
      '%d'
    );
    insert_db_table_record($table, $data, $format);
  }
  }


}
endif;

//テーブルからレコードの取得
if ( !function_exists( 'get_db_table_ggp_action_rice' ) ):
function get_db_table_ggp_action_rice($earth_no, $team_no){
  $table = TABLE_NAME_GGP_ACTION_RICE;
  global $wpdb;
  $where = $wpdb->prepare(' WHERE earth_no LIKE %s AND team_no LIKE %s', $earth_no, $team_no);
  $query = "SELECT * FROM {$table}".
              $where;

  $records = $wpdb->get_results( $query );
  //_v($query);

  return $records;
}
endif;

if ( !function_exists( 'update_db_table_ggp_action_rice' ) ):
function update_db_table_ggp_action_rice($earth_no, $team_no, $phase, $quantity){
  if($phase == 'reduction') return;

  $table = TABLE_NAME_GGP_ACTION_RICE;
  $get_db_table_ggp_action_rice = get_db_table_ggp_action_rice($earth_no, $team_no);

  $where = array('earth_no' => $earth_no,
                 'team_no' => $team_no
                );
  $format = array('%d');
  $where_format = array('%d','%d');

  if($phase == 'grow'){
    $data = array('grow' => (int)$get_db_table_ggp_action_rice[0]->grow + $quantity);
    $res = update_db_table_record($table, $data, $where, $format, $where_format);
  } else if($phase == 'to_factory'){
    $data = array('to_factory' => (int)$get_db_table_ggp_action_rice[0]->to_factory + $quantity);
    $res = update_db_table_record($table, $data, $where, $format, $where_format);

    $data = array('grow'=> (int)$get_db_table_ggp_action_rice[0]->grow - $quantity);
    update_db_table_record($table, $data, $where, $format, $where_format);
  }else if ($phase == 'make'){
    $data = array('make' => (int)$get_db_table_ggp_action_rice[0]->make + $quantity);
    $res = update_db_table_record($table, $data, $where, $format, $where_format);

    $data = array('to_factory'=> (int)$get_db_table_ggp_action_rice[0]->to_factory - $quantity);
    update_db_table_record($table, $data, $where, $format, $where_format);
  }else if ($phase == 'to_store'){
    $data = array('to_store' => (int)$get_db_table_ggp_action_rice[0]->to_store + $quantity);
    $res = update_db_table_record($table, $data, $where, $format, $where_format);

    $data = array('make'=> (int)$get_db_table_ggp_action_rice[0]->make - $quantity);
    update_db_table_record($table, $data, $where, $format, $where_format);
  }else{
    var_dump("不正な値です".$phase);
    return;
  }
  return $res;
}
endif;

if( !function_exists( 'get_reduction_cardNum' ) ):
function get_reduction_cardNum($earth_no, $team_no, $key) {
  $table = TABLE_NAME_GGP_ACTION;
  global $wpdb;
  $query = "SELECT count(*) FROM {$table} WHERE keyword LIKE '{$key}' AND earth_no LIKE {$earth_no} AND team_no LIKE {$team_no}";

  $count = $wpdb->get_var( $query );

  return intval($count);

}
endif;


if( !function_exists(' update_table_ggp_options' ) ):
function update_table_ggp_options($key, $value){
  $table = TABLE_NAME_GGP_OPTIONS;

  $data = array( 'option_value'=>$value );
  $where = array( 'option_name' => $key );
  $format = array('%s');
  $where_format = array('%s');
  return update_db_table_record($table, $data, $where, $format, $where_format);

}
endif;

if( !function_exists(' reset_table_ggp_all' ) ):
function reset_table_ggp_all(){
  reset_table_ggp_earth();
  reset_table_ggp_team();
  reset_table_ggp_action();
  reset_table_ggp_action_rice();
  reset_table_ggp_action_tree();
  reset_table_ggp_message();
}
endif;

if( !function_exists(' reset_table_ggp_transaction' ) ):
function reset_table_ggp_transaction(){
  reset_table_ggp_earth();
  reset_table_ggp_team_transaction();
  reset_table_ggp_action();
  reset_table_ggp_action_rice();
  reset_table_ggp_action_tree();
  reset_table_ggp_message();
}
endif;

if( !function_exists( 'event_disaster' ) ):
function event_disaster( $earth_no, $team_no) {
  global $wpdb;
  $table = TABLE_NAME_GGP_ACTION_RICE;
  $query = $wpdb->prepare('UPDATE ' . $table . ' SET grow = 0, to_factory = 0, make = 0 WHERE earth_no = %d AND team_no = %d', $earth_no, $team_no);
  $result = $wpdb->query($query);
  return $result;
}
endif;

if( !function_exists( 'event_count_electric_car' ) ):
function event_count_electric_car($earth_no, $team_no) {
  global $wpdb;
  $table = TABLE_NAME_GGP_ACTION;
  $query = $wpdb->prepare('SELECT count(id) as count from '. $table . ' WHERE keyword = %s AND earth_no = %d AND team_no = %d','car_denki',$earth_no, $team_no);
  $result = $wpdb->get_results($query);
  return $result;

}
endif;

if( !function_exists( 'event_count_car_truck' ) ):
function event_count_car_truck($earth_no, $team_no) {
  global $wpdb;
  $table = TABLE_NAME_GGP_ACTION;
  $query = $wpdb->prepare('SELECT count(id) as count from '. $table . ' WHERE keyword = %s AND earth_no = %d AND team_no = %d','car_truck',$earth_no, $team_no);
  $result = $wpdb->get_results($query);
  return $result;

}
endif;
