<?php

/**
 * iDevices
 *
 *
 * @package project
 * @author ZeleVova <zelevova@gmail.com>
 * @copyright zelevova (c)
 * @version 2.1
 */
//
//
require_once(DIR_MODULES . 'idevices/FindMyiPhone.php');

class idevices extends module {
  private $deep_debug = false;
  private $current_AppleID = '';
  private $FindMyiPhone;
  
  /**
   * idevices
   *
   * Module class constructor
   *
   * @access private
   */
  function __construct() {
    $this->name="idevices";
    $this->title="iDevices";
    $this->module_category="<#LANG_SECTION_DEVICES#>";
    $this->checkInstalled();
  }
  
  /**
   * idevices
   *
   * Module class constructor
   *
   * @access private
   */
  function initClient($device_id) {
    $device = SQLSelectOne("SELECT appleIDs.APPLEID, appleIDs.PASSWORD, idevices.DEVICE_ID, idevices.NAME FROM appleIDs, idevices WHERE appleIDs.APPLEID = idevices.APPLEID AND (idevices.DEVICE_ID = '".DBSafe($device_id)."' OR idevices.NAME = '".DBSafe($device_id)."')");
    if ($this->current_AppleID != $device['APPLEID']) {
      $this->current_AppleID = $device['APPLEID'];
      $this->FindMyiPhone = new FindMyiPhone($device['APPLEID'], $device['PASSWORD'], $this->deep_debug);
    }
    return $device;
  }
  
  /**
   * saveParams
   *
   * Saving module parameters
   *
   * @access public
   */
  function saveParams($data=0) {
    $p=array();
    if (IsSet($this->id)) {
      $p["id"]=$this->id;
    }
    if (IsSet($this->view_mode)) {
      $p["view_mode"]=$this->view_mode;
    }
    if (IsSet($this->edit_mode)) {
      $p["edit_mode"]=$this->edit_mode;
    }
    if (IsSet($this->data_source)) {
      $p["data_source"]=$this->data_source;
    }
    if (IsSet($this->tab)) {
      $p["tab"]=$this->tab;
    }
    return parent::saveParams($p);
  }
  /**
   * getParams
   *
   * Getting module parameters from query string
   *
   * @access public
   */
  function getParams() {
    global $id;
    global $mode;
    global $view_mode;
    global $edit_mode;
    global $data_source;
    global $tab;
    if (isset($id)) {
      $this->id=$id;
    }
    if (isset($mode)) {
      $this->mode=$mode;
    }
    if (isset($view_mode)) {
      $this->view_mode=$view_mode;
    }
    if (isset($edit_mode)) {
      $this->edit_mode=$edit_mode;
    }
    if (isset($data_source)) {
      $this->data_source=$data_source;
    }
    if (isset($tab)) {
      $this->tab=$tab;
    }
  }
  /**
   * Run
   *
   * Description
   *
   * @access public
   */
  function run() {
    global $session;
    $out=array();
    if ($this->action=='admin') {
      $this->admin($out);
    } else {
      $this->usual($out);
    }
    if (IsSet($this->owner->action)) {
      $out['PARENT_ACTION']=$this->owner->action;
    }
    if (IsSet($this->owner->name)) {
      $out['PARENT_NAME']=$this->owner->name;
    }
    $out['VIEW_MODE']=$this->view_mode;
    $out['EDIT_MODE']=$this->edit_mode;
    $out['MODE']=$this->mode;
    $out['ACTION']=$this->action;
    $out['DATA_SOURCE']=$this->data_source;
    $out['TAB']=$this->tab;
    $this->data=$out;
    $p=new parser(DIR_TEMPLATES.$this->name."/".$this->name.".html", $this->data, $this);
    $this->result=$p->result;
  }
  /**
   * Debug
   *
   * Description
   *
   * @access private
   */
  function debug($content) {
    $this->getConfig();
    if($this->config['DEBUG'])
      $this->log(print_r($content,true));
  }
  /**
   * Log
   *
   * Description
   *
   * @access private
   */
  function log($message) {
    if(!is_dir(ROOT . 'cms/debmes')) {
      mkdir(ROOT . 'cms/debmes', 0777);
    }
    $today_file = ROOT . 'cms/debmes/log_' . date('Y-m-d') . '_idevices.php.txt';
    $data = date("H:i:s")." " . $message . "\n";
    file_put_contents($today_file, $data, FILE_APPEND | LOCK_EX);
  }
  /**
   * BackEnd
   *
   * Module backend
   *
   * @access public
   */
  function admin(&$out) {
    $this->getConfig();
    $out['CYCLERUN'] = ((time() - gg('cycle_idevicesRun')) < 120 ) ? 1 : 0;
    global $ajax;
    global $filter;
    global $limit;
    if($ajax) {
      header("HTTP/1.0: 200 OK\n");
      header('Content-Type: text/html; charset=utf-8');
      // Find last midifed
      $filename = ROOT . 'cms/debmes/log_*_idevices.php.txt';
      foreach(glob($filename) as $file) {
        $LastModified[] = filemtime($file);
        $FileName[] = $file;
      }
      $files = array_multisort($LastModified, SORT_NUMERIC, SORT_ASC, $FileName);
      $lastIndex = count($LastModified) - 1;
      // Open file
      $data = LoadFile($FileName[$lastIndex]);
      $lines = explode("\n", $data);
      $lines = array_reverse($lines);
      $res_lines = array();
      $total = count($lines);
      $added = 0;
      for($i = 0; $i < $total; $i++) {
        if(trim($lines[$i]) == '') {
          continue;
        }
        if($filter && preg_match('/' . preg_quote($filter) . '/is', $lines[$i])) {
          $res_lines[] = $lines[$i];
          $added++;
        } elseif(!$filter) {
          $res_lines[] = $lines[$i];
          $added++;
        }
        if($added >= $limit) {
          break;
        }
      }
      echo implode("<br/>", $res_lines);
      exit;
    }
    
    global $sendMessage;
    if ($sendMessage)
    {
      header("HTTP/1.0: 200 OK\n");
      header('Content-Type: text/html; charset=utf-8');
      global $device_id;
      global $message;
      global $subject;
      global $sound;
      $res = $this->sendMessage($device_id, $message, $subject, $sound);
      echo $res;
      exit;
    }
    global $playSound;
    if ($playSound)
    {
      header("HTTP/1.0: 200 OK\n");
      header('Content-Type: text/html; charset=utf-8');
      global $device_id;
      $res = $this->playSound($device_id);
      echo $res;
      exit;
    }
    global $locate;
    if ($locate)
    {
      header("HTTP/1.0: 200 OK\n");
      header('Content-Type: text/html; charset=utf-8');
      global $device_id;
      $res = $this->locate($device_id);
      echo $res;
      exit;
    }
    global $getDevices;
    if ($getDevices)
    {
      header("HTTP/1.0: 200 OK\n");
      header('Content-Type: text/html; charset=utf-8');
      global $appleid;
      $res = $this->getDevices($appleid);
      echo $res;
      exit;
    }
    
    $out['DEBUG'] = $this->config['DEBUG'];
    $out['PAGINATION'] = $this->config['PAGINATION'] > 0 ? $this->config['PAGINATION'] : 10;
    $out['APIKEY'] = $this->config['APIKEY'];
    $out['MAPTYPE'] = ($this->config['MAPTYPE'] ? $this->config['MAPTYPE'] : 'hybrid');


    if($this->data_source == 'idevices' || $this->data_source == '') {
      if($this->view_mode == 'update_settings') {
        global $debug;
        $this->config['DEBUG'] = $debug;
        global $pagination;
        global $apikey;
        global $maptype;
        
        $this->config['PAGINATION'] = $pagination;
        $this->config['APIKEY'] = $apikey;
        $this->config['MAPTYPE'] = $maptype;
        $this->saveConfig();
        $this->log("Save config");
        $this->redirect("?tab=".$this->tab);
      }
      if($this->view_mode == 'appleid_edit') {
        $this->edit_appleid($out, $this->id);
      }
      if($this->view_mode == 'device_edit') {
        $this->edit_device($out, $this->id);
      }
      if($this->view_mode == 'appleid_delete') {
        $this->delete_appleid($this->id);
        $this->redirect("?tab=appleids");
      }
      if($this->view_mode == 'device_delete') {
        $this->delete_device($this->id);
        $this->redirect("?");
      }
      if($this->view_mode == '' || $this->view_mode == 'search_ms') {
        if($this->tab == 'appleids') {
          $this->idevices_appleids($out);
        } else if($this->tab == 'log') {
          $this->idevices_log($out);
        } else {
          $this->idevices_devices($out);
        }
      }
    }
  }
  /**
   * FrontEnd
   *
   * Module frontend
   *
   * @access public
   */
  function usual(&$out) {
    $this->admin($out);
  }
  /**
   * Edit/add
   *
   * @access public
   */
  function edit_appleid(&$out, $id) {
    require(DIR_MODULES . $this->name . '/appleid_edit.inc.php');
  }
  function edit_device(&$out, $id) {
    require(DIR_MODULES . $this->name . '/device_edit.inc.php');
  }
  /**
   * View record
   *
   * @access public
   */
  function idevices_devices(&$out) {
    require(DIR_MODULES . $this->name . '/idevices_devices.inc.php');
  }
  function idevices_log(&$out) {
    require(DIR_MODULES . $this->name . '/idevices_log.inc.php');
  }
  function idevices_appleids(&$out) {
    require(DIR_MODULES . $this->name . '/idevices_appleids.inc.php');
  }
  /**
   * Delete record
   *
   * @access public
   */
  function delete_device($id) {
    $rec = SQLSelectOne("SELECT * FROM idevices WHERE ID='$id'");
    // some action for related tables
    SQLExec("DELETE FROM idevices WHERE ID='" . $rec['ID'] . "'");
  }
  function delete_appleid($id) {
    $rec=SQLSelectOne("SELECT * FROM appleIDs WHERE ID='$id'");
    // some action for related tables
    SQLExec("DELETE FROM idevices WHERE APPLEID='".$rec['APPLEID']."'");
    SQLExec("DELETE FROM appleIDs WHERE ID='".$rec['ID']."'");
  }
  /**
   * Program interface
   *
   * @access public
   */
  function restartService() {
    setGlobal('cycle_idevices','restart');
    $this->log("Init cycle restart");
  }
  function setCheckInterval($device_id, $interval = 0) {
    $device = SQLSelectOne("SELECT ID, NAME FROM idevices WHERE DEVICE_ID = '".DBSafe($device_id)."' OR NAME = '".DBSafe($device_id)."'");
    $this->debug('<pre>setCheckInterval: '.$device['NAME'].'</br>  device_id: '.$device_id.'</br>  interval: '.$interval.'</pre>');
    $device['GET_LOCATION'] = $interval;
    SQLUpdate('idevices', $device);
    return $interval;
  }
  function getCheckInterval($device_id) {
    $device = SQLSelectOne("SELECT ID, NAME FROM idevices WHERE DEVICE_ID = '".DBSafe($device_id)."' OR NAME = '".DBSafe($device_id)."'");
    $this->debug('<pre>getCheckInterval: '.$device['NAME'].'</br>  device_id: '.$device_id.'</pre>');
    return $device['GET_LOCATION'];
  }
  /**
   * Actions
   *
   * @access public
   */
  function sendMessage($device_id, $message, $subject = '', $sound = false) {
    if($message == "")
      return 0;
    set_time_limit(600);
    $device = $this->initClient($device_id);
    $this->debug('<pre>sendMessage: '.$device['NAME'].'</br>  device_id: '.$device_id.'</br>  message: '.$message.'</br>  subject: '.$subject.'</br>  sound: '.$sound.'</pre>');
    try {
      $this->FindMyiPhone->sendMessage($device['DEVICE_ID'], $message, (bool)$sound, $subject);
    } catch (exception $e) {
      $this->debug($e->getMessage());
    }
    return "Message sended";
  }
  function playSound($device_id, $subject = '') {
    set_time_limit(600);
    $device = $this->initClient($device_id);
    $this->debug('<pre>playSound: '.$device['NAME'].'</br>  device_id: '.$device_id.'</br>  subject: '.$subject.'</pre>');
    try {
      $this->FindMyiPhone->playSound($device['DEVICE_ID'], $subject);
    } catch (exception $e) {
      $this->debug($e->getMessage());
    }
    return "Sound played";
  }
  function locate($device_id){
    set_time_limit(0);
    $device = $this->initClient($device_id);
    $this->debug('<pre>locate: '.$device['NAME'].'</br>  device_id: '.$device_id.'</pre>');
    try {
      $location =  $this->FindMyiPhone->locate($device['DEVICE_ID'], 180);
      $prop=SQLSelectOne("SELECT * FROM idevices WHERE APPLEID='".DBSafe($device['APPLEID'])."' AND DEVICE_ID='".DBSafe($device['DEVICE_ID'])."'");
      $prop['NAME'] = $this->FindMyiPhone->devices[$device['DEVICE_ID']]->name;
      $prop['BATTERY_LEVEL'] = round($this->FindMyiPhone->devices[$device['DEVICE_ID']]->batteryLevel*100, 2);
      $prop['BATTERY_STATUS'] = ($this->FindMyiPhone->devices[$device['DEVICE_ID']]->batteryStatus == "Charging") ? 1 : 0;
      $prop['ACCURACY'] = $location->horizontalAccuracy;
      $prop['LATITUDE'] = $location->latitude;
      $prop['LONGITUDE'] = $location->longitude;
      $prop['POSITION_TYPE'] = $this->FindMyiPhone->devices[$device['DEVICE_ID']]->API['location']['positionType'];
      $prop['UPDATED'] = date('Y-m-d H:i:s', substr($location->timestamp, 0, -3));
      SQLUpdateInsert('idevices', $prop);
      if(file_exists(DIR_MODULES.'app_gpstrack/installed')) {
        $url = BASE_URL . '/gps.php?latitude=' . str_replace(',', '.', $prop['LATITUDE'])
        . '&longitude=' . str_replace(',', '.', $prop['LONGITUDE'])
        . '&altitude=' . 0
        . '&accuracy=' . $prop['ACCURACY']
        . '&provider=' . ''
        . '&speed=' . 0
        . '&battlevel=' . $prop['BATTERY_LEVEL']
        . '&charging=' . $prop['BATTERY_STATUS']
        . '&deviceid=' . urlencode($prop['NAME']);
        getURL($url, 0);
      }
    } catch (exception $e) {
      $this->debug($e->getMessage());
    }
    return "Device located";
  }
  function getDevices($appleid){
    set_time_limit(0);
    $record=SQLSelectOne("SELECT * FROM appleIDs WHERE APPLEID='".DBSafe($appleid)."'");
    $this->debug('getDevices: '.$appleid);
    try {
      if ($this->current_AppleID != $appleid) {
        $this->current_AppleID = $appleid;
        $this->FindMyiPhone = new FindMyiPhone($record['APPLEID'], $record['PASSWORD'], $this->deep_debug);
      }
      $this->FindMyiPhone->getDevices();
      foreach ($this->FindMyiPhone->devices as $device_id => $device)
      {
        $prop=SQLSelectOne("SELECT * FROM idevices WHERE APPLEID='".DBSafe($appleid)."' AND DEVICE_ID='".DBSafe($device_id)."'");
        $prop['NAME'] = $device->name;
        $prop['DEVICE_ID'] = $device_id;
        $prop['APPLEID'] = $record['APPLEID'];
        $prop['BATTERY_LEVEL'] = round($device->batteryLevel*100, 2);
        $prop['BATTERY_STATUS'] = ($device->batteryStatus == "Charging") ? 1 : 0;
        $prop['UPDATED']=date('Y-m-d H:i:s', substr($device->serverAPI["serverTimestamp"], 0, -3));
        SQLUpdateInsert('idevices', $prop);
      }
      unset($device);
    } catch (exception $e) {
      $this->debug($e->getMessage());
    }
    return "Devices got";
  }
  
  function processSubscription($event, &$details) {
    if ($event=='SAY') {
      $level=$details['level'];
      $message=$details['message'];
      //...
    }
  }
  function processCycle() {
    $devices = SQLSelect("SELECT DEVICE_ID, APPLEID FROM idevices
      WHERE GET_LOCATION > 0 AND DATE_ADD(UPDATED, INTERVAL GET_LOCATION MINUTE) <= NOW()
      ORDER BY DATE_ADD(UPDATED, INTERVAL GET_LOCATION MINUTE) - NOW()");
    foreach($devices as $device)
      $this->locate($device['DEVICE_ID']);
  }
  /**
   * Install
   *
   * Module installation routine
   *
   * @access private
   */
  function install($data='') {
    //subscribeToEvent($this->name, 'SAY');
    //subscribeToEvent($this->name, 'SAYTO', '', 10);
    //subscribeToEvent($this->name, 'SAYREPLY', '', 10);
    parent::install();
    $this->restartService();
  }
  /**
   * Uninstall
   *
   * Module uninstall routine
   *
   * @access public
   */
  function uninstall() {
    SQLExec('DROP TABLE IF EXISTS appleIDs');
    SQLExec('DROP TABLE IF EXISTS idevices');
    if(file_exists(ROOT.'lib/idevices.php'))
      unlink(ROOT.'lib/idevices.php');
    array_map('unlink', glob(ROOT.'languages/idevices_*.php'));
    unsubscribeFromEvent($this->name, 'SAY');
    unsubscribeFromEvent($this->name, 'SAYTO');
    unsubscribeFromEvent($this->name, 'SAYREPLY');
    parent::uninstall();
  }
  /**
   * dbInstall
   *
   * Database installation routine
   *
   * @access private
   */
  function dbInstall($data = '') {
  /*
   appleIDs - 
   idevices - 
   */
    $data = <<<EOD
      appleIDs: ID int(10) unsigned NOT NULL auto_increment
      appleIDs: APPLEID varchar(50) NOT NULL
      appleIDs: PASSWORD varchar(50) NOT NULL DEFAULT ''
      idevices: ID int(10) unsigned NOT NULL auto_increment
      idevices: NAME varchar(50) NOT NULL
      idevices: DEVICE_ID varchar(70) NOT NULL DEFAULT ''
      idevices: APPLEID varchar(50) NOT NULL
      idevices: GET_LOCATION int(5) unsigned DEFAULT '0'
      idevices: BATTERY_LEVEL int(3) unsigned NOT NULL DEFAULT '0'
      idevices: BATTERY_STATUS int(1) unsigned DEFAULT '0'
      idevices: LATITUDE varchar(20) NOT NULL DEFAULT ''
      idevices: LONGITUDE varchar(20) NOT NULL DEFAULT ''
      idevices: ACCURACY int(5) NOT NULL DEFAULT '0'
      idevices: POSITION_TYPE varchar(20) NOT NULL DEFAULT ''
      idevices: UPDATED datetime
EOD;
     parent::dbInstall($data);
  }
// --------------------------------------------------------------------
}
?>
