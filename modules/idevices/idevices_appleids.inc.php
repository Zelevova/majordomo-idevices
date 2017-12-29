<?php
  
  global $session;
  
  global $name;
  if ($name!='') {
    $qry.=" AND TITLE LIKE '%".DBSafe($name)."%'";
    $out['TITLE']=$name;
  }
  
  // FIELDS ORDER
  global $sortby_appleid;
  if (!$sortby_appleid) {
    $sortby_appleid=$session->data['idevices_sort_appleid'];
  } else {
    if ($session->data['idevices_sort_appleid']==$sortby_appleid) {
      if (Is_Integer(strpos($sortby_appleid, ' DESC'))) {
        $sortby_appleid=str_replace(' DESC', '', $sortby_appleid);
      } else {
        $sortby_appleid=$sortby_appleid." DESC";
      }
    }
    $session->data['idevices_sort_appleid']=$sortby_appleid;
  }
  if (!$sortby_appleid) $sortby_appleid="APPLEID";
  $out['SORTBY']=$sortby_appleid;
  
  // SEARCH RESULTS
  $res=SQLSelect("SELECT * FROM appleIDs ORDER BY ".$sortby_appleid);
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
