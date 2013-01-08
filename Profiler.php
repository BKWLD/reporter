<?php namespace Reporter;

// Imports
use Laravel\Config;

// Extend the Laravel profiler to get access to the profiler data
class Profiler extends \Laravel\Profiling\Profiler {
	
	// Contains most of the logic from Laravel's profiler's render method
	// but executable during AJAX
	public static function finish() {
		static::$data['memory'] = get_file_size(memory_get_usage(true));
		static::$data['memory_peak'] = get_file_size(memory_get_peak_usage(true));
		static::$data['time'] = number_format((microtime(true) - LARAVEL_START) * 1000, 2);
		foreach ( static::$data['timers'] as &$timer) {
			$timer['running_time'] = number_format((microtime(true) - $timer['start'] ) * 1000, 2);
		}
	}
	
	// Get the profielr data
	public static function data() {
		return self::$data;
	}
	
}