<?php
/** ggp_testcode.php
* @author arakawa asuka
* @date 2021/11/26
*/

add_shortcode('ggp_testcode','ggp_testcode');
require_once(get_stylesheet_directory().'/lib/ggp_common.php');

if (! function_exists( 'ggp_testcode' ) ):
function ggp_testcode(){

ob_start();
?>
<div align="center">
<table class="ggp_select_team">
  <form method="post" action="/">
  <tr><td>
    <input type="hidden" id="earth_no" name="earth_no" value="1">
    <input type="hidden" id="team_no" name="team_no" value="2">
    <input type="hidden" id="select_no" name="select_no" value="6">
    <input type="hidden" id="selector" name="selector" value="8">
    <input type="hidden" id="ggp_event_mode" name="ggp_event_mode" value="tax">
    <input type="hidden" id="is_general" name="is_general" value="enabled">
    <input type="hidden" id="mode" name="mode" value="select_event">
    <input class="btn-select-team" type="submit" value="テストコード実行">
  </td></tr>
  </form>
</table>
</div>

<?php
//バッファした出力を変数に格納
$return_html = ob_get_contents();
ob_end_clean();

return $return_html;

}
endif;

add_shortcode('error','error');

if (! function_exists( 'error' ) ):
function error(){
session_start();
$message = $_SESSION['error_message'];
$dump = $_SESSION['dump'];

unset($_SESSION['error_message']);
unset($_SESSION['dump']);


ob_start();
?>
<?php
echo $message;
if(isset($dump) )var_dump($dump);

//バッファした出力を変数に格納
$return_html = ob_get_contents();
ob_end_clean();

return $return_html;

}
endif;


