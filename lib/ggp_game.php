<?php
/** ggp_game.php
* @author arakawa asuka
* @date 2021/05/02
*/


/****************************************************************
* ボードゲーム画面を表示
****************************************************************/
add_shortcode('show_ggp_gameboard','show_ggp_gameboard');

if (! function_exists( 'show_ggp_gameboard') ):
function show_ggp_gameboard(){

$ggp_init_earth = get_option('ggp_init_earth');
$ggp_init_perteam = get_option('ggp_init_perteam');
$mode = $_POST['mode'];
$earth_no = $_POST['earth_no'];
$team_no = $_POST['team_no'];
$is_general = $_POST['is_general'];

if($is_general == NULL){
  // チームリーダ以外の一般画面を表示しない（常にenabled）
  //$_POST['is_general'] = "disabled";
  $_POST['is_general'] = "enabled";
}else if($is_general != "disabled" && $is_general != "enabled"){
  $_POST['is_general'] = "disabled";
}

if($mode == "select_boardgame" && $earth_no != NULL && $team_no != NULL){
  if(check_ggp_earth_no_validate($ggp_init_earth, $earth_no) && 
     check_ggp_team_no_validate($ggp_init_perteam, $team_no) ){
    show_ggp_gameboard_main();
  }else{
    echo ggp_error("earth_no, team_noが不正です。earth_no = {$earth_no}, team_no = {$team_no}");
    return;
  }
}
else if($mode == "select_team" && $earth_no != NULL){
  if(check_ggp_earth_no_validate($ggp_init_earth, $earth_no)){
    show_ggp_gameboard_team_select();
  }else{
    echo ggp_error("earth_noが不正です。earth_no = {$earth_no}");
    return;
  }
}
else if($mode == "select_event" && $earth_no != NULL && $team_no != NULL){
  if(check_ggp_earth_no_validate($ggp_init_earth, $earth_no) && 
     check_ggp_team_no_validate($ggp_init_perteam, $team_no) ){
    show_ggp_event();
  }else{
    echo ggp_error("earth_no, team_noが不正です。earth_no = {$earth_no}, team_no = {$team_no}");
    return;
  }
}
else{
// ショートコードは、htmlをreturnで返す必要があるため
// 編集のときに不正なjson形式と表示されてしまうため、else文は下記形式で記述
  return show_ggp_gameboard_earth_select();
}
}
endif;

if (! function_exists( 'check_ggp_earth_no_validate' ) ):
function check_ggp_earth_no_validate($ggp_init_earth, $earth_no){
  if($earth_no >= 0 && $ggp_init_earth > $earth_no){
    return true;
  }else{
    return false;
  }
}
endif;

if (! function_exists( 'check_ggp_team_no_validate' ) ):
function check_ggp_team_no_validate($ggp_init_perteam, $team_no){
  if($team_no >= 0 && $ggp_init_perteam > $team_no){
    return true;
  }else{
    return false;
  }
}
endif;


/****************************************************************
* tag: boardgame_earthselect
* 【ユーザ画面】地球の選択画面を表示
****************************************************************/
if( !function_exists('show_ggp_gameboard_earth_select') ):
function show_ggp_gameboard_earth_select(){
$ggp_init_earth = get_option('ggp_init_earth');
$ggp_init_perteam = get_option('ggp_init_perteam');
$ggp_game_reset = $_POST['ggp_game_reset'];
$is_general = $_POST['is_general'];

$ggp_team = get_table_ggp_team_all();
$token = uniqid('', true);

// 不正チェック　設定不備があった場合は画面を表示しない
if(count($ggp_team) != $ggp_init_earth*$ggp_init_perteam ){
  echo ggp_error('チーム設定が不正です。管理画面からゲームを初期化してください。');
  return;
}

if( $is_general == NULL ) $is_general = "disabled" ;

if($ggp_game_reset == "1"){
  reset_table_ggp_all();
}
// 出力のバッファリング開始
ob_start();

?>
<div align="center">
<table class="ggp_select_team">

<?php
for($i = 0; $i < $ggp_init_earth; $i++){
?>
  <form method="post" action="">
  <tr><td>
    <input type="hidden" id="earth_no" name="earth_no" value="<?=$i ?>">
    <input type="hidden" id="is_general" name="is_general" value="<?=$is_general ?>">
    <input type="hidden" id="mode" name="mode" value="select_team">
    <input class="btn-select-team" type="submit" value="ルーム No.<?=$i ?>">
  </td></tr>
  </form>
<?php 
}
if($is_general == 'disabled'){

?>
  <form method="post" action="">
  <tr><td>
    <input type="hidden" id="earth_no" name="earth_no" value="<?=$i ?>">
    <input type="hidden" id="is_general" name="is_general" value="enabled">
    <input type="submit" value="チームリーダーはこちら">
  </td></tr>
  </form>
<?php
}
?>
  <tr><td><div style="width:100%; height:100px"></div></td></tr>

  <form method="post" action="/ggp_graph/">
  <tr><td>
     <input type="submit" value="結果のグラフを表示する">
  </td></tr>
  </form>

  <tr><td><div style="width:100%; height:100px"></div></td></tr>

  <form method="post" action="">
  <tr><td>
    <input type="hidden" id="ggp_game_reset" name="ggp_game_reset" value="1">
    <input type="submit" value="ゲームをリセットする">
  </td></tr>
  </form>
  </table>
  </div>
<?php

//バッファした出力を変数に格納
$return_html = ob_get_contents();
ob_end_clean();

// Shortcodeの場合、出力されるHTMLをreturnで返す必要がある
return $return_html;

}
endif;


/****************************************************************
* tag: boardgame_teamselect
* 【ユーザ画面】チームの選択画面を表示
****************************************************************/
if( !function_exists( 'show_ggp_gameboard_team_select' ) ):
function show_ggp_gameboard_team_select(){

$ggp_team = get_table_ggp_team_all();
$ggp_init_perteam = get_option('ggp_init_perteam');
$earth_no = $_POST['earth_no'];
$is_general = $_POST['is_general'];
?>
  <div align="center">
  <table class="ggp_select_team">
<?php
  for($i = 0; $i < $ggp_init_perteam; $i++){
?>
  <form method="post" action="">
  <tr><td>
    <input type="hidden" id="earth_no" name="earth_no" value="<?=$earth_no ?>">
    <input type="hidden" id="team_no" name="team_no" value="<?= $i ?>">
    <input type="hidden" id="mode" name="mode" value="select_boardgame">
    <input type="hidden" id="is_general" name="is_general" value="<?=$is_general ?>">
    <input class="btn-select-team" type="submit" value="<?=$ggp_team[$earth_no*$ggp_init_perteam+$i]->teamname ?>">
  </td></tr>
  </form>
  <?php } ?>
  <form method="post" action="">
  <input type="hidden" id="is_general" name="is_general" value="<?=$is_general ?>">
  <tr><td></td></tr>
  <tr><td><input type="submit" value="戻る"></td></tr>
  </form>
  </table>
  </div>
<?php
}
endif;


/****************************************************************
* tag: boardgame_event
* 【ユーザ画面】イベントのルーレット画面
****************************************************************/
if( !function_exists( ' show_ggp_event' )):
function show_ggp_event(){
$earth_no = $_POST['earth_no'];
$team_no = $_POST['team_no'];
$token = $_POST['token'];
$is_general = $_POST['is_general'];
$select_no = $_POST['select_no'];
$selector = (int)$_POST['selector'];
$token_new = uniqid('', true);
$ggp_game_reset = $_POST['ggp_game_reset'];
$ggp_event_mode = $_POST['ggp_event_mode'];
$ggp_team = get_db_table_records_ggp(TABLE_NAME_GGP_TEAM,"earth_no",$earth_no);
$ggp_earth = get_db_table_records_ggp(TABLE_NAME_GGP_EARTH,"earth_no",$earth_no);
$ggp_quota_co2 = $ggp_earth[0]->co2_quota;
$show_all = false;

if($is_general == NULL)$is_general='disabled';

$ggp_event_list =  Array(
                          Array('key'=>'disaster','text'=>'台風で田んぼと倉庫がやられてしまいました。振り出しに戻る。','value'=>''),
//                          Array('key'=>'donation','text'=>'環境を大事にする団体へ500万円を寄付する。', 'value'=>500),
                          Array('key'=>'subsidy','text'=>'電気自動車を使った会社に補助金が出ることになった。電気自動車を使った回数&times;200万円をもらう。','value'=>200),
                          Array('key'=>'tax','text'=>'ガソリン車を使った会社に税金がかかることになった。ガソリン車を使った回数&times;200万円を支払う。','value'=>200),
                          Array('key'=>'emissions_trading','text'=>'二酸化炭素排出権を買う。300万円を支払って排出量上限を200kg増やす。','value'=>200)
                       );


// イベントのカードをランダムに選ぶ
if($selector == NULL ){
  $selector = rand(0, count($ggp_event_list) - 1);

//選択肢（$ggp_event_mode)と選択肢番号（$selector)が一致しない場合はエラー
}else if ($ggp_event_mode != $ggp_event_list[$selector]['key'] ){
    echo ggp_error("【イベント選択画面】不正なカードの選択です。key = " . $ggp_event_mode . ", card_no = ". $selector . "(" . $ggp_event_list[$selector]['key'] . ")");
    return;
}

// すべて表示　ボタンを押下したときの制御
if($ggp_event_mode == "show_all") $show_all = true;

if(allow_insert_table_ggp_action($token) ){
  switch( $ggp_event_mode ){
  case "disaster" :
    event_disaster($earth_no, $team_no);
    insert_db_table_ggp_message($earth_no,'【イベント】「' . $ggp_team[$team_no]->teamname . '」チームが台風で田んぼと倉庫がやられてしまいました。');
    insert_table_ggp_action($token, $earth_no, $team_no, 'event' ,'台風で田んぼと倉庫がやられてしまいました','blackbox_question_open.png','','',0,0);
    break;
  case "donation" :
    update_table_ggp_team_transaction($earth_no, $team_no, 0, 500, 0);
    insert_db_table_ggp_message($earth_no,'【イベント】「' . $ggp_team[$team_no]->teamname . '」チームが環境を大事にする団体へ寄付をしました。');
    insert_table_ggp_action($token, $earth_no, $team_no, 'event' ,'環境を大事にする団体へ寄付をしました','blackbox_question_open.png','',-500,0,0);
    break;
  case "subsidy" :
    $count =  event_count_electric_car($earth_no, $team_no);
    $count = (int)$count[0]->count;
    update_table_ggp_team_transaction($earth_no, $team_no, 0, - ($count * 200), 0);
    insert_db_table_ggp_message($earth_no,'【イベント】「' . $ggp_team[$team_no]->teamname . '」チームが電気自動車を使った分だけ補助金を受け取りました。');
    insert_table_ggp_action($token, $earth_no, $team_no, 'event' ,'補助金を受け取りました（トラック（電気自動車）を使った回数&times;200万円）','blackbox_question_open.png','',$count * 200,0,0);
    break;
  case "tax" :
    $count =  event_count_car_truck($earth_no, $team_no);
    $count = (int)$count[0]->count;
    update_table_ggp_team_transaction($earth_no, $team_no, 0, $count * 200, 0);
    insert_db_table_ggp_message($earth_no,'【イベント】「' . $ggp_team[$team_no]->teamname . '」チームがトラックを使用した分だけ税金を支払いました。');
    insert_table_ggp_action($token, $earth_no, $team_no, 'event' ,'税金を支払いました（トラック（ガソリン）を使った回数&times;200万円）','blackbox_question_open.png','',-$count * 200,0,0);
    break;
  case "emissions_trading" :
        update_table_ggp_team_transaction($earth_no, $team_no, 0, 300, 0);
        insert_db_table_ggp_message($earth_no,'【イベント】「' . $ggp_team[$team_no]->teamname . '」チームが環境対策をしました（二酸化炭素排出権を買う）。');
        insert_table_ggp_action($token, $earth_no, $team_no, 'event' ,'環境対策をしました（二酸化炭素排出を買う）','blackbox_question_open.png','',-300,0,0);
        update_table_ggp_earth_quota($earth_no, $ggp_quota_co2 + 200);
        update_table_ggp_team_co2_quota($earth_no, $team_no, $ggp_team[$team_no]->co2_quota + 200);
    break;
  case "show_all" :
  default :
    //何もしない
    break;
  }
}



?>
<div class="header-div">
  <div class="return">
    <form method="post" action="">
    <input type="hidden" name="is_general" value="<?php echo $is_general; ?>">
    <input type="hidden" name="earth_no" value="<?php echo $earth_no; ?>">
    <input type="hidden" name="token" value="<?=$token_new ?>">
    <input type="hidden" id="team_no" name="team_no" value="<?=$team_no ?>">
    <input type="hidden" id="is_general" name="is_general" value="<?=$is_general ?>">
    <input type="hidden" name="mode" value="select_boardgame">
    <input type="submit" class="btn-return" value="戻る">
    </form>
  </div>
</div>

<div align="center">
<table class="ggp_select_team">

<?php 
for( $i = 0; $i < count($ggp_event_list); $i++){
?>
  <form method="post" action="">
  <tr><td>
    <input type="hidden" id="earth_no" name="earth_no" value="<?=$earth_no ?>">
    <input type="hidden" id="team_no" name="team_no" value="<?= $team_no ?>">
    <input type="hidden" name="token" value="<?=$token_new ?>">
    <input type="hidden" id="is_general" name="is_general" value="<?=$is_general ?>">
    <input type="hidden" id="mode" name="mode" value="select_event">
    <input type="hidden" id="select_no" name="select_no" value="<?=$i?>">
    <input type="hidden" id="selector" name="selector" value="<?=$selector?>">
    <input type="hidden" id="ggp_event_mode" name="ggp_event_mode" value="<?=$ggp_event_list[$selector]['key'] ?>">
    <?php if($show_all == true){ ?> <input class="btn-select-event-selected" type="submit" value="<?=$ggp_event_list[$i]['text'] ?>" disabled>
    <?php }else if($select_no == NULL){ ?> <input class="btn-select-event" type="submit" value="No.<?=$i+1 ?> ">
    <?php }else if ($select_no != NULL && $i == $select_no){ ?> <input class="btn-select-event-selected" type="submit" value="<?=$ggp_event_list[$selector]['text'] ?>" disabled>
    <?php }else { ?><input class="btn-select-event" type="submit" value="No.<?=$i+1 ?>" disabled>
    <?php } ?>
  </td></tr>
  </form>

<?php
}
?>



  <form method="post" action="">
  <tr><td>
    <input type="hidden" id="earth_no" name="earth_no" value="<?=$earth_no ?>">
    <input type="hidden" id="team_no" name="team_no" value="<?=$team_no ?>">
    <input type="hidden" id="is_general" name="is_general" value="<?=$is_general ?>">
    <input type="hidden" id="mode" name="mode" value="select_event">
    <input type="hidden" id="ggp_event_mode" name="ggp_event_mode" value="show_all">
    <input type="submit" value="すべてのカードを表示する">
  </td></tr>
  </form>
</table>
</div>

<?php
}
endif;

/****************************************************************
* tag: boardgame
* 【ユーザ画面】ボードゲームを表示
****************************************************************/
function is_btn_valid(){
$earth_no = $_POST['earth_no'];
$team_no = $_POST['team_no'];
$is_general = $_POST['is_general'];

//各フェーズの米の量を取得
$get_db_table_ggp_action_rice = get_db_table_ggp_action_rice($earth_no, $team_no);
$after_grow_quantity =  (int)$get_db_table_ggp_action_rice[0]->grow;
$after_to_factory_quantity =  (int)$get_db_table_ggp_action_rice[0]->to_factory;
$after_make_quantity =  (int)$get_db_table_ggp_action_rice[0]->make;

//配列のコピー
$is_btn_valid = get_option('ggp_cardinfo');

foreach($is_btn_valid as $key => &$each_card){
  foreach($each_card as &$value){

    // NULL or 1 を enable, disableに置き換え
    if($value['is_valid'] == NULL ){
      $value['is_valid'] = 'disabled';
    }else{
      $value['is_valid'] = 'enabled';
    }

    // チームリーダー以外はボタンをすべて無効化
    if($is_general == 'disabled'){
      $value['is_valid'] = 'disabled';
    }
    // 生産量が０以下の場合はボタンを無効化
    else if ($key == 'to_factory' && $after_grow_quantity <= 0){
      $value['is_valid'] = 'disabled';
    }
    else if ($key == 'make' && $after_to_factory_quantity <= 0){
      $value['is_valid'] = 'disabled';
    }
    else if ($key == 'to_store' && $after_make_quantity <= 0){
      $value['is_valid'] = 'disabled';
    }
  }
}
return $is_btn_valid;

}

if( !function_exists( 'show_ggp_gameboard_main' ) ):
function show_ggp_gameboard_main(){

define('TABLE_NAME_GGP_TEAM',  $wpdb->prefix . 'ggp_team');
define('TABLE_NAME_GGP_EARTH',  $wpdb->prefix . 'ggp_earth');
$earth_no = $_POST['earth_no'];
$team_no = $_POST['team_no'];
$phase = $_POST['phase'];
$card_no = $_POST['card_no'];
$is_general = $_POST['is_general'];
$token = $_POST['token'];
$id = $_POST['id'];
$ggp_team = get_db_table_records_ggp(TABLE_NAME_GGP_TEAM,"earth_no",$earth_no);
$ggp_earth = get_db_table_records_ggp(TABLE_NAME_GGP_EARTH,"earth_no",$earth_no);
$ggp_quota_co2 = $ggp_earth[0]->co2_quota;
$ggp_quota_turn = get_option('ggp_quota_turn');
$ggp_cardinfo = get_option('ggp_cardinfo');
$ggp_init_sales = get_option('ggp_init_sales');
$ggp_init_co2_gameover = get_option('ggp_init_co2_gameover');
$ggp_init_event_tree = get_option('ggp_event_tree');
$ggp_init_event_sales = get_option('ggp_event_sales');
$ggp_event_valid_tree = $ggp_earth[0]->event_valid_tree;
$ggp_event_valid_sales = $ggp_earth[0]->event_valid_sales;
$ggp_event_tree_magnification = (double)get_option('ggp_event_tree_magnification');
$ggp_event_sales_magnification = (double)get_option('ggp_event_sales_magnification');
$ggp_reduction_valid = get_option('ggp_reduction_valid');
$token_new = uniqid('', true);
$error_msg = "";

if($is_general == NULL)$is_general='disabled';


$phase_name = Array('grow'=>'お米を育てる',
                    'to_factory'=>'お米を工場に運ぶ',
                    'make'=>'おせんべいを作る',
                    'to_store'=>'おせんべいをお店に運ぶ',
                    'reduction'=>'環境対策',
                    'sales'=>'おせんべいを売る',
                    'event'=>'イベント');

$msg_array = Array( 'grow'=>'お米を育てました',
                    'to_factory'=>'お米を田んぼから工場に運びました',
                    'make'=>'工場でおせんべいを作りました',
                    'to_store'=>'おせんべいを工場からお店に運びました',
                    'reduction'=>'環境対策をしました',
                    'tree'=>'植えた木によって二酸化炭素が減りました',
                    'sales'=>'おせんべいを売りました');
$event_arise_turn = Array(5, 10, 14);


//イベント用の変数
$event_tree_msg_in_card = "【イベント発生中】「木を植える」カードの効果が" . $ggp_event_tree_magnification ."倍になりました。";
$event_sales_msg_in_card = "【イベント発生中】売り上げが木を植えた回数&#x2613;" . (100*(double)$ggp_event_sales_magnification) ."％アップになりました。";

$event_tree_msg = "【イベント発生】「木を植える」カードの効果が" . $ggp_event_tree_magnification ."倍になりました。画面を更新してください。";
$event_sales_msg = "【イベント発生】売り上げが木を植えた回数&#x2613;" . (100*(double)$ggp_event_sales_magnification) ."％アップになりました。画面を更新してください。";
$event_cancel_msg = "【イベントキャンセル】イベントがキャンセルされました。画面を更新してください。";

$reduction_quantity_tree = (int)(get_table_ggp_action_tree($earth_no, $team_no))[0]->reduction_co2;
$tree_num = (int)(get_table_ggp_action_tree($earth_no, $team_no))[0]->tree_num;

//イベント設定のフラグ設定
if($ggp_init_event_tree == NULL || $ggp_event_valid_tree == 0){
  $ggp_event_tree_magnification = 1;
}

if($ggp_init_event_sales == NULL || $ggp_event_valid_sales == 0){
  $ggp_event_sales_magnification = 0;
}

//カードが選択された場合
if($phase != NULL && $card_no != NULL ){

if($ggp_team[$team_no]->money < $ggp_cardinfo[$phase][$card_no]['money'] ){
  //お金が足りない場合
  $error_msg = 'お金が足りません。カードを選びなおしてください。';
} else if($ggp_init_co2_gameover == NULL && check_co2_over($earth_no, $team_no, $phase, $card_no)){
  $error_msg = '二酸化炭素の上限を超えてしまいます。カードを選びなおしてください。';
//}else if( !check_allow_transaction($earth_no, $team_no, $id) ){
//  $error_msg = '不正な操作です。画面を更新してもう一度操作をしてください。';
} else if(check_allow_transaction($earth_no, $team_no, $id) && allow_insert_table_ggp_action($token)){

// 通常のカードの場合、情報を更新する
  if($phase != 'reduction'){
      //選んだカードの内容を更新する
      update_db_table_ggp_action_rice($earth_no, $team_no, $phase, $ggp_cardinfo[$phase][$card_no]['rice']);
      insert_table_ggp_action($token, $earth_no, $team_no, $phase, $ggp_cardinfo[$phase][$card_no]['name'], $ggp_cardinfo[$phase][$card_no]['url'],$ggp_cardinfo[$phase][$card_no]['key'], -$ggp_cardinfo[$phase][$card_no]['money'], $ggp_cardinfo[$phase][$card_no]['co2'],$ggp_cardinfo[$phase][$card_no]['turn']);
      update_table_ggp_earth($earth_no, $ggp_earth[0]->co2+$ggp_cardinfo[$phase][$card_no]['co2']);
      update_table_ggp_team_transaction($earth_no, $team_no, $ggp_cardinfo[$phase][$card_no]['turn'], $ggp_cardinfo[$phase][$card_no]['money'], $ggp_cardinfo[$phase][$card_no]['co2']);
      insert_db_table_ggp_message($earth_no, $ggp_team[$team_no]->turn.'ターン目 : 「'.$ggp_team[$team_no]->teamname.'」チームが'.$msg_array[$phase].'（'.$ggp_cardinfo[$phase][$card_no]['name'].'）。');

    }

// 環境対策のカードを選んだ場合
    else {
      if($ggp_cardinfo[$phase][$card_no]['key'] == 'tree'){
        //植樹 カード切ったタイミングではCo2削減は実施しない

        $tree_card_no = 0;
        // key=treeとなっているカードの番号を探索する
       for($tree_card_no = 0; $tree_card_no < count($ggp_cardinfo['reduction']); $tree_card_no++){
          if($ggp_cardinfo['reduction'][$tree_card_no]['key'] == 'tree') break;
        }

        insert_table_ggp_action($token, $earth_no, $team_no, $phase, $ggp_cardinfo[$phase][$card_no]['name'],$ggp_cardinfo[$phase][$card_no]['url'],$ggp_cardinfo[$phase][$card_no]['key'],-$ggp_cardinfo[$phase][$card_no]['money'], 0 ,$ggp_cardinfo[$phase][$card_no]['turn']);
        insert_db_table_ggp_message($earth_no, $ggp_team[$team_no]->turn.'ターン目 : 「'.$ggp_team[$team_no]->teamname.'」チームが'.$msg_array[$phase].'（'.$ggp_cardinfo[$phase][$card_no]['name'].' '.'二酸化炭素が'.$ggp_cardinfo['reduction'][$tree_card_no]['co2']*$ggp_event_tree_magnification.'kg/ターン減少）。');
        update_table_ggp_team_transaction($earth_no, $team_no, $ggp_cardinfo[$phase][$card_no]['turn'], $ggp_cardinfo[$phase][$card_no]['money'], 0);
        update_table_ggp_action_tree($earth_no, $team_no, $ggp_cardinfo['reduction'][$tree_card_no]['co2']*$ggp_event_tree_magnification);

      } else if ($ggp_cardinfo[$phase][$card_no]['key'] == 'buy'){
        //排出権購入の場合
        //CO2排出量上限の増強
        insert_table_ggp_action($token, $earth_no, $team_no, $phase, $ggp_cardinfo[$phase][$card_no]['name'],$ggp_cardinfo[$phase][$card_no]['url'],$ggp_cardinfo[$phase][$card_no]['key'],-$ggp_cardinfo[$phase][$card_no]['money'],0 ,$ggp_cardinfo[$phase][$card_no]['turn']);
        update_table_ggp_team_transaction($earth_no, $team_no, $ggp_cardinfo[$phase][$card_no]['turn'], $ggp_cardinfo[$phase][$card_no]['money'], 0);
        insert_db_table_ggp_message($earth_no, $ggp_team[$team_no]->turn.'ターン目 : 「'.$ggp_team[$team_no]->teamname.'」チームが'.$msg_array[$phase].'（'.$ggp_cardinfo[$phase][$card_no]['name'].'）。');
        update_table_ggp_earth_quota($earth_no, $ggp_quota_co2 + $ggp_cardinfo[$phase][$card_no]['co2']);
        update_table_ggp_team_co2_quota($earth_no, $team_no, $ggp_team[$team_no]->co2_quota + $ggp_cardinfo[$phase][$card_no]['co2']);
        $ggp_quota_co2 += $ggp_cardinfo[$phase][$card_no]['co2'];
        $ggp_team[$team_no]->co2_quota += $ggp_cardinfo[$phase][$card_no]['co2'];

      }
    }

    //お店に運ぶフェーズの場合、売り上げを計上
    if($phase == 'to_store'){
      update_table_ggp_team_transaction($earth_no, $team_no, 0, -$ggp_init_sales*(1+$tree_num*$ggp_event_sales_magnification) , 0);
      insert_table_ggp_action($token, $earth_no, $team_no, 'sales', $msg_array['sales'],"money.png","", $ggp_init_sales*(1+$tree_num*$ggp_event_sales_magnification),0,0);
      insert_db_table_ggp_message($earth_no, $ggp_team[$team_no]->turn.'ターン目 : 「'.$ggp_team[$team_no]->teamname.'」チームが'.$msg_array['sales'].'。');
    }

//毎ターンずつ植樹によりCo2排出量を削減する
    if($reduction_quantity_tree < 0 ){
      $ggp_earth = get_db_table_records_ggp(TABLE_NAME_GGP_EARTH,"earth_no",$earth_no);
      $tree_card_no = 0;
      // key=treeとなっているカードの番号を探索する
      for($tree_card_no = 0; $tree_card_no < count($ggp_cardinfo['reduction']); $tree_card_no++){
        if($ggp_cardinfo['reduction'][$tree_card_no]['key'] == 'tree') break;
      }
      insert_table_ggp_action($token, $earth_no, $team_no, "reduction",$msg_array['tree'], "tree_reduction_co2.png","", 0, $reduction_quantity_tree,0);
      update_table_ggp_earth($earth_no, $ggp_earth[0]->co2+$reduction_quantity_tree);
      update_table_ggp_team_transaction($earth_no, $team_no, 0, 0, $reduction_quantity_tree);
    }

  }
}


//更新後の最新情報を取得
$ggp_team = get_db_table_records_ggp(TABLE_NAME_GGP_TEAM,"earth_no",$earth_no);
$ggp_earth = get_db_table_records_ggp(TABLE_NAME_GGP_EARTH,"earth_no",$earth_no);
$ggp_message = get_db_table_ggp_message($earth_no);
$ggp_action = get_db_table_ggp_action($earth_no, $team_no);
$latest_id = get_table_action_latest_id($earth_no, $team_no);

//メッセージの1行目を取得
$ggp_message_1st = $ggp_message[0]->msg;

//各フェーズの米の量を取得
$get_db_table_ggp_action_rice = get_db_table_ggp_action_rice($earth_no, $team_no);
$after_grow_quantity =  (int)$get_db_table_ggp_action_rice[0]->grow;
$after_to_factory_quantity =  (int)$get_db_table_ggp_action_rice[0]->to_factory;
$after_make_quantity =  (int)$get_db_table_ggp_action_rice[0]->make;

//イベント発生要否を確認する
if(check_event_arise($earth_no, 1) != ""){
  if($ggp_earth[0]->event_valid_tree == 0){
    update_table_ggp_earth_event_tree($earth_no);
  }
}

if(check_event_arise($earth_no, 2) != ""){
  if($ggp_earth[0]->event_valid_sales == 0){
    update_table_ggp_earth_event_sales($earth_no);
  }
}

//イベントを実施するターンの場合ポップアップを表示する
$card_turn = get_table_ggp_earth_event_card_turn($earth_no);
error_log('turn = '. $ggp_team[$team_no]->turn . ' db = ' . $card_turn[0]->event_card_turn);
for( $i = 0; $i < count($ggp_team); $i++){
  if($i == count($ggp_team) - 1 && $card_turn[0]->event_card_turn != $ggp_team[$team_no]->turn){
    update_table_ggp_earth_event_card_turn($earth_no, $ggp_team[$i]->turn);
    insert_db_table_ggp_message($earth_no, '【イベント発生】イベントのカードを1枚選んでください。');
    break;
  }
  if($ggp_team[$i]->turn == ($event_arise_turn[0]+1) && $ggp_team[$i]->turn == $ggp_team[$i + 1]->turn){
    continue;
  }
  if($ggp_team[$i]->turn == ($event_arise_turn[1]+1) && $ggp_team[$i]->turn == $ggp_team[$i + 1]->turn){
    continue;
  }
  if($ggp_team[$i]->turn == ($event_arise_turn[2]+1) && $ggp_team[$i]->turn == $ggp_team[$i + 1]->turn){
    continue;
  }
  break;
}

//CO2排出量が地球または各チームの上限を超えたらゲームオーバーとする
if($ggp_earth[0]->co2 > $ggp_quota_co2 || $ggp_team[$team_no]->co2 > $ggp_team[$team_no]->co2_quota) {
  $_POST['is_general'] = 'disabled';
  $is_general = 'disabled';
  $error_msg = 'ゲームオーバー：二酸化炭素の上限を超えました';
?>
<style>
.header-div {
  background-color: red;
}
</style>
<?php }

//ボタンの表示・非表示のロジック
$is_btn_valid = is_btn_valid();

?>


<link href="https://use.fontawesome.com/releases/v5.6.1/css/all.css" rel="stylesheet">
<!--Chart.jsの読み込み -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.5.0/Chart.js"></script>
<div id="event_valid_tree" class="popup_msg"><?=$ggp_event_valid_tree ?></div>
<div id="event_valid_sales" class="popup_msg"><?=$ggp_event_valid_sales ?></div>
<div id="popup_msg" class="popup_msg"><?=$ggp_message[0]->msg ?></div>
<div id="popup_event_msg" class="popup_event_msg"></div>
<div id="alert_co2_msg" class="popup_msg"></div>

<!-- ***********ヘッダ部分*********** -->
<div class="header-div">
  <div class="team-name">
    <?php echo $ggp_team[$team_no]->teamname ?>
  </div>
  <div class="team-name">
    <form method="post" action="">
    <input type="hidden" name="is_general" value="<?php echo $is_general; ?>">
    <input type="hidden" name="earth_no" value="<?php echo $earth_no; ?>">
    <input type="hidden" name="team_no" value="<?php echo $team_no; ?>">
    <input type="hidden" name="mode" value="select_boardgame">
    <input type="submit" class="fas btn-reload" value="&#xf2f1 更新">
    </form>
  </div>

  <div class="team-name">
    <?php if($is_general == 'disabled'){ ?>
      <span style="font-size:0.5em; color:red;">【参照のみ】カードをえらぶことはできません</span>
    <?php ; }?>
  </div>
  <div class="return">
    <form method="post" action="">
    <input type="hidden" name="is_general" value="<?php echo $is_general; ?>">
    <input type="hidden" name="earth_no" value="<?php echo $earth_no; ?>">
    <input type="hidden" name="mode" value="select_team">
    <input type="submit" class="btn-return" value="戻る">
    </form>
  </div>
  <div class="return">
    <p style="width:20px;"></p>
  </div>
  <?php if($is_general == 'enabled'){ ?>
  <div class="return">
    <form method="post" action="">
    <input type="hidden" name="is_general" value="<?php echo $is_general; ?>">
    <input type="hidden" name="earth_no" value="<?php echo $earth_no; ?>">
    <input type="hidden" name="team_no" value="<?php echo $team_no; ?>">
    <input type="hidden" name="mode" value="select_event">
    <input type="submit" class="btn-return" value="イベント">
    </form>
  </div>
  <div class="return">
    <p style="width:20px;"></p>
  </div>
  <?php ; }?>
  <div class="return">
    <form method="post" name="timer">
      <input type="text" class="textbox-timer" value="1">分
      <input type="text" class="textbox-timer" value="0">秒
      <input type="button" class="btn-timer" id="btn-timer" value="スタート" onclick="cntStart()">
      <input type="button" class="btn-timer" value="リセット" onclick="reSet()">
      <input type="hidden" name="is_general" value="<?php echo $is_general; ?>">
      <input type="hidden" name="earth_no" value="<?php echo $earth_no; ?>">
      <input type="hidden" name="team_no" value="<?php echo $team_no; ?>">
    </form>
  </div>
</div>
<div class="header-div">
  <!--ほかのチームの情報 -->
  <div class="earth-info">
    <!-- 一覧表を表示する -->
    <div id="earth-info">
    </div>
    <!-- 地球全体の二酸化炭素排出量のグラフを表示する -->
    <div>
      <canvas id="co2_graph" height="50px" width="500px"></canvas>
    </div>
  </div>

  <!-- ***********メッセージ一覧*********** -->
  <div class="ggp-msg" id = "ggp-msg">
  <?php for($i = 0; $i < count($ggp_message); $i++) { ?>
  <p class="p-ggp-msg"><?php echo $ggp_message[$i]->msg; ?></p>
  <?php } ?>
  </div>

</div>
<hr>

<?php if($error_msg != ""){ ?>
<div class="error-msg">
<?=$error_msg ?>
</div>
<?php } ?>



<!-- ***********カード選択部分*********** -->

<p class="subtitle">カード</p>
  <table class="card_image">
    <tr>
  <?php if($ggp_reduction_valid != NULL ) { ?>
    <td width="200px" style="border-right: 1px solid #CCCCCC !important;">
      <img src="/wp-content/uploads/earth_good.png" width="160px" onclick="OnClick_reduction()"><br>
      <?=$phase_name['reduction'] ?><br>
      <input type="button" class="btn-select-phase" id="btn_reduction" value="表示" onclick="OnClick_reduction()">
    </td>
  <?php } ?>
    <td width="200px">
      <img src="/wp-content/uploads/ine.png" width="160px" onclick="OnClick_grow()"><br>
      <?=$phase_name['grow'] ?><br>
      <input type="button" class="btn-select-phase" id="btn_grow" value="表示" onclick="OnClick_grow()"></td>
    <td style="vertical-align:middle;"><div class="triangle"></div><br><?=$after_grow_quantity ?>トン</td>
    <td width="200px">
      <img src="/wp-content/uploads/to_factory.png" width="180px" onclick="OnClick_to_factory()"><br>
      <?=$phase_name['to_factory'] ?><br>
      <input type="button" class="btn-select-phase" id="btn_to_factory" value="表示" onclick="OnClick_to_factory()">
    </td>
    <td style="vertical-align:middle;"><div class="triangle"></div><br><?=$after_to_factory_quantity ?>トン</td>
    <td width="200px">
      <img src="/wp-content/uploads/factory.png" width="160px" onclick="OnClick_make()"><br>
      <?=$phase_name['make'] ?><br>
      <input type="button" class="btn-select-phase" id="btn_make" value="表示" onclick="OnClick_make()">
    </td>
    <td style="vertical-align:middle;"><div class="triangle"></div><br><?=$after_make_quantity ?>トン</td>
    <td width="200px">
      <img src="/wp-content/uploads/to_store.png" width="180px" onclick="OnClick_to_store()"><br>
      <?=$phase_name['to_store'] ?><br>
      <input type="button" class="btn-select-phase" id="btn_to_store" value="表示" onclick="OnClick_to_store()">
    </td>
  </tr>
  </table>

<!-- ***********カード詳細部分*********** -->

  <div class="card_area" id="card_area_grow">
  <p class="card_text"><?=$phase_name['grow'] ?>&nbsp;&nbsp;<font size="2em">( <i class="fas fa-coins"></i>お金 <?=$ggp_team[$team_no]->money ?>万円 <i class="fas fa-skull-crossbones"></i>二酸化炭素 <?=$ggp_team[$team_no]->co2 ?>kg/<?=$ggp_team[$team_no]->co2_quota ?>kg）</font></p>
  <a class="close-btn" onclick="OnClick_grow()" style="color:black;"><i class="fas fa-times" ></i></a>
  <?php for($i = 0; $i < count($ggp_cardinfo['grow']); $i++){
    if($ggp_cardinfo['grow'][$i]['is_visible'] == NULL) continue; ?>
    <form method="post" action="">
      <div class="card" <?=is_card_disabled($ggp_cardinfo['grow'][$i]['is_valid']) ?>>
        <input type="hidden" name="is_general" value="<?=$is_general ?>">
        <input type="hidden" name="token" value="<?=$token_new ?>">
        <input type="hidden" name="earth_no" value="<?=$earth_no ?>">
        <input type="hidden" name="mode" value="select_boardgame">
        <input type="hidden" name="id" value="<?=$latest_id ?>">
        <input type="hidden" name="team_no" value="<?=$team_no ?>">
        <input type="hidden" name="phase" value="grow">
        <input type="hidden" name="card_no" value="<?=$i ?>">
        <div class="icon"><img src="/wp-content/uploads/<?=$ggp_cardinfo['grow'][$i]['url'] ?>"></div>
        <p class="card_text"><?=$ggp_cardinfo['grow'][$i]['name'] ?></p>
        <p class="card_text"><?=$ggp_cardinfo['grow'][$i]['description'] ?></p>
        <p class="card_text"><i class="fas fa-coins"></i>&nbsp;必要なお金<br>&nbsp;<?=$ggp_cardinfo['grow'][$i]['rice'] ?>トンの米　<?=$ggp_cardinfo['grow'][$i]['money'] ?>万円</p>
        <p class="card_text"><i class="fas fa-heart"></i>&nbsp;必要なターン数<br>&nbsp;<?=$ggp_cardinfo['grow'][$i]['turn'] ?>ターン</p>
        <p class="card_text"><i class="fas fa-skull-crossbones"></i>&nbsp;二酸化炭素ガス<br>&nbsp;<?=$ggp_cardinfo['grow'][$i]['co2'] ?>kg</p>
        <div style="text-align:center;"><input class="btn-select-card" type="submit" value="<?php echo($is_btn_valid['grow'][$i]['is_valid'] == 'disabled' ? 'えらべません' :'えらぶ'); ?>" <?=$is_btn_valid['grow'][$i]['is_valid'] ?>></div>

      </div>
    </form>
  <?php } ?>
  </div>

  <div class="card_area" id="card_area_to_factory">
  <p class="card_text"><?=$phase_name['to_factory'] ?>&nbsp;&nbsp;<font size="2em">( <i class="fas fa-coins"></i>お金 <?=$ggp_team[$team_no]->money ?>万円 <i class="fas fa-skull-crossbones"></i>二酸化炭素 <?=$ggp_team[$team_no]->co2 ?>kg/<?=$ggp_team[$team_no]->co2_quota ?>kg）</font></p>
  <?php if($after_grow_quantity <= 0){ ;?><p class="card_text_err"><i class="fas fa-ban"></i>お米を育てないとこのカードはえらべません</p> <?php  };?>
  <a class="close-btn" onclick="OnClick_to_factory()" style="color:black;"><i class="fas fa-times" ></i></a>
  <?php for($i = 0; $i < count($ggp_cardinfo['to_factory']); $i++){
    if($ggp_cardinfo['to_factory'][$i]['is_visible'] == NULL) continue; ?>
    <form method="post" action="">
      <div class="card" <?=is_card_disabled($ggp_cardinfo['to_factory'][$i]['is_valid']) ?>>
        <input type="hidden" name="token" value="<?=$token_new ?>">
        <input type="hidden" name="is_general" value="<?=$is_general ?>">
        <input type="hidden" name="earth_no" value="<?=$earth_no ?>">
        <input type="hidden" name="mode" value="select_boardgame">
        <input type="hidden" name="id" value="<?=$latest_id ?>">
        <input type="hidden" name="team_no" value="<?=$team_no ?>">
        <input type="hidden" name="phase" value="to_factory">
        <input type="hidden" name="card_no" value="<?=$i ?>">
        <div class="icon"><img src="/wp-content/uploads/<?=$ggp_cardinfo['to_factory'][$i]['url'] ?>"></div>
        <p class="card_text"><?=$ggp_cardinfo['to_factory'][$i]['name'] ?></p>
        <p class="card_text"><?=$ggp_cardinfo['to_factory'][$i]['description'] ?></p>
        <p class="card_text"><i class="fas fa-coins"></i>&nbsp;必要なお金<br>&nbsp;<?=$ggp_cardinfo['to_factory'][$i]['rice'] ?>トンの米　<?=$ggp_cardinfo['to_factory'][$i]['money'] ?>万円</p>
        <p class="card_text"><i class="fas fa-heart"></i>&nbsp;必要なターン数<br>&nbsp;<?=$ggp_cardinfo['to_factory'][$i]['turn'] ?>ターン</p>
        <p class="card_text"><i class="fas fa-skull-crossbones"></i>&nbsp;二酸化炭素ガス<br>&nbsp;<?=$ggp_cardinfo['to_factory'][$i]['co2'] ?>kg</p>
        <div style="text-align:center;"><input class="btn-select-card" type="submit" value="<?php echo($is_btn_valid['to_factory'][$i]['is_valid'] == 'disabled' ? 'えらべません' :'えらぶ'); ?>" <?=$is_btn_valid['to_factory'][$i]['is_valid'] ?>></div>
      </div>
    </form>
  <?php } ?>
  </div>

  <div class="card_area" id="card_area_make">
  <p class="card_text"><?=$phase_name['make'] ?>&nbsp;&nbsp;<font size="2em">( <i class="fas fa-coins"></i>お金 <?=$ggp_team[$team_no]->money ?>万円 <i class="fas fa-skull-crossbones"></i>二酸化炭素 <?=$ggp_team[$team_no]->co2 ?>kg/<?=$ggp_team[$team_no]->co2_quota ?>kg）</font></p>
  <?php if($after_to_factory_quantity <= 0){ ;?><p class="card_text_err"><i class="fas fa-ban"></i>お米を工場に運ばないとこのカードはえらべません</p> <?php  };?>
  <a class="close-btn" onclick="OnClick_make()" style="color:black;"><i class="fas fa-times" ></i></a>
  <?php for($i = 0; $i < count($ggp_cardinfo['make']); $i++){
    if($ggp_cardinfo['make'][$i]['is_visible'] == NULL) continue; ?>
    <form method="post" action="">
      <div class="card" <?=is_card_disabled($ggp_cardinfo['make'][$i]['is_valid']) ?>>
        <input type="hidden" name="token" value="<?=$token_new ?>">
        <input type="hidden" name="is_general" value="<?=$is_general ?>">
        <input type="hidden" name="earth_no" value="<?=$earth_no ?>">
        <input type="hidden" name="mode" value="select_boardgame">
        <input type="hidden" name="id" value="<?=$latest_id ?>">
        <input type="hidden" name="team_no" value="<?=$team_no ?>">
        <input type="hidden" name="phase" value="make">
        <input type="hidden" name="card_no" value="<?=$i ?>">
        <div class="icon"><img src="/wp-content/uploads/<?=$ggp_cardinfo['make'][$i]['url'] ?>"></div>
        <p class="card_text"><?=$ggp_cardinfo['make'][$i]['name'] ?></p>
        <p class="card_text"><?=$ggp_cardinfo['make'][$i]['description'] ?></p>
        <p class="card_text"><i class="fas fa-coins"></i>&nbsp;必要なお金<br>&nbsp;<?=$ggp_cardinfo['make'][$i]['rice'] ?>トンの米　<?=$ggp_cardinfo['make'][$i]['money'] ?>万円</p>
        <p class="card_text"><i class="fas fa-heart"></i>&nbsp;必要なターン数<br>&nbsp;<?=$ggp_cardinfo['make'][$i]['turn'] ?>ターン</p>
        <p class="card_text"><i class="fas fa-skull-crossbones"></i>&nbsp;二酸化炭素ガス<br>&nbsp;<?=$ggp_cardinfo['make'][$i]['co2'] ?>kg</p>
        <div style="text-align:center;"><input class="btn-select-card" type="submit" value="<?php echo($is_btn_valid['make'][$i]['is_valid'] == 'disabled' ? 'えらべません' :'えらぶ'); ?>" <?=$is_btn_valid['make'][$i]['is_valid'] ?>></div>
      </div>
    </form>
  <?php } ?>
  </div>

  <div class="card_area" id="card_area_to_store">
  <p class="card_text"><?=$phase_name['to_store'] ?>&nbsp;&nbsp;<font size="2em">( <i class="fas fa-coins"></i>お金 <?=$ggp_team[$team_no]->money ?>万円 <i class="fas fa-skull-crossbones"></i>二酸化炭素 <?=$ggp_team[$team_no]->co2 ?>kg/<?=$ggp_team[$team_no]->co2_quota ?>kg）</font></p>
  <?php if($ggp_event_valid_sales == "1"){ ?><p class="card_text" style="color:red;"><?=$event_sales_msg_in_card ?></p><?php }; ?>
  <?php if($after_make_quantity <= 0){ ;?><p class="card_text_err"><i class="fas fa-ban"></i>おせんべいを作らないとこのカードはえらべません</p> <?php  };?>
  <a class="close-btn" onclick="OnClick_to_store()" style="color:black;"><i class="fas fa-times" ></i></a>
  <?php for($i = 0; $i < count($ggp_cardinfo['to_store']); $i++){
    if($ggp_cardinfo['to_store'][$i]['is_visible'] == NULL) continue; ?>
    <form method="post" action="">
      <div class="card" <?=is_card_disabled($ggp_cardinfo['to_store'][$i]['is_valid']) ?>>
        <input type="hidden" name="token" value="<?=$token_new ?>">
        <input type="hidden" name="is_general" value="<?=$is_general ?>">
        <input type="hidden" name="earth_no" value="<?=$earth_no ?>">
        <input type="hidden" name="mode" value="select_boardgame">
        <input type="hidden" name="id" value="<?=$latest_id ?>">
        <input type="hidden" name="team_no" value="<?=$team_no ?>">
        <input type="hidden" name="phase" value="to_store">
        <input type="hidden" name="card_no" value="<?=$i ?>">
        <div class="icon"><img src="/wp-content/uploads/<?=$ggp_cardinfo['to_store'][$i]['url'] ?>"></div>
        <p class="card_text"><?=$ggp_cardinfo['to_store'][$i]['name']; ?></p>
        <p class="card_text"><?=$ggp_cardinfo['to_store'][$i]['description'] ?></p>
        <p class="card_text"><i class="fas fa-coins"></i>&nbsp;必要なお金<br>&nbsp;<?=$ggp_cardinfo['to_store'][$i]['rice'] ?>トンの米　<?=$ggp_cardinfo['to_store'][$i]['money'] ?>万円</p>
        <p class="card_text"><i class="fas fa-heart"></i>&nbsp;必要なターン数<br>&nbsp;<?=$ggp_cardinfo['to_store'][$i]['turn'] ?>ターン</p>
        <p class="card_text"><i class="fas fa-skull-crossbones"></i>&nbsp;二酸化炭素ガス<br>&nbsp;<?=$ggp_cardinfo['to_store'][$i]['co2'] ?>kg</p>
        <div style="text-align:center;"><input class="btn-select-card" type="submit" value="<?php echo($is_btn_valid['to_store'][$i]['is_valid'] == 'disabled' ? 'えらべません' :'えらぶ'); ?>" <?=$is_btn_valid['to_store'][$i]['is_valid'] ?>></div>
      </div>
    </form>
  <?php } ?>
  </div>

  <div class="card_area" id="card_area_reduction">
  <p class="card_text"><?=$phase_name['reduction'] ?>&nbsp;&nbsp;<font size="2em">( <i class="fas fa-coins"></i>お金 <?=$ggp_team[$team_no]->money ?>万円 <i class="fas fa-skull-crossbones"></i>二酸化炭素 <?=$ggp_team[$team_no]->co2 ?>kg/<?=$ggp_team[$team_no]->co2_quota ?>kg）</font></p>
  <?php if($ggp_event_valid_tree == "1"){ ?><p class="card_text" style="color:red;"><?=$event_tree_msg_in_card ?></p><?php }; ?>

  <a class="close-btn" onclick="OnClick_reduction()" style="color:black;"><i class="fas fa-times" ></i></a>
  <?php for($i = 0; $i < count($ggp_cardinfo['reduction']); $i++){
    if($ggp_cardinfo['reduction'][$i]['is_visible'] == NULL) continue; ?>
    <form method="post" action="">
      <div class="card" <?=is_card_disabled($ggp_cardinfo['reduction'][$i]['is_valid']) ?>>
        <input type="hidden" name="token" value="<?=$token_new ?>">
        <input type="hidden" name="is_general" value="<?=$is_general; ?>">
        <input type="hidden" name="earth_no" value="<?=$earth_no ?>">
        <input type="hidden" name="mode" value="select_boardgame">
        <input type="hidden" name="id" value="<?=$latest_id ?>">
        <input type="hidden" name="team_no" value="<?=$team_no ?>">
        <input type="hidden" name="phase" value="reduction">
        <input type="hidden" name="card_no" value="<?=$i ?>">
        <div class="icon"><img src="/wp-content/uploads/<?=$ggp_cardinfo['reduction'][$i]['url'] ?>"></div>
        <p class="card_text"><?=$ggp_cardinfo['reduction'][$i]['name'] ?></p>
        <p class="card_text"><?=$ggp_cardinfo['reduction'][$i]['description'] ?></p>
        <p class="card_text"><i class="fas fa-coins"></i>&nbsp;必要なお金<br>&nbsp;<?=$ggp_cardinfo['reduction'][$i]['money'] ?>万円</p>
        <p class="card_text"><i class="fas fa-heart"></i>&nbsp;必要なターン数<br>&nbsp;<?=$ggp_cardinfo['reduction'][$i]['turn'] ?>ターン</p>
        <?php if($ggp_cardinfo['reduction'][$i]['key'] == 'tree'){ ?> <p class="card_text"><i class="fas fa-skull-crossbones"></i>&nbsp;二酸化炭素ガス<br>&nbsp;<?=$ggp_cardinfo['reduction'][$i]['co2']*$ggp_event_tree_magnification ?>kg</p> <?php } else { ?>
        <p class="card_text"><i class="fas fa-skull-crossbones"></i>&nbsp;二酸化炭素ガス<br>&nbsp;<?=$ggp_cardinfo['reduction'][$i]['co2'] ?>kg</p><?php } ?>
        <div style="text-align:center;"><input class="btn-select-card" type="submit" value="<?php echo($is_btn_valid['reduction'][$i]['is_valid'] == 'disabled' ? 'えらべません' :'えらぶ'); ?>" <?=$is_btn_valid['reduction'][$i]['is_valid'] ?>></div>
      </div>
    </form>
  <?php } ?>
  </div>



<!-- ***********行動記録*********** -->
<p class="subtitle">行動</p>
<table class="action_table">
<tr><th style="width:15%;">ターン</th><th style="width:40%;">行動</th><th style="width:15%;">お金</th><th style="width:15%;">二酸化炭素</th><th style="width:15%;">必要なターン数</th></tr>
<?php for ($i = 0 ; $i < count($ggp_action); $i++){ ?>
<tr>
<td><?php echo ($ggp_action[$i]->require_turn == 0 ? "-" : $ggp_action[$i]->turn); ?></td>
<td style="text-align:left !important;"><img class="icon-action" src="/wp-content/uploads/<?=$ggp_action[$i]->url ?>"><?php echo $phase_name[$ggp_action[$i]->phase].'('.$ggp_action[$i]->cardname.')'; ?></td>
<td><?php echo $ggp_action[$i]->money; ?>万円</td>
<td><?php echo $ggp_action[$i]->co2; ?>kg</td>
<td><?php echo $ggp_action[$i]->require_turn; ?>ターン</td>
</tr>
<?php } ?>
</table>

<script>
    document.getElementById("card_area_grow").style.display ="none";
    document.getElementById("card_area_to_factory").style.display ="none";
    document.getElementById("card_area_make").style.display ="none";
    document.getElementById("card_area_to_store").style.display ="none";
    document.getElementById("card_area_reduction").style.display ="none";

function OnClick_grow(){
  const p = document.getElementById("card_area_grow");
  const btn = document.getElementById("btn_grow");
  if(p.style.display=="block"){
    // noneで非表示
    p.style.display ="none";
    btn.value="表示";
  }else{
    // blockで表示
    p.style.display ="block";
    btn.value="非表示";
    document.getElementById("card_area_to_factory").style.display ="none";
    document.getElementById("card_area_make").style.display ="none";
    document.getElementById("card_area_to_store").style.display ="none";
    document.getElementById("card_area_reduction").style.display ="none";
    document.getElementById("btn_to_factory").value ="表示";
    document.getElementById("btn_make").value ="表示";
    document.getElementById("btn_to_store").value ="表示";
    document.getElementById("btn_reduction").value ="表示";
  }
}

function OnClick_to_factory(){
  const p = document.getElementById("card_area_to_factory");
  const btn = document.getElementById("btn_to_factory");
  if(p.style.display=="block"){
    // noneで非表示
    p.style.display ="none";
    btn.value="表示";
  }else{
    // blockで表示
    p.style.display ="block";
    btn.value="非表示";
    document.getElementById("card_area_grow").style.display ="none";
    document.getElementById("card_area_make").style.display ="none";
    document.getElementById("card_area_to_store").style.display ="none";
    document.getElementById("card_area_reduction").style.display ="none";
    document.getElementById("btn_grow").value ="表示";
    document.getElementById("btn_make").value ="表示";
    document.getElementById("btn_to_store").value ="表示";
    document.getElementById("btn_reduction").value ="表示";
  }
}

function OnClick_make(){
  const p = document.getElementById("card_area_make");
  const btn = document.getElementById("btn_make");
  if(p.style.display=="block"){
    // noneで非表示
    p.style.display ="none";
    btn.value="表示";
  }else{
    // blockで表示
    p.style.display ="block";
    btn.value="非表示";
    document.getElementById("card_area_grow").style.display ="none";
    document.getElementById("card_area_to_factory").style.display ="none";
    document.getElementById("card_area_to_store").style.display ="none";
    document.getElementById("card_area_reduction").style.display ="none";
    document.getElementById("btn_grow").value ="表示";
    document.getElementById("btn_to_factory").value ="表示";
    document.getElementById("btn_to_store").value ="表示";
    document.getElementById("btn_reduction").value ="表示";
  }
}

function OnClick_to_store(){
  const p = document.getElementById("card_area_to_store");
  const btn = document.getElementById("btn_to_store");
  if(p.style.display=="block"){
    // noneで非表示
    p.style.display ="none";
    btn.value="表示";
  }else{
    // blockで表示
    p.style.display ="block";
    btn.value="非表示";
    document.getElementById("card_area_grow").style.display ="none";
    document.getElementById("card_area_to_factory").style.display ="none";
    document.getElementById("card_area_make").style.display ="none";
    document.getElementById("card_area_reduction").style.display ="none";
    document.getElementById("btn_grow").value ="表示";
    document.getElementById("btn_to_factory").value ="表示";
    document.getElementById("btn_make").value ="表示";
    document.getElementById("btn_reduction").value ="表示";
  }
}

function OnClick_reduction(){
  const p = document.getElementById("card_area_reduction");
  const btn = document.getElementById("btn_reduction");
  if(p.style.display=="block"){
    // noneで非表示
    p.style.display ="none";
    btn.value="表示";
  }else{
    // blockで表示
    p.style.display ="block";
    btn.value="非表示";
    document.getElementById("card_area_grow").style.display ="none";
    document.getElementById("card_area_to_factory").style.display ="none";
    document.getElementById("card_area_make").style.display ="none";
    document.getElementById("card_area_to_store").style.display ="none";
    document.getElementById("btn_grow").value ="表示";
    document.getElementById("btn_to_factory").value ="表示";
    document.getElementById("btn_make").value ="表示";
    document.getElementById("btn_to_store").value ="表示";
  }
}

// 初回にjavascript関数を実行する
window.addEventListener('load',co2_graph(<?=$ggp_earth[0]->co2 ?>, <?=$ggp_quota_co2 ?>));
window.addEventListener('load',get_ggp_earth_info_ajax());
window.addEventListener('load',check_ggp_event_tree_ajax("1"));
window.addEventListener('load',check_ggp_event_sales_ajax("1"));

function co2_graph(co2, ggp_quota_co2) {
let co2_graph_green = [];
let co2_graph_yellow = [];
let co2_graph_red = [];
co2_graph_green.push(0);
co2_graph_yellow.push(0);
co2_graph_red.push(0);

if( co2 / ggp_quota_co2 <= 0.5 ) {
   co2_graph_green[0] = co2;
}else if( co2 / ggp_quota_co2 <= 0.8) {
   co2_graph_green[0] = ggp_quota_co2*0.5;
   co2_graph_yellow[0] = co2 - co2_graph_green;
}else{
   co2_graph_green[0] = ggp_quota_co2*0.5;
   co2_graph_yellow[0] = ggp_quota_co2*0.3;
   co2_graph_red[0] = co2 - co2_graph_green - co2_graph_yellow;
}

    var ctx = document.getElementById("co2_graph").getContext('2d');
    window.myChart = new Chart(ctx, {
        type: "horizontalBar",
        data: {
            labels:  ["二酸化炭素("+co2+"kg/"+ggp_quota_co2+"kg)"],
            datasets: [
                {
                    label: "green",
                    data: co2_graph_green,
                    backgroundColor: "#00B06B"
                },
                {
                    label: "yellow",
                    data: co2_graph_yellow,
                    backgroundColor: "#F2E700"
                },
                {
                    label: "red",
                    data: co2_graph_red,
                    backgroundColor: "#FF4B00"
                }
            ]
        },
        options: {
          animation: {
            duration: 0
          },
          tooltips: {
            enabled: false
          },
          responsive: false,
          title: {
            display: false,
            fontSize: 20,
            text: "二酸化炭素"
          },
          legend: {
            display : false
          },
          scales: {
            xAxes: [
              {
                stacked: true,  // 積み上げの指定
                ticks: {
                  min: 0,
                  max:<?=$ggp_quota_co2 ?>,
                  stepSize:200
                }
              }
            ],
            yAxes: [
              {
                stacked: true  //  積み上げの指定
              }
            ]
          }
        }
    });
}

function co2_graph_reload(co2, ggp_quota_co2) {
let co2_graph_green = 0;
let co2_graph_yellow = 0;
let co2_graph_red = 0;

if( co2 / ggp_quota_co2 <= 0.5 ) {
   co2_graph_green = co2;
}else if( co2 / ggp_quota_co2 <= 0.8) {
   co2_graph_green = ggp_quota_co2*0.5;
   co2_graph_yellow = co2 - co2_graph_green;
}else{
   co2_graph_green = ggp_quota_co2*0.5;
   co2_graph_yellow = ggp_quota_co2*0.3;
   co2_graph_red = co2 - co2_graph_green - co2_graph_yellow;
}

myChart.data.datasets[0].data[0] = co2_graph_green;
myChart.data.datasets[1].data[0] = co2_graph_yellow;
myChart.data.datasets[2].data[0] = co2_graph_red;
myChart.data.labels[0] = "二酸化炭素("+co2+"kg/"+ggp_quota_co2+"kg)";

myChart.update({duration: 0});

}

// 定期的（5000ms）に関数を実行する
window.addEventListener('load', function () {
    setInterval( function() {
                  get_ggp_msg_ajax();
                  get_ggp_earth_co2_ajax();
                  get_ggp_earth_info_ajax();
                  get_ggp_msg_newest_ajax();
                  check_ggp_event_tree_ajax("0");
                  check_ggp_event_sales_ajax("0");
                  }, 5000);
}
);

// ajax（非同期通信）各チームの行動一覧を取得する
function get_ggp_msg_ajax() {
let ajaxUrl = '<?php echo esc_url( admin_url( 'admin-ajax.php', __FILE__ ) ); ?>';

$.ajax({
  type: 'POST',
  url: ajaxUrl,
  data: {
    'action' : 'get_ggp_msg_ajax',
    'earth_no' : '<?=$earth_no ?>',
    'nonce': '<?php echo wp_create_nonce( 'get_ggp_msg-nonce' ); ?>'
  },
  success: function( response ) {
    document.getElementById('ggp-msg').innerHTML = response;
  }
});
}

// ajax（非同期通信）各チームがActionを起こしたメッセージを取得する
function get_ggp_msg_newest_ajax() {
let ajaxUrl = '<?php echo esc_url( admin_url( 'admin-ajax.php', __FILE__ ) ); ?>';

$.ajax({
  type: 'POST',
  url: ajaxUrl,
  data: {
    'action' : 'get_ggp_msg_newest_ajax',
    'earth_no' : '<?=$earth_no ?>',
    'nonce': '<?php echo wp_create_nonce( 'get_ggp_msg_newest-nonce' ); ?>'
  },
  success: function( response ) {
    if( document.getElementById('popup_msg').innerHTML != response){
      document.getElementById('popup_msg').innerHTML = response;
      if(response == ""){
        document.getElementById('popup_msg').innerHTML = "ゲームが初期化されました。";
        window.setTimeout(window_assign,3000);
      }
      popup_msg();
    }
  }
});
}

// ajax（非同期通信）植樹効果2倍のイベントが発生しているか否かを取得する
function check_ggp_event_tree_ajax( initial ) {
let ajaxUrl = '<?php echo esc_url( admin_url( 'admin-ajax.php', __FILE__ ) ); ?>';

$.ajax({
  type: 'POST',
  url: ajaxUrl,
  data: {
    'action' : 'check_ggp_event_tree_ajax',
    'earth_no' : '<?=$earth_no ?>',
    'nonce': '<?php echo wp_create_nonce( 'check_ggp_event_tree-nonce' ); ?>'
  },
  success: function( response ) {
    if(response != document.getElementById('event_valid_tree').innerHTML){
      document.getElementById("event_valid_tree").innerHTML = response;
      document.getElementById("popup_event_msg").style.display = "block";
      if(response != "0" ){
        document.getElementById("popup_event_msg").innerHTML = "<?=$event_tree_msg ?>";
      }else{
        document.getElementById("popup_event_msg").innerHTML = "<?=$event_cancel_msg ?>";
      }
      window.setTimeout(window_reload, 10000);
    }
  }
});
}

// ajax（非同期通信）売り上げ2倍のイベントが発生しているか否かを取得する
function check_ggp_event_sales_ajax( initial ) {
let ajaxUrl = '<?php echo esc_url( admin_url( 'admin-ajax.php', __FILE__ ) ); ?>';

$.ajax({
  type: 'POST',
  url: ajaxUrl,
  data: {

    'action' : 'check_ggp_event_sales_ajax',
    'earth_no' : '<?=$earth_no ?>',
    'nonce': '<?php echo wp_create_nonce( 'check_ggp_event_sales-nonce' ); ?>'
  },
  success: function( response ) {
    if(response != document.getElementById('event_valid_sales').innerHTML){
      document.getElementById("event_valid_sales").innerHTML = response;
      document.getElementById("popup_event_msg").style.display = "block";
      if(response != "0" ){
        document.getElementById("popup_event_msg").innerHTML = "<?=$event_sales_msg ?>";
      }else{
        document.getElementById("popup_event_msg").innerHTML = "<?=$event_cancel_msg ?>";
      }
      window.setTimeout(window_reload, 10000);
    }
  }
});
}

function window_reload(){
  location.reload();
}

function window_assign(){
  location.assign("/");
}

// ajax（非同期通信）各チームの数値（残金、ターン数、Co2排出量）の更新
function get_ggp_earth_info_ajax() {
let ajaxUrl = '<?php echo esc_url( admin_url( 'admin-ajax.php', __FILE__ ) ); ?>';

$.ajax({
  type: 'POST',
  url: ajaxUrl,
  data: {
    'action' : 'get_ggp_earth_info_ajax',
    'earth_no' : '<?=$earth_no ?>',
    'nonce': '<?php echo wp_create_nonce( 'get_ggp_earth_info-nonce' ); ?>'
  }
  }).done(function( response ) {
    document.getElementById('earth-info').innerHTML = response;
  })
}

// ajax（非同期通信）　地球全体のCo2グラフの更新
function get_ggp_earth_co2_ajax() {
let ajaxUrl = '<?php echo esc_url( admin_url( 'admin-ajax.php', __FILE__ ) ); ?>';

$.ajax({
  type: 'POST',
  url: ajaxUrl,
  data: {
    'action' : 'get_ggp_earth_co2_ajax',
    'earth_no' : '<?=$earth_no ?>',
    'nonce': '<?php echo wp_create_nonce( 'get_ggp_earth_co2-nonce' ); ?>'
  },
  dataType: 'json'
  }).done(function( response ) {
    co2_graph_reload(response.co2, response.quota_co2);
  })
}


/* カウントダウンタイマー */
var timer1; //タイマーを格納する変数（タイマーID）の宣言
var timer_status = 0;

//カウントダウン関数を1000ミリ秒毎に呼び出す関数
function cntStart()
{

  if(timer_status == 0) {
    timer_status = 1;
    document.getElementById("btn-timer").value ="ストップ";
    timer1=setInterval("countDown()",1000);
  } else {
    timer_status = 0;
    document.getElementById("btn-timer").value ="スタート";
    clearInterval(timer1);
  }

}

//タイマー停止関数
function cntStop()
{
  document.getElementById("btn-timer").value ="スタート";
  clearInterval(timer1);
}

//カウントダウン関数
function countDown()
{
  var min=document.timer.elements[0].value;
  var sec=document.timer.elements[1].value;

  if( (min=="") && (sec=="") )
  {
    alert("時刻を設定してください！");
    reSet();
  }
  else
  {
    if (min=="") min=0;
    min=parseInt(min);

    if (sec=="") sec=0;
    sec=parseInt(sec);

    tmWrite(min*60+sec-1);
  }
}

//残り時間を書き出す関数
function tmWrite(int)
{
  int=parseInt(int);

  if (int<=0)
  {
    reSet();
    alert("時間です！");
  }
  else
  {
    //残り分数はintを60で割って切り捨てる
    document.timer.elements[0].value=Math.floor(int/60);
    //残り秒数はintを60で割った余り
    document.timer.elements[1].value=int % 60;
  }
}

//フォームを初期状態に戻す（リセット）関数
function reSet()
{
  document.timer.elements[0].value="1";
  document.timer.elements[1].value="0";
  document.timer.elements[2].disabled=false;
  document.getElementById("btn-timer").value ="スタート";
  clearInterval(timer1);
}

//他チームのActionが発生したらポップアップを表示する
function popup_msg(){
    // ツールチップの class="invisible" を削除
    document.getElementById("popup_msg").style.display = "block";

  // 表示されたツールチップを隠す処理（ツールチップ領域以外をマウスクリックで隠す）
    $('html').mousedown(function(event){
        // イベント発生源である要素を取得（クリックされた要素を取得）
        var clickedElement = event.target;
        // どの要素をクリックしても非表示とする
        document.getElementById("popup_msg").style.display ="none";

        // 取得要素が"toolTip"クラスを持ってなかったら、ツールチップを非表示に
//        if(!$(clickedElement).hasClass('popup_msg')){
//            document.getElementById("popup_msg").style.display ="none";
//        }
    });
}

//他チームのActionが発生したらポップアップを表示する
function alert_co2_msg(){

    // ツールチップの class="invisible" を削除
    document.getElementById("alert_co2_msg").style.display = "block";

  // 表示されたツールチップを隠す処理（ツールチップ領域以外をマウスクリックで隠す）
    $('html').mousedown(function(event){
        // イベント発生源である要素を取得（クリックされた要素を取得）
        var clickedElement = event.target;
        // どの要素をクリックしても非表示とする
        document.getElementById("alert_co2_msg").style.display ="none";

        // 取得要素が"toolTip"クラスを持ってなかったら、ツールチップを非表示に
//        if(!$(clickedElement).hasClass('alert_co2_msg')){
//            document.getElementById("alert_co2_msg").style.display ="none";
//        }
    });
}

<?php
//地球のCo2、各チームのCo2が上限の90%を超えたらアラートをあげる
if((float)$ggp_earth[0]->co2 > (float)$ggp_quota_co2*0.8 && (float)$ggp_team[$team_no]->co2 > (float)$ggp_team[$team_no]->co2_quota * 0.8){
?>
  document.getElementById('alert_co2_msg').innerHTML = "チーム／全体の二酸化炭素の量が多すぎます！環境対策をしてください。";
  alert_co2_msg();
<?php
}
else if ((float)$ggp_earth[0]->co2 > (float)$ggp_quota_co2 * 0.8){
?>
  document.getElementById('alert_co2_msg').innerHTML = "全体の二酸化炭素の量が多すぎます！環境対策をしてください。";
  alert_co2_msg();
<?php
}
else if ((float)$ggp_team[$team_no]->co2 > (float)$ggp_team[$team_no]->co2_quota * 0.8){
?>
  document.getElementById('alert_co2_msg').innerHTML = "チームの二酸化炭素の量が多すぎます！環境対策をしてください。";
  alert_co2_msg();
<?php
}
?>

</script>

<?php 
}
endif;



