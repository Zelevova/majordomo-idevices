<?php

function AppletoGPS($idevice)
{
  if(file_exists(DIR_MODULES.'app_gpstrack/installed')) {
    $_REQUEST['deviceid']  = $idevice['NAME'];
    $_REQUEST['latitude']  = $idevice['LATITUDE'];
    $_REQUEST['longitude'] = $idevice['LONGITUDE'];
    $_REQUEST['battlevel'] = $idevice['BATTERY_LEVEL'];
    $_REQUEST['charging'] = $idevice['BATTERY_STATUS'];
    $_REQUEST['accuracy'] = $idevice['ACCURACY'];
    include_once('./gps.php');
  }
}

?>
