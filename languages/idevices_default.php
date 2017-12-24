<?php
/*
*
* Default language file for iDevices module
*
*/

$dictionary=array(
'ABOUT' => 'About',
'HELP' => 'Help',
'DEBUG' => 'Debug',
'CLOSE' => 'Close',
                  'HELP_APPLEID' =>'Apple ID',
'HELP_PASSWORD' =>'Password',
'SEND_MESSAGE' => 'Send message',
'INPUT_MESSAGE_TEXT' => 'Input text message',
'AppleIDs' => 'AppleIDs'
);

foreach ($dictionary as $k=>$v) {
 if (!defined('LANG_'.$k)) {
  define('LANG_'.$k, $v);
 }
}

?>
