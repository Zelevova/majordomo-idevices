<?php
/*
* @version 0.1 (wizard)
*/
 global $session;
  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }
  $qry="1";
  // search filters
  //searching 'APPLEID' (varchar)
  global $appleID;
  if ($appleID!='') {
   $qry.=" AND APPLEID LIKE '%".DBSafe($title)."%'";
   $out['APPLEID']=$appleID;
  }
 // QUERY READY
  global $save_qry;
  if ($save_qry) {
   $qry=$session->data['appleIDs_qry'];
  } else {
   $session->data['appleIDs_qry']=$qry;
  }
  if (!$qry) $qry="1";

  // FIELDS ORDER
  global $sortby_appleIDs;
  if (!$sortby_appleIDs) {
   $sortby_appleIDs=$session->data['appleIDs_sort'];
  } else {
   if ($session->data['appleIDs_sort']==$sortby_appleIDs) {
    if (Is_Integer(strpos($sortby_appleIDs, ' DESC'))) {
     $sortby_appleIDs=str_replace(' DESC', '', $sortby_appleIDs);
    } else {
     $sortby_appleIDs=$sortby_appleIDs." DESC";
    }
   }
   $session->data['appleIDs_sort']=$sortby_appleIDs;
  }
  if (!$sortby_appleIDs) $sortby_appleIDs="APPLEID";

  $out['SORTBY']=$sortby_appleIDs;
  // SEARCH RESULTS
  $res=SQLSelect("SELECT * FROM appleIDs WHERE $qry ORDER BY ".$sortby_appleIDs);
  if ($res[0]['ID']) {
   //paging($res, 100, $out); // search result paging
   $total=count($res);
   for($i=0;$i<$total;$i++) {
    // some action for every record if required
   }
   $out['RESULT']=$res;
  }
