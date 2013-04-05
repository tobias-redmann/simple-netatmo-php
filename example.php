<?php

$device_name = 'my_device';
$module_name = 'outdoor';

$credentials = array(
  'client_id' => 'your_client_id',
  'client_secret' => 'your_client_secret',
  'username' => 'your_username',
  'password' => 'your_password'
);

$client = new NAApiClient($credentials);

$netatmo = new Netatmo($client);

$device = $netatmo->getDevice($device_name);

$module = $device->getModule($module_name);

$measurement = $module->getLatestMeasurement();

$temperature = $measurement->get('temperature');
?>
