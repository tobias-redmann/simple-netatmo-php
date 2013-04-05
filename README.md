simply-netatmo-php
==================

Simple Wrapper for the Netatmo API

# Requirements

First of all you need also the official API of NetAtmo. To be more specific the Client API which we will use to wrap around. You can get the API from the [PHP API repository](https://github.com/Netatmo/Netatmo-API-PHP) at Github as well.

# Install

To use the Simply Netatmo PHP API just include the simple-netatmo-php.php file into your code. You will find the file in the lib folder.

    require('lib/simple-netatmo-php.php');

For sure you need to download the lib from here.

# Usage

As I already mentioned you need an instance of a NAApiClient to use the libary. You can create a client one like this:

    $credentials = array(
      'client_id'     => 'your_client_id',
	    'client_secret' => 'your_client_secret',
      'username'      => 'your_username',
      'password'      => 'your_password'
    );
    
    $client = new NAApiClient($credentials);

This client instance can we now use to create our wrapper instance.

## Creating the API wrapper

You just need to create a new Netatmo instance with the client as parameter:

    $netatmo = new Netatmo($client);
    
## Devices

With this instance we can get all our devices:

    $devices = $netatmo->getDevices();
    
    foreach($devices as $device) {
    
      echo $device->getName();
    
    }
    
    
We can also get a know device:

	$device = $netatmo->getDevice('device_name');
	
With this device we can now work with the modules.

## Modules

Every device can have modules - in fact the device itself is a module. To get measurements we need to have a module instance. First we can get all modules per device.

    $modules = $device->getModules();
    
    foreach($modules as $module) {
    
      echo $module->getName();
    
    }
    
We can also get a named module. At this time there should always be two modules for every device called 'Indoor' and 'Outdoor'. You can select them directly with:

    $outdoor_module = $device->getModule('Outdoor');
    
From these module we can get some measurements.

## Measurements

You can get measurements by calling module functions. For example you can get the latest measurements like this:

    $measurement = $outdoor_module->getLatestMeasurement();
    
    echo 'Temperature: ' . $measurement->get('temperature');
    echo 'CO2:         ' . $measurement->get('co2');
    
But you can also get measurements for given day:

	$measurements = $outdoor_module->getMeasurementByDay('2013-04-05');
	
	foreach($measurements as $measurement) {
	
	  echo 'Time:        ' . $measurement->getTime('H:i:s');
	  echo 'Temperature: ' . $measurement->get('temperature');
	
	}
	
The function currently work only in 30min steps - but in the future there will be more functions and parameters.

# Example

Just to wrap everything up a copy&paste example to get the temperature from an outdoor module:

    $device_name = 'my_device';
    $module_name = 'outdoor';
    
    $credentials = array(
      'client_id'     => 'your_client_id',
	    'client_secret' => 'your_client_secret',
      'username'      => 'your_username',
      'password'      => 'your_password'
    );
    
    $client = new NAApiClient($credentials);
    
    $netatmo = new Netatmo($client);
    
    $device = $netatmo->getDevice($device_name);
    
    $module = $device->getModule($module_name);
    
    $measurement = $module->getLatestMeasurement();
    
    $temperature = $measurement->get('temperature');
    
    // or
    
    // $netatmo->getDevice($device_name)->getModule($module_name)->getLatestMeasurement()->get('temperature');
    
# Bugs and contact
 
Yes, we have bugs and problems! To report them use the issue tracker here. You can also contact me:

Mail: tobias@tricd.co

Web: [www.tricd.de](http://www.tricd.de)