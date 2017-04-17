<?php

 $record=SQLSelectOne("SELECT * FROM appleIDs WHERE ID='".(int)$id."'");
 require_once('FindMyiPhone.php');
 try {
  $FindMyiPhone = new FindMyiPhone($record['APPLEID'], $record['PASSWORD'], false);
  $FindMyiPhone->getDevices();
  foreach ($FindMyiPhone->devices as $device_id => $device)
  {
   $location =  $FindMyiPhone->locate($device_id);
   if($location->horizontalAccuracy > 1000)
    $location =  $FindMyiPhone->locate($device_id);
   $prop=SQLSelectOne("SELECT * FROM idevices WHERE APPLEID='".DBSafe($record['APPLEID'])."' AND DEVICE_ID='".DBSafe($device_id)."'");
   $prop['NAME'] = $device->name;
   $prop['DEVICE_ID'] = $device_id;
   $prop['APPLEID'] = $record['APPLEID'];
   $prop['GET_LOCATION'] = 30;
   $prop['BATTERY_LEVEL'] = $device->batteryLevel*100;
   $prop['BATTERY_STATUS'] = ($device->batteryStatus == "NotCharging") ? 0 : 1;
   $prop['ACCURACY'] = $location->horizontalAccuracy;
   $prop['LATITUDE'] = $location->latitude;
   $prop['LONGITUDE'] = $location->longitude;
   $prop['UPDATED']=date('Y-m-d H:i:s');
   SQLUpdateInsert('idevices', $prop);
   if(file_exists(DIR_MODULES.'app_gpstrack/installed')) {
    $_REQUEST['deviceid']  = $prop['NAME'];
    $_REQUEST['battlevel'] = $prop['BATTERY_LEVEL'];
    $_REQUEST['charging'] = $prop['BATTERY_STATUS'];
    $_REQUEST['latitude']  = $prop['LATITUDE'];
    $_REQUEST['longitude'] = $prop['LONGITUDE'];
    $_REQUEST['accuracy'] = $prop['ACCURACY'];
    include_once('./gps.php');
   }
  }
  unset($device);
 } catch (exception $e) {
  registerError('idevices', 'ERROR: ' . $e->getMessage());
 }

?>
