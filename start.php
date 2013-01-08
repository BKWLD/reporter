<?php

// Do nothing if the profiler isn't enbled
if (!Config::get('application.profiler')) return;

// Imports
Autoloader::namespaces(array('Reporter' => Bundle::path('reporter')));

// Listen for request to be done
Event::listen('laravel.done', function($response) {
	$r = new Reporter\Reporter();
	$r->write();
});