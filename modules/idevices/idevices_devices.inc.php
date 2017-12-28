<?php

  global $session;
    
  global $uid;
  if ($nid!='') {
   $qry.=" AND APPLEID LIKE '%".DBSafe($nid)."%'";
   $out['APPLEID']=$nid;
  }
  
  global $name;
  if ($name!='') {
   $qry.=" AND NAME LIKE '%".DBSafe($name)."%'";
   $out['NAME']=$name;
  }
  
  // FIELDS ORDER
  global $sortby_device;
  if (!$sortby_device) {
   $sortby_device=$session->data['idevices_device_sort'];
  } else {
   if ($session->data['idevices_device_sort']==$sortby_device) {
    if (Is_Integer(strpos($sortby_device, ' DESC'))) {
     $sortby_device=str_replace(' DESC', '', $sortby_device);
    } else {
     $sortby_device=$sortby_device." DESC";
    }
   }
   $session->data['idevices_device_sort']=$sortby_device;
  }
  if (!$sortby_device) $sortby_device="NAME";
  $out['SORTBY']=$sortby_device;
  
  // SEARCH RESULTS  
  $res=SQLSelect("SELECT * FROM idevices ORDER BY ".$sortby_device);
  if ($res[0]['ID']) {  
  	paging($res, intval($this->config['PAGINATION']), $out); // search result paging
    colorizeArray($res);
    $total=count($res);
    for($i=0;$i<$total;$i++) {
     // some action for every record if required
    }
    $out['RESULT']=$res;
  }  
?>
