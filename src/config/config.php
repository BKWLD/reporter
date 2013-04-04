<?php $config = array(
	
	// Toggle reporting on and off
	'enable' => true,	
	
	// Style the output using escaped codes
	'style' => true,
);

// Lets an application config file override the settings
return array_merge($config, (array) Config::get('reporter'));