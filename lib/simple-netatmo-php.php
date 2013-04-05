<?php



/**
 * Wrapper for the Netatmo client
 */
class Netatmo {
  
  private $client = null;  
  
  /**
   * Create a new Netatmo Wrapper
   * 
   * @param NAApiClient $client
   */
  function __construct($client) {
    
    $this->client = $client;
    
  }
  
  /**
   * Will return all registered main devices
   * 
   * @return \NetatmoDevice
   */
  function getDevices() {
    
    $devices_obj = array();
    
    $devices = $this->client->api('devicelist', 'POST');
    
    
    foreach($devices['devices'] as $device) {
      
      $modules = array();
      
      foreach($device['modules'] as $module_id) {
        
        foreach($devices['modules'] as $raw_module) {
          
          if ($module_id == $raw_module['_id']) {
            
            $n = strtolower($raw_module['module_name']);
            
            $modules[$n] = new NetatmoModule($raw_module);
            
          }
          
        }
                
      }
      
      // main device is also a module
      $main_module = array(
          'module_name' => strtolower($device['module_name']),
          '_id'         => $device['_id']
      );
      
      $modules[$main_module['module_name']] = new NetatmoModule($main_module);
      
      
      $devices_obj[] = new NetatmoDevice($this->client, $device, $modules);
      
      
    }
    
    return $devices_obj;
            
  }
  
  
  /**
   * Get an device by name
   * 
   * @param String $station_name
   * @return NetatmoDevice/boolean
   */
  function getDeviceByName($station_name) {
    
    $devices = $this->getDevices();
        
    foreach ($devices as $device) {
    
      
      if ($device->getName() == $station_name) return $device;
      
    }
    
    return false;
    
  }
  

  
  
}

/**
 * Wrapper for the device
 */
class NetatmoDevice {
  
  private $client;
  private $device_attributes;
  private $modules = array();
  
  /**
   * Create a new NetatmoDevice with retrieved data
   * 
   * @param NAApiClient $client
   * @param Array $device_attributes
   */
  function __construct($client, $device_attributes, $modules) {
    
    $this->client = $client;
    $this->device_attributes = $device_attributes;
    
    $this->modules = $modules;
    
    foreach($this->modules as $module) {
      
      $module->setDevice($this);
      
    }
    
  }
  
  function getModule($name) {
    
    $n = strtolower($name);
    
    if (!array_key_exists($n, $this->modules)) return false;
    
    return $this->modules[$n];
    
  }
  
  /**
   * Get the name of the device
   * 
   * @return String
   */
  function getName() {
    
    return $this->device_attributes['station_name'];
    
  }
  
  /**
   * Get the ID for the device
   * 
   * @return String
   */
  function getID() {
    
    return $this->device_attributes['_id'];
  }
  

  function getClient() {
    
    return $this->client;
    
  }
  
  
}

class NetatmoModule{
  
  private $device;
  private $module_attributes;
  
  function __construct($module_attributes) {
    
    $this->module_attributes = $module_attributes;
    
  }
  
  function setDevice($device) {
    
    $this->device = $device;
    
  }
  
  function getID() {
    
    return $this->module_attributes['_id'];
    
  }
  
    /**
   * Helper function to get timestamp for 00:00:00 for day
   * 
   * @param String $day
   * @return int
   */
  private function getStartTimeStamp($day) {
    
    $ts = strtotime($day);
    
    return mktime(0,0,0, date('m', $ts), date('d', $ts), date('Y',$ts));
    
  }
  
  /**
   * Helper function to get last second timestamp for day
   * 
   * @param String $day
   * @return int
   */
  private function getEndTimeStamp($day) {
    
    $ts = strtotime($day);
    
    
    return mktime(23,59,59, date('m', $ts), date('d', $ts), date('Y',$ts));
  }
  
  /**
   * Function to wrap retrieved measurements
   * 
   * @param Array $measures
   * @param String $specification
   * @return \NetatmoMeasurement
   */
  private function wrapMeasures($measures, $specification) {
    
    $results = array();
    
    
    $specs = explode(',', $specification);
    
    #var_dump($specs);
    
    $start_time = $measures[0]['beg_time'];
    
    $steps = 0;
    
    if (isset($measures[0]['step_time'])) {
      
      $steps = $measures[0]['step_time'];
      
    }
    
    
    foreach($measures[0]['value'] as $counter => $values) {
      
      $time = $start_time + $counter*$steps;
      
      $data = array();
      
      foreach($specs as $pos => $spec) {
        
        $s = strtolower(trim($spec));
        
        $data[$s] = $values[$pos];
        
      }
      
      $results[] = new NetatmoMeasurement($time, $data);
      
      
    }
    
    return $results;
    
    
  } 
  
  /**
   * get specified measeruses for a day
   * 
   * @param String $day
   * @param String $measures
   * @return Array
   */
  function getMeasuresByDay($day, $measures = 'Temperature,CO2,Humidity,Pressure,Noise', $module = null) {
    
    $params = array(
      "scale" => "30min", 
      "type" => "Temperature,CO2,Humidity,Pressure,Noise", 
      "date_begin"  => $this->getStartTimeStamp($day),
      "date_end" => $this->getEndTimeStamp($day), 
      "device_id" => $this->device->getID(),
      "module_id" => $this->getID(),
      "optimize"  => true
    );
    
    
    $meas = $this->device->getClient()->api("getmeasure", "POST", $params);
    
    $ret_meas = $this->wrapMeasures($meas, $measures);
    
    
    return $ret_meas;  
    
  }
  
  
  /**
   * Get the latest speficied measures 
   * 
   * @param String $measures
   * @return NetatmoMeasurement
   */
  function getLatestMeasures($measures = 'Temperature,CO2,Humidity,Pressure,Noise') {
    
    $params = array(
      "scale" => "30min", 
      "type" => "Temperature,CO2,Humidity,Pressure,Noise", 
      "date_end" => 'last', 
      "device_id" => $this->device->getID(),
      "module_id" => $this->getID(),
      "optimize"  => true
    );
    
    
    $meas = $this->device->getClient()->api("getmeasure", "POST", $params);
    
    $ret_meas = $this->wrapMeasures($meas, $measures);
    
    
    return $ret_meas[0]; 
    
  }
  
}


class NetatmoMeasurement {
  
  public $timestamp;
  public $data;
  
  function __construct($timestamp,$data) {
    
    $this->timestamp = $timestamp;
    $this->data = $data;
    
  }
  
  function getTime($format = null) {
    
    if ($format == null) return $this->timestamp;
    
    return date($format, $this->timestamp);
    
  }
  
  function get($value_name) {
    
    if (!array_key_exists($value_name, $this->data)) return false;
    
    return $this->data[$value_name];
  }
  
  
}





?>
