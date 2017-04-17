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
      if($location->horizontalAccuracy > 1000)
        $location =  $FindMyiPhone->locate($device['DEVICE_ID']);
      $prop=SQLSelectOne("SELECT * FROM idevices WHERE APPLEID='".DBSafe($device['APPLEID'])."' AND DEVICE_ID='".DBSafe($device['DEVICE_ID'])."'");
      $prop['NAME'] = $FindMyiPhone->devices[$device['DEVICE_ID']]->name;
      $prop['DEVICE_ID'] = $device['DEVICE_ID'];
      $prop['APPLEID'] = $device['APPLEID'];
      $prop['BATTERY_LEVEL'] = $FindMyiPhone->devices[$device['DEVICE_ID']]->batteryLevel*100;
      $prop['BATTERY_STATUS'] = ($FindMyiPhone->devices[$device['DEVICE_ID']]->batteryStatus == "NotCharging") ? 0 : 1;
      $prop['ACCURACY'] = $location->horizontalAccuracy;
      $prop['LATITUDE'] = $location->latitude;
      $prop['LONGITUDE'] = $location->longitude;
      $prop['UPDATED']=date('Y-m-d H:i:s');
      SQLUpdateInsert('idevices', $prop);
      require_once(ROOT.'modules/idevices/toGPS.php');
      AppleToGPS($prop);
    }
  } catch (exception $e) {
    registerError('idevices', 'ERROR: ' . $e->getMessage());
  }
}

?>
