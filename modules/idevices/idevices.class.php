<?php

/**
* iDevices
* @package project
* @author Wizard <sergejey@gmail.com>
* @copyright http://majordomo.smartliving.ru/ (c)
* @version 0.1 (wizard, 23:04:59 [Apr 14, 2017])
*/
//
//
class idevices extends module {
/**
* idevices
*
* Module class constructor
*
* @access private
*/
function idevices() {
  $this->name="idevices";
  $this->title="iDevices";
  $this->module_category="<#LANG_SECTION_DEVICES#>";
  $this->checkInstalled();
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
* BackEnd
*
* Module backend
*
* @access public
*/
function admin(&$out) {
 if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
  $out['SET_DATASOURCE']=1;
 }
 if ($this->data_source=='appleIDs' || $this->data_source=='') {
  if ($this->view_mode=='' || $this->view_mode=='search_appleIDs') {
   $this->search_appleIDs($out);
  }
  if ($this->view_mode=='edit_appleIDs') {
   $this->edit_appleIDs($out, $this->id);
  }
  if ($this->view_mode=='delete_appleIDs') {
   $this->delete_appleIDs($this->appleid);
   $this->redirect("?data_source=appleIDs");
  }
 }
 if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
  $out['SET_DATASOURCE']=1;
 }
 if ($this->data_source=='idevices') {
  if ($this->view_mode=='' || $this->view_mode=='search_idevices') {
   $this->search_idevices($out);
  }
  if ($this->view_mode=='edit_idevices') {
   $this->edit_idevices($out, $this->id);
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
* appleIDs search
*
* @access public
*/
 function search_appleIDs(&$out) {
  require(DIR_MODULES.$this->name.'/appleIDs_search.inc.php');
 }

 function scan($id) {
  require(DIR_MODULES.$this->name.'/scan.inc.php');
 }

/**
* appleIDs edit/add
*
* @access public
*/
 function edit_appleIDs(&$out, $id) {
  require(DIR_MODULES.$this->name.'/appleIDs_edit.inc.php');
 }
/**
* appleIDs delete record
*
* @access public
*/
 function delete_appleIDs($id) {
  $rec=SQLSelectOne("SELECT * FROM appleIDs WHERE ID='$id'");
  // some action for related tables
  SQLExec("DELETE FROM appleIDs WHERE ID='".$rec['ID']."'");
 }
/**
* idevices search
*
* @access public
*/
 function search_idevices(&$out) {
  require(DIR_MODULES.$this->name.'/idevices_search.inc.php');
 }
/**
* idevices edit/add
*
* @access public
*/
 function edit_idevices(&$out, $id) {
  require(DIR_MODULES.$this->name.'/idevices_edit.inc.php');
 }
 function propertySetHandle($object, $property, $value) {
   $table='idevices';
   $properties=SQLSelect("SELECT ID FROM $table WHERE LINKED_OBJECT LIKE '".DBSafe($object)."' AND LINKED_PROPERTY LIKE '".DBSafe($property)."'");
   $total=count($properties);
   if ($total) {
    for($i=0;$i<$total;$i++) {
     //to-do
    }
   }
 }
 function processSubscription($event, $details='') {
  if ($event=='SAY') {
   $level=$details['level'];
   $message=$details['message'];
   //...
  }
 }
 function processCycle() {
  $devices = SQLSelect("SELECT NAME, APPLEID
  FROM idevices
  WHERE GET_LOCATION > 0 AND DATE_ADD(UPDATED, INTERVAL GET_LOCATION MINUTE) <= NOW()
  ORDER BY DATE_ADD(UPDATED, INTERVAL GET_LOCATION MINUTE) - NOW()");
  foreach($devices as $device)
    findApple($device['NAME']);
  $timeout = SQLSelectOne("SELECT MIN(DATE_ADD(UPDATED, INTERVAL GET_LOCATION MINUTE)) AS TIME
  FROM idevices
  WHERE GET_LOCATION > 0");
  time_sleep_until(strtotime($timeout['TIME']));
}
/**
* Install
*
* Module installation routine
*
* @access private
*/
 function install($data='') {
  subscribeToEvent($this->name, 'SAY');
  parent::install();
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
  unsubscribeFromEvent($this->name, 'SAY');
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
 appleIDs: APPLEID varchar(50) NOT NULL DEFAULT ''
 appleIDs: PASSWORD varchar(50) NOT NULL DEFAULT ''
 idevices: ID int(10) unsigned NOT NULL auto_increment
 idevices: NAME varchar(50) NOT NULL DEFAULT ''
 idevices: DEVICE_ID varchar(70) NOT NULL DEFAULT ''
 idevices: APPLEID varchar(50) NOT NULL DEFAULT ''
 idevices: GET_LOCATION int(5) unsigned DEFAULT '0'
 idevices: BATTERY_LEVEL int(3) unsigned NOT NULL DEFAULT '0'
 idevices: BATTERY_STATUS int(1) unsigned DEFAULT '0'
 idevices: LATITUDE varchar(20) NOT NULL DEFAULT ''
 idevices: LONGITUDE varchar(20) NOT NULL DEFAULT ''
 idevices: ACCURACY int(5) NOT NULL DEFAULT '0'
 idevices: LINKED_OBJECT varchar(100) NOT NULL DEFAULT ''
 idevices: LINKED_PROPERTY varchar(100) NOT NULL DEFAULT ''
 idevices: LINKED_METHOD varchar(100) NOT NULL DEFAULT ''
 idevices: UPDATED datetime
EOD;
  parent::dbInstall($data);
 }
// --------------------------------------------------------------------
}
/*
*
* TW9kdWxlIGNyZWF0ZWQgQXByIDE0LCAyMDE3IHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
