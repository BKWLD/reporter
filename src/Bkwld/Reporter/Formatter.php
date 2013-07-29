<?php namespace Bkwld\Reporter;

// Dependencies
use Monolog\Formatter\FormatterInterface;

class Formatter implements FormatterInterface {
	
	// Private vars
	private $output = array();
	const PAD = 11;
	const WRAP = "\n           ";
	const WIDTH = 72;
	
	/**
	 * Format reporter output in the proper style.
	 */
	public function format(array $record) {
		
		// Start new log
		$this->add(Style::wrap('grey', str_repeat('-', self::WIDTH)));
		$this->add(Style::wrap('grey', date('n/j/y g:i:s A')));
		$this->add();
		
		// And off formatting by type to sub functions
		$extra = $record['extra'];
		$this->formatRequest($extra, $record['context']['request']);
		$this->formatTimer($extra);
		$this->formatUsage($extra);
		$this->formatInput($extra, $record['context']['input']);
		$this->formatDatabase($extra, $record['context']['database']);
		
		// End
		$this->add(); $this->add();
		return implode("\n", $this->output);
	}
	
	/**
	 * Request info
	 */
	private function formatRequest($extra, $request) {
		$props = array();
		if ($extra['http_method'] != 'GET') $props[] = $extra['http_method'];
		if ($request->ajax()) $props[] = 'XHR';
		$props = count($props) ? ' ('.implode(',',$props).')' : null;
		$this->style('REQUEST', wordwrap($extra['url'], self::WIDTH, self::WRAP, true).$props);
	}
	
	/**
	 * Timing of the page
	 */
	private function formatTimer($extra) {
		
		// Display execution time
		$this->style('TIME', $extra['time'].'ms');
		
		// Display custom timers
		if (!empty($extra['timers'])) {
			$this->style('TIMERS');
			$maxlen = 0;
			foreach(array_keys($extra['timers']) as $key) $maxlen = max($maxlen, strlen($key) + 4);
			foreach($extra['timers'] as $key => $val) {
				$this->add(
					Style::wrap('grey', str_pad('  '.$key.': ', $maxlen)).
					Style::wrap('cyan', $val['elapsed'].'ms')
				);
			}
		}
	}
	
	/**
	 * Memory usage of the request
	 */
	private function formatUsage($extra) {
		$this->style('MEMORY', $extra['memory_usage'].' (PEAK: '.$extra['memory_peak_usage'].')');
	}
	
	/**
	 * Request data
	 */
	private function formatInput($extra, $input) {
		if (empty($input)) return;
		$this->style('INPUT');
		$maxlen = 0;
		foreach(array_keys($input) as $key) $maxlen = max($maxlen, strlen($key) + 4);
		foreach ($input as $key => $val) {
			if (is_array($val) || is_object($val)) $val = json_encode($val);
			$this->add(
				Style::wrap('grey', str_pad('  '.$key.': ', $maxlen)).
				Style::wrap('cyan', wordwrap($val, self::WIDTH, "\n".str_repeat(' ', $maxlen)))
			);
		}
		
	}
	
	
	/**
	 * Database queries
	 */
	private function formatDatabase($extra, $queries) {
		if (empty($queries)) return;
		$this->style('SQL', count($queries).' queries');
		foreach($queries as $query) {
			$sql = $query['query'];
			
			// Loop through bindings and insert into the query string
			foreach($query['bindings'] as $binding) {
				if ($binding instanceof \DateTime) $binding = $binding->format(\Bkwld\Library\Utils\Constants::MYSQL_DATETIME);
				elseif (is_object($binding) && !method_exists($binding, '__toString' )) $binding = 'COULD_NOT_CONVERT_TO_STRING';
				$sql = preg_replace('/\?/', $binding, $sql, 1);
			}
			
			// Add log line
			$this->add(
				Style::wrap('grey', '  ('.number_format($query['time'],2).' ms) ').
				Style::wrap('cyan', wordwrap($sql, self::WIDTH, self::WRAP))
			);
		}
	}
	
	/**
	 * Add a line to the output
	 */
	private function add($line = '') {
		$this->output[] = $line;
	}
	
	/**
	 * Format a line and add it to the output
	 */
	private function style($label, $value='', $pad=self::PAD) {
		$this->add(
			Style::wrap(array('bold', 'grey'), str_pad($label.':', $pad)).
			Style::wrap('magenta', $value)
		);
	}
	
	
	/**
	 * Not intended to be used but required by interface
	 */
	public function formatBatch(array $records) {
		foreach ($records as $key => $record) {
			$records[$key] = $this->format($record);
		}
		return $records;
	}
	
}