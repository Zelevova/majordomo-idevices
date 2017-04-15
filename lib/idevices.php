<?php

function messageToApple($name, $message, $sound = true, $subject = "") {
  if($message == "") {
		return 0;
  }
  require_once(ROOT.'modules/idevices/FindMyiPhone.php');
	try {
    $devices = SQLSelect('SELECT `appleIDs`.`APPLEID`, `appleIDs`.`PASSWORD`, `idevices`.`device_ID`
FROM `appleIDs`, `idevices`
WHERE `appleIDs`.`APPLEID` = `idevices`.`APPLEID`
 AND `idevices`.`NAME` =  '.$name.'');
    foreach($devices as $device)
		$FindMyiPhone = new FindMyiPhone($device['APPLEID'], $device['PASSWORD'], TRUE);
		$FindMyiPhone->sendMessage($device['device_id'], $message, (bool)$sound, $subject);
	} catch (exception $e) {
		echo 'ERROR: ' . $e->getMessage() ;
	}
}

function soundToApple($device, $subject = "") {
}

function lockToApple($device, $message, $phoneNumber = "") {
}

function findApple($device, $timeout = 60) {
  
}

?>
