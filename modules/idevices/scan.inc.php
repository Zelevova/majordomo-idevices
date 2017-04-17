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
   require_once('toGPS.php');
   AppleToGPS($prop);
  }
  unset($device);
 } catch (exception $e) {
  registerError('idevices', 'ERROR: ' . $e->getMessage());
 }

?>
