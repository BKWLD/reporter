<?php namespace Bkwld\Reporter\Processors;

class Timer {

	// Log the the start time
	private $start_time;
	public function __construct() {
		$this->start_time = microtime(true);
	}
	
	// Add the elapsed time
	public function __invoke(array $record) {
		$record['extra']['time'] = number_format((microtime(true) - $this->start_time) * 1000, 2);
		return $record;
	}
}