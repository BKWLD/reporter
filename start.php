<?php

// Disable
if (!Config::get('reporter::reporter.enable')) return;

// Dependencies
Autoloader::namespaces(array('Reporter' => Bundle::path('reporter')));

// Attach built in profiler listeners if it's not turned on
if (!Config::get('application.profiler')) {
	Laravel\Profiling\Profiler::attach();
}

// Listen for request to be done
Event::listen('laravel.done', function($response) {
	$r = new Reporter\Reporter();
	$r->write();
});