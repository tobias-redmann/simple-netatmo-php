<?php

$my_device_name = 'your_device_name';

// create the wrapper by passing the official netatmo client
$netatmo = new Netatmo($client);

// select the main device - by default the indoor one
$my_device = $netatmo->getDeviceByName($my_device_name);


// get the lastest measurements
$latest_measurement = $my_device->getLatestMeasures();

// get the temperature
$temperature = $latest_measurement->get('temperature');




?>
