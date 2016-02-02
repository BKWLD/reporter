<?php namespace Bkwld\Reporter\Processors;

class Timer {

	// Private vars
	private $start_time;
	private $timers = array();

	/**
	 * Log the start time on construct
	 */
	public function __construct() {
		$this->start_time = microtime(true);
	}
	
	/**
	 * Start a custom timer
	 */
	public function start($key) {
		
		// Don't start if that key is currently being used
		if (!empty($this->timers[$key])) return false;
		
		// Add start time
		$this->timers[$key] = array('start' => microtime(true), 'key' => $key);
		
	}
	
	/**
	 * Stop a custom timer
	 */
	public function stop($key) {
		
		// Don't stop if the key doesn't exist
		if (empty($this->timers[$key])) return false;
		
		// Add a stop time
		$this->timers[$key]['stop'] = microtime(true);
		
	}
	
	/**
	 * Composite all the timer data for formatting
	 */
	public function __invoke(array $record) {
		
		// Time for the full request
		$record['extra']['time'] = number_format((microtime(true) - $this->start_time) * 1000, 2);
		
		// Loop through timers and them
		$record['extra']['timers'] = array();
		foreach($this->timers as $key => $timer) {
			if (empty($timer['stop'])) $timer['stop'] = microtime(true);
			$timer['elapsed'] = number_format(($timer['stop'] - $timer['start']) * 1000, 2);
			$record['extra']['timers'][$key] = $timer;
		}
		
		// Done
		return $record;
	}
}