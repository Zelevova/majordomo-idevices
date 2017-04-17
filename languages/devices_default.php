<?php
/*
*
* English language file for iDevices module
*
*/
$dictionary=array(
/* general */
'HELP'=>'Help',
'APPLE_ID'=>'Apple ID',
'PASSWORD'=>'Password'
/* end module names */
);
foreach ($dictionary as $k=>$v) {
 if (!defined('LANG_'.$k)) {
  define('LANG_'.$k, $v);
 }
}
?>
