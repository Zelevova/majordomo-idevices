<?php
/*
* @version 0.1 (wizard)
*/
  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }
  $table_name='appleIDs';
  $rec=SQLSelectOne("SELECT * FROM $table_name WHERE ID='$id'");
  if ($this->mode=='update') {
   $ok=1;
  // step: default
  if ($this->tab=='') {
  //updating 'appleid' (varchar)
   global $appleid;
   $rec['APPLEID']=$appleid;
  //updating 'password' (varchar)
   global $password;
   $rec['PASSWORD']=$password;
  }
  // step: data
  if ($this->tab=='data') {
  }
  //UPDATING RECORD
   if ($ok) {
    if ($rec['ID']) {
     SQLUpdate($table_name, $rec); // update
    } else {
     $new_rec=1;
     $rec['ID']=SQLInsert($table_name, $rec); // adding new record
    }
    $out['OK']=1;
   } else {
    $out['ERR']=1;
   }
  }
  // step: default
  if ($this->tab=='') {
  }
  // step: data
  if ($this->tab=='data') {
  }
  if ($this->tab=='data') {
   //dataset2
   $new_id=0;
   global $delete_id;
   if ($delete_id) {
    SQLExec("DELETE FROM idevices WHERE ID='".(int)$delete_id."'");
   }
   $properties=SQLSelect("SELECT * FROM idevices WHERE appleid='".$rec['APPLEID']."' ORDER BY ID");
   $total=count($properties);
   for($i=0;$i<$total;$i++) {
    if ($properties[$i]['APPLEID']==$new_id) continue;
    if ($this->mode=='update') {
      global ${'name'.$properties[$i]['APPLEID']};
      $properties[$i]['NAME']=trim(${'name'.$properties[$i]['APPLEID']});
      global ${'device_id'.$properties[$i]['APPLEID']};
      $properties[$i]['DEVICE_ID']=trim(${'device_id'.$properties[$i]['APPLEID']});
      global ${'appleid'.$properties[$i]['APPLEID']};
      $properties[$i]['APPLEID']=trim(${'appleid'.$properties[$i]['APPLEID']});
      global ${'get_location'.$properties[$i]['APPLEID']};
      $properties[$i]['GET_LOCATION']=trim(${'get_location'.$properties[$i]['APPLEID']});
      global ${'linked_object'.$properties[$i]['APPLEID']};
      $properties[$i]['LINKED_OBJECT']=trim(${'linked_object'.$properties[$i]['APPLEID']});
      global ${'linked_property'.$properties[$i]['APPLEID']};
      $properties[$i]['LINKED_PROPERTY']=trim(${'linked_property'.$properties[$i]['APPLEID']});
      global ${'linked_method'.$properties[$i]['APPLEID']};
      $properties[$i]['LINKED_METHOD']=trim(${'linked_method'.$properties[$i]['APPLEID']});
      SQLUpdate('idevices', $properties[$i]);
      $old_linked_object=$properties[$i]['LINKED_OBJECT'];
      $old_linked_property=$properties[$i]['LINKED_PROPERTY'];
      if ($old_linked_object && $old_linked_object!=$properties[$i]['LINKED_OBJECT'] && $old_linked_property && $old_linked_property!=$properties[$i]['LINKED_PROPERTY']) {
       removeLinkedProperty($old_linked_object, $old_linked_property, $this->name);
      }
      if ($properties[$i]['LINKED_OBJECT'] && $properties[$i]['LINKED_PROPERTY']) {
       addLinkedProperty($properties[$i]['LINKED_OBJECT'], $properties[$i]['LINKED_PROPERTY'], $this->name);
      }
     }
   }
   $out['PROPERTIES']=$properties;
  }
  if (is_array($rec)) {
   foreach($rec as $k=>$v) {
    if (!is_array($v)) {
     $rec[$k]=htmlspecialchars($v);
    }
   }
  }

  if ($this->mode=='getdata') {
   $this->scan($rec['ID']);
   $this->redirect("?view_mode=".$this->view_mode."&tab=".$this->tab."&id=".$rec['ID']);
  }

  outHash($rec, $out);
