<?php
/*
*
* Default language file for iDevices module
*
*/

$dictionary=array(
'ABOUT' => 'About',
'DEBUG' => 'Debug',
'CLOSE' => 'Close',
'PAGINATION' => 'Object(s) on the Page',

'HELP' => 'Help',
'HELP_APPLEID' =>'Your Apple ID',
'HELP_PASSWORD' =>'Password from Your Apple ID',
'HELP_NAME' =>'Name',
'HELP_CHECK_INTERVAL' =>'Location Check Interval',
'HELP_BATTERY_LEVEL' =>'Current Battery Level',
'HELP_UPDATED' =>'Date and Time of the Update',
'HELP_LATITUDE' =>'Current Latitude',
'HELP_LONGITUDE' =>'Current Longitude',
'HELP_ACCURACY' =>'Current Accuracy',

'GET_DEVICES' =>'Get Devices List',
'SEND_MESSAGE' => 'Send Message',
'PLAY_SOUND' =>'Play Sound',
'LOCATE' =>'Locate',
'LOST_MODE' =>'Enable Lost Mode on this Device',
'INPUT_MESSAGE_TEXT' => 'Enter Message Text',
'AppleIDs' => 'AppleIDs List',

'IDEVICES_MAPTYPE' => 'Map type',
'IDEVICES_MAPTYPE_ROADMAP' => 'Roadmap',
'IDEVICES_MAPTYPE_SATELLITE' => 'Satellite',
'IDEVICES_MAPTYPE_HYBRID' => 'Hybrid',
'IDEVICES_MAPTYPE_TERRAIN' => 'Terrain',

'IDEVICES_CYCLE_STATE' => 'Cycle status',
'IDEVICES_CYCLE_START' => 'Cycle running',
'IDEVICES_CYCLE_STOP' => 'Cycle stopped'

);

foreach ($dictionary as $k=>$v) {
 if (!defined('LANG_'.$k)) {
  define('LANG_'.$k, $v);
 }
}

?>
