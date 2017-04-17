<?php

function AppletoGPS($idevice)
{
if(file_exists(DIR_MODULES.'app_gpstrack/installed')) {
  if($idevice['NAME'])
  {
    $sqlQuery = "SELECT *
        FROM gpsdevices
        WHERE DEVICEID = '" . DBSafe($idevice['NAME']) . "'";
    $device = SQLSelectOne($sqlQuery);
    if (!$device['ID'])
    {
      $device = array();
      $device['DEVICEID'] = $idevice['NAME'];
      $device['TITLE']    = 'New GPS Apple Device';
      $device['ID'] = SQLInsert('gpsdevices', $device);
      $sqlQuery = "UPDATE gpslog
        SET DEVICE_ID = '" . $device['ID'] . "'
        WHERE DEVICEID = '" . DBSafe($idevice['NAME']) . "'";
      SQLExec($sqlQuery);
    }
    $device['LAT']     = $idevice['LATITUDE'];
    $device['LON']     = $idevice['LONGITUDE'];
    $device['UPDATED'] = date('Y-m-d H:i:s');
    SQLUpdate('gpsdevices', $device);
  }

  $rec = array();
  $rec['ADDED']     = date('Y-m-d H:i:s');
  $rec['LAT']       = $idevice['LATITUDE'];
  $rec['LON']       = $idevice['LONGITUDE'];
  //$rec['ALT']       = round($_REQUEST['altitude'], 2);
  //$rec['PROVIDER']  = $_REQUEST['provider'];
  //$rec['SPEED']     = round($_REQUEST['speed'], 2);
  $rec['BATTLEVEL'] = $idevice['BATTERY_LEVEL'];
  $rec['CHARGING']  = (int)$idevice['BATTERY_STATUS'];
  $rec['DEVICEID']  = $idevice['NAME'];
  $rec['ACCURACY']  = isset($idevice['ACCURACY']) ? $idevice['ACCURACY'] : 0;
  if ($device['ID'])
    $rec['DEVICE_ID'] = $device['ID'];
  $rec['ID'] = SQLInsert('gpslog', $rec);
  if ($device['USER_ID'])
  {
    $sqlQuery = "SELECT *
        FROM users
        WHERE ID = '" . $device['USER_ID'] . "'";
    $user = SQLSelectOne($sqlQuery);
    if ($user['LINKED_OBJECT'])
    {
      setGlobal($user['LINKED_OBJECT'] . '.Coordinates', $rec['LAT'] . ',' . $rec['LON']);
      setGlobal($user['LINKED_OBJECT'] . '.CoordinatesUpdated', date('H:i'));
      setGlobal($user['LINKED_OBJECT'] . '.CoordinatesUpdatedTimestamp', time());
      setGlobal($user['LINKED_OBJECT'] . '.BattLevel', $rec['BATTLEVEL']);
      setGlobal($user['LINKED_OBJECT'] . '.Charging', $rec['CHARGING']);
      $sqlQuery = "SELECT *
        FROM gpslog
        WHERE ID        != '" . $rec['ID'] . "'
        AND DEVICE_ID = '" . $device['ID'] . "'
        ORDER BY ID DESC
        LIMIT 1";
      $prev_log = SQLSelectOne($sqlQuery);
      if ($prev_log['ID'])
      {
        $distance = calculateTheDistanceToApple($rec['LAT'], $rec['LON'], $prev_log['LAT'], $prev_log['LON']);
        if ($distance > 100)
        {
          //we're moving
          $objectIsMoving = $user['LINKED_OBJECT'] . '.isMoving';
          setGlobal($objectIsMoving, 1);
          clearTimeOut($user['LINKED_OBJECT'] . '_moving');
          // stopped after 15 minutes of inactivity
          setTimeOut($user['LINKED_OBJECT'] . '_moving', "setGlobal('" . $objectIsMoving . "', 0);", 15 * 60);
        }
      }
    }
  }

  // checking locations
  $lat = (float)$idevice['LATITUDE'];
  $lon = (float)$idevice['LONGITUDE'];
  $locations = SQLSelect("SELECT * FROM gpslocations");
  $total     = count($locations);

  $location_found = 0;
   
  for ($i = 0; $i < $total; $i++)
  {
    if (!$locations[$i]['RANGE'])
      $locations[$i]['RANGE'] = GPS_LOCATION_RANGE_DEFAULT;
      
    $distance = calculateTheDistanceToApple($lat, $lon, $locations[$i]['LAT'], $locations[$i]['LON']);

    if ($locations[$i]['IS_HOME'] && $device['ID']) {
      $device['HOME_DISTANCE']=(int)$distance;
      SQLUpdate('gpsdevices', $device);
      if ($user['LINKED_OBJECT']) {
        setGlobal($user['LINKED_OBJECT'] . '.HomeDistance', $device['HOME_DISTANCE']);
        setGlobal($user['LINKED_OBJECT'] . '.HomeDistanceKm', round($device['HOME_DISTANCE']/1000, 1));
      }
    }
      
    //echo ' (' . $locations[$i]['LAT'] . ' : ' . $locations[$i]['LON'] . ') ' . $distance . ' m';
    if ($distance <= $locations[$i]['RANGE'])
    {
      //Debmes("Device (" . $device['TITLE'] . ") NEAR location " . $locations[$i]['TITLE']);
      $location_found = 1;
         
      if ($user['LINKED_OBJECT'])
        setGlobal($user['LINKED_OBJECT'] . '.seenAt', $locations[$i]['TITLE']);
         
      // we are at location
      $rec['LOCATION_ID'] = $locations[$i]['ID'];
      SQLUpdate('gpslog', $rec);

      $sqlQuery = "SELECT *
            FROM gpslog
            WHERE DEVICE_ID = '" . $device['ID'] . "'
            AND ID        != '" . $rec['ID'] . "'
            ORDER BY ADDED DESC
            LIMIT 1";

      $tmp = SQLSelectOne($sqlQuery);
         
      if ($tmp['LOCATION_ID'] != $locations[$i]['ID'])
      {
        //Debmes("Device (" . $device['TITLE'] . ") ENTERED location " . $locations[$i]['TITLE']);
            
        // entered location
        $sqlQuery = "SELECT *
                FROM gpsactions
                WHERE LOCATION_ID = '" . $locations[$i]['ID'] . "'
                AND ACTION_TYPE = 1
                AND USER_ID     = '" . $device['USER_ID'] . "'";

        $gpsaction = SQLSelectOne($sqlQuery);
            
        if ($gpsaction['ID'])
        {
          $gpsaction['EXECUTED'] = date('Y-m-d H:i:s');
          $gpsaction['LOG']      = $gpsaction['EXECUTED'] . " Executed\n" . $gpsaction['LOG'];
               
          SQLUpdate('gpsactions', $gpsaction);
               
          if ($gpsaction['SCRIPT_ID'])
            runScript($gpsaction['SCRIPT_ID']);
          elseif ($gpsaction['CODE'])
          {
            try
            {
              $code    = $gpsaction['CODE'];
              $success = eval($code);

              if ($success === false)
              {
                DebMes("Error in GPS action code: " . $code);
                registerError('gps_action', "Code execution error: " . $code);
              }
            }
            catch (Exception $e)
            {
              DebMes('Error: exception ' . get_class($e) . ', ' . $e->getMessage() . '.');
              registerError('gps_action', get_class($e) . ', ' . $e->getMessage());
            }
          }
        }
      }
    }
    else
    {
      $sqlQuery = "SELECT *
            FROM gpslog
            WHERE DEVICE_ID = '" . $device['ID'] . "'
            AND ID        != '" . $rec['ID'] . "'
            ORDER BY ADDED DESC
            LIMIT 1";

      $tmp = SQLSelectOne($sqlQuery);
         
      if ($tmp['LOCATION_ID'] == $locations[$i]['ID'])
      {
        //Debmes("Device (" . $device['TITLE'] . ") LEFT location " . $locations[$i]['TITLE']);
            
        // left location
        $sqlQuery = "SELECT *
                FROM gpsactions
                WHERE LOCATION_ID = '" . $locations[$i]['ID'] . "'
                AND ACTION_TYPE = 0
                AND USER_ID     = '" . $device['USER_ID'] . "'";
            
        $gpsaction = SQLSelectOne($sqlQuery);
            
        if ($gpsaction['ID'])
        {
          $gpsaction['EXECUTED'] = date('Y-m-d H:i:s');
          $gpsaction['LOG']      = $gpsaction['EXECUTED'] . " Executed\n" . $gpsaction['LOG'];
               
          SQLUpdate('gpsactions', $gpsaction);
               
          if ($gpsaction['SCRIPT_ID'])
            runScript($gpsaction['SCRIPT_ID']);
          elseif ($gpsaction['CODE'])
          {
            try
            {
              $code    = $gpsaction['CODE'];
              $success = eval($code);
                     
              if ($success === false)
                DebMes("Error in GPS action code: " . $code);
            }
            catch (Exception $e)
            {
              DebMes('Error: exception ' . get_class($e) . ', ' . $e->getMessage() . '.');
            }
          }
        }
      }
    }
  }
  if ($user['LINKED_OBJECT'] && !$location_found)
    setGlobal($user['LINKED_OBJECT'] . '.seenAt', '');
}
}

/**
 * Calculate distance between two GPS coordinates
 * @param mixed $latA First coord latitude
 * @param mixed $lonA First coord longitude
 * @param mixed $latB Second coord latitude
 * @param mixed $lonB Second coord longitude
 * @return double
 */
function calculateTheDistanceToApple($latA, $lonA, $latB, $lonB)
{
   define('EARTH_RADIUS', 6372795);
   
   $lat1  = $latA * M_PI / 180;
   $lat2  = $latB * M_PI / 180;
   $long1 = $lonA * M_PI / 180;
   $long2 = $lonB * M_PI / 180;

   $cl1 = cos($lat1);
   $cl2 = cos($lat2);
   $sl1 = sin($lat1);
   $sl2 = sin($lat2);

   $delta  = $long2 - $long1;
   $cdelta = cos($delta);
   $sdelta = sin($delta);

   $y = sqrt(pow($cl2 * $sdelta, 2) + pow($cl1 * $sl2 - $sl1 * $cl2 * $cdelta, 2));
   $x = $sl1 * $sl2 + $cl1 * $cl2 * $cdelta;

   $ad = atan2($y, $x);
   
   $dist = round($ad * EARTH_RADIUS);

   return $dist;
}


?>
