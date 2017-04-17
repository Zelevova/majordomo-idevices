<?php

function messageToApple($name, $message, $sound = true, $subject = "") {
  if($message == "") {
    return 0;
  }
  require_once(ROOT.'modules/idevices/FindMyiPhone.php');
  try {
    $devices = SQLSelect("SELECT appleIDs.APPLEID, appleIDs.PASSWORD, idevices.DEVICE_ID FROM appleIDs, idevices WHERE appleIDs.APPLEID = idevices.APPLEID AND idevices.NAME =  '".$name."'");
    foreach($devices as $device) {
      $FindMyiPhone = new FindMyiPhone($device['APPLEID'], $device['PASSWORD'], false);
      $FindMyiPhone->sendMessage($device['DEVICE_ID'], $message, (bool)$sound, $subject);
    }
  } catch (exception $e) {
    registerError('idevices', 'ERROR: ' . $e->getMessage());
  }
}

function soundToApple($name, $subject = "") {
  require_once(ROOT.'modules/idevices/FindMyiPhone.php');
  try {
    $devices = SQLSelect("SELECT appleIDs.APPLEID, appleIDs.PASSWORD, idevices.DEVICE_ID FROM appleIDs, idevices WHERE appleIDs.APPLEID = idevices.APPLEID AND idevices.NAME =  '".$name."'");
    foreach($devices as $device) {
      $FindMyiPhone = new FindMyiPhone($device['APPLEID'], $device['PASSWORD'], false);
      $FindMyiPhone->playSound($device['DEVICE_ID'], $subject);
    }
  } catch (exception $e) {
    registerError('idevices', 'ERROR: ' . $e->getMessage());
  }
}

function lockToApple($name, $message, $phoneNumber = "") {
  require_once(ROOT.'modules/idevices/FindMyiPhone.php');
  try {
    $devices = SQLSelect("SELECT appleIDs.APPLEID, appleIDs.PASSWORD, idevices.DEVICE_ID FROM appleIDs, idevices WHERE appleIDs.APPLEID = idevices.APPLEID AND idevices.NAME =  '".$name."'");
    foreach($devices as $device) {
      $FindMyiPhone = new FindMyiPhone($device['APPLEID'], $device['PASSWORD'], false);
      $FindMyiPhone->lostMode($device['DEVICE_ID'], $message, $phoneNumber);
    }
  } catch (exception $e) {
    registerError('idevices', 'ERROR: ' . $e->getMessage());
  }
}

function findApple($name, $timeout = 60) {
  require_once(ROOT.'modules/idevices/FindMyiPhone.php');
  try {
    $devices = SQLSelect("SELECT appleIDs.APPLEID, appleIDs.PASSWORD, idevices.DEVICE_ID FROM appleIDs, idevices WHERE appleIDs.APPLEID = idevices.APPLEID AND idevices.NAME =  '".$name."'");
    foreach($devices as $device) {
      $FindMyiPhone = new FindMyiPhone($device['APPLEID'], $device['PASSWORD'], false);
      $location =  $FindMyiPhone->locate($device['DEVICE_ID']);
      $prop=SQLSelectOne("SELECT * FROM idevices WHERE APPLEID='".DBSafe($record['APPLEID'])."' AND DEVICE_ID='".DBSafe($device_id)."'");
      $prop['NAME'] = $device->name;
      $prop['DEVICE_ID'] = $device_id;
      $prop['APPLEID'] = $record['APPLEID'];
      $prop['BATTERY_LEVEL'] = $device->batteryLevel*100;
      $prop['BATTERY_STATUS'] = ($device->batteryStatus == "NotCharging") ? 0 : 1;
      if($location->horizontalAccuracy > 1000)
        $location =  $FindMyiPhone->locate($device_id);
      $prop['ACCURACY'] = $location->horizontalAccuracy;
      $prop['LATITUDE'] = $location->latitude;
      $prop['LONGITUDE'] = $location->longitude;
      $prop['UPDATED']=date('Y-m-d H:i:s');
      SQLUpdateInsert('idevices', $prop);
    }
  } catch (exception $e) {
    registerError('idevices', 'ERROR: ' . $e->getMessage());
  }
}

?>
