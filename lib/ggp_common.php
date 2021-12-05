<?php
/** ggp_common.php
* @author arakawa asuka
* @date 2021/11/26
*/

require_once(get_stylesheet_directory().'/lib/db.php');


if (! function_exists( 'ggp_error' ) ):
function ggp_error($message, $dump = NULL){
session_start();

$_SESSION['error_message'] = $message;
if(isset($dump) ) $_SESSION['dump'] = $dump;
header('Location: /error', true , 302);
exit;

ob_start();
?>
<h1> 内部エラー</h1>
<?=$message ?>
<br>

<h1>var_dump</h1>
<?php var_dump($dump); ?>

<?php

//バッファした出力を変数に格納
$return_html = ob_get_contents();
ob_end_clean();

// Shortcodeの場合、出力されるHTMLをreturnで返す必要がある
return $return_html;

}
endif;



