<?php

if ($this->mode=='setvalue') {
   global $prop_id;
   global $new_value;
   global $id;
   $this->setProperty($prop_id, $new_value, 1);   
   $this->redirect("?id=".$id."&view_mode=".$this->view_mode."&edit_mode=".$this->edit_mode."&tab=".$this->tab);
} 

if ($this->mode=='device') {
    global $data;
    $this->device($data);
}


  
if ($this->owner->name=='panel') {
  $out['CONTROLPANEL']=1;
}

$table_name='idevices';
$rec=SQLSelectOne("SELECT * FROM $table_name WHERE ID='$id'");
  

if ($this->mode=='update') { 
  $ok=1;
  if ($this->tab=='') {
    if (!$rec['ID'])
    {
        global $device_id;
        $rec['DEVICE_ID']=$device_id;
    }
    global $get_location;
    $rec['GET_LOCATION']=$get_location;
    
    //UPDATING RECORD
    if ($ok) {
      if ($rec['ID']) {
        SQLUpdate($table_name, $rec); // update
      } else {
        $new_rec=1; 
        $rec['ID']=SQLInsert($table_name, $rec); // adding new record
        $id=$rec['ID'];
        $this->locate($device_id);
      }
      $out['OK']=1;
    } else {
      $out['ERR']=1;
    }
  }
    $ok=1;
}
 


outHash($rec, $out);
  
?>
