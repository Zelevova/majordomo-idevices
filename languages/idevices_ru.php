<?php
/*
*
* Russian language file for iDevices module
*
*/
$dictionary=array(
/* general */
'HELP'=>'Помощь',
'APPLE_ID'=>'Apple ID',
'PASSWORD'=>'Пароль'
/* end module names */
);
foreach ($dictionary as $k=>$v) {
 if (!defined('LANG_'.$k)) {
  define('LANG_'.$k, $v);
 }
}
?>
