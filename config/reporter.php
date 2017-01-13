<?php return array(

	// Toggle reporting on and off
	'enable' => env('REPORTER_ENABLE', true),

	// Also write logs to the error_log
	'error_log' => env('REPORTER_ERROR_LOG', false),

	// Style the output using escaped codes
	'style' => true,

	// Laravel log levels to show to show
	'levels' => ['debug', 'info', 'notice', 'warning'],

	// A regex for URL paths to ignore.
	// 'ignore' => '\.(jpg|png|gif)$',

);
