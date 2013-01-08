<?php namespace Reporter;

// Dependencies
use Reporter\Profiler;
use Laravel\Log;
use Laravel\URI;
use Laravel\Request;

class Reporter {
	
	// Store the output
	private $ouput = array();
	
	// Write a new report
	public function write() {
		
		// Start a new entry
		$this->add();$this->add();
	
		// Display the URL
		$props = array();
		if (Request::method() != 'GET') $props[] = Request::method();
		if (Request::ajax()) $props[] = 'XHR';
		$props = count($props) ? ' ('.implode(',',$props).')' : null;
		$this->format('REQUEST', preg_replace('#https?:#', '', URI::full()).$props);

		// Get the data
		Profiler::finish();
		$data = Profiler::data();
		
		// Display execution time
		$this->format('TIME', $data['time'].'ms');
		
		// Display memory
		$this->format('MEMORY', $data['memory'].' (PEAK: '.$data['memory_peak'].')');
		
		// Display POST data
		if (!empty($_POST)) {
			$this->format('POST');
			$maxlen = 0;
			foreach(array_keys($_POST) as $key) $maxlen = max($maxlen, strlen($key) + 4);
			foreach ($_POST as $key => $val) {
				$this->add(str_pad('  '.$key.': ', $maxlen).wordwrap($val, 72, "\n".str_repeat(' ', $maxlen)));
			}
		}
		
		// Display queries
		if (count($data['queries'])) {
			$this->format('SQL', count($data['queries']).' queries');
			foreach($data['queries'] as $query) {
				$this->add('  ('.$query[1].'ms) '.wordwrap($query[0], 72, "\n           "));
			}
		}
		
		// Display
		$this->add();
		$this->add(str_repeat('-', 72));
		$this->publish();		
	}
	
	// Add a line to the output
	private function add($line = '') {
		$this->output[] = $line;
	}
	
	// Format a line and add it
	private function format($label, $value='', $pad=11) {
		$this->add(str_pad($label.':', $pad).$value);
	}
	
	// Dump the output to disk
	private function publish() {
		Log::write('Reporter', implode($this->output, "\n"));
		$this->output = array();
	}
	
}