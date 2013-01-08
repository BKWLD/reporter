<?php $config = array(
	
	// Toggle reporting on and off
	'enable' => true,	
);

// Lets an application config file override the settings
return array_merge($config, (array) Config::get('reporter'));