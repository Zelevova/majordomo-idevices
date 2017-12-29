<?php
  
  function messageToApple($name, $message, $sound = true, $subject = "") {
    if($message == "")
      return 0;
    include_once(DIR_MODULES . 'idevices/idevices.class.php');
    $idevices_module = new idevices();
    $idevices_module->sendMessage($name, $message, $subject, $sound);
  }
  
  function soundToApple($name, $message = "") {
    include_once(DIR_MODULES . 'idevices/idevices.class.php');
    $idevices_module = new idevices();
    $idevices_module->playSound($name, $message]);
  }
  
  function lockToApple($name, $message, $phoneNumber = "") {
    include_once(DIR_MODULES . 'idevices/idevices.class.php');
    $idevices_module = new idevices();
    $idevices_module->lostMode($name, $message, $phoneNumber);
  }
  
  function findApple($name, $timeout = 0) {
    include_once(DIR_MODULES . 'idevices/idevices.class.php');
    $idevices_module = new idevices();
    $idevices_module->locate($name);
  }
  
?>
