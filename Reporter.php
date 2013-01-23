<?php namespace Reporter;

// Dependencies
use Reporter\Profiler;
use Laravel\Log;
use Laravel\URI;
use Laravel\Request;
use Laravel\Input;

// Assemble stats and write them to the file
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
		$url = preg_replace('#https?:#', '', URI::full());
		$this->format('REQUEST', wordwrap($url, 72, "\n           ", true).$props);

		// Get the data
		Profiler::finish();
		$data = Profiler::data();
		
		// Display execution time
		$this->format('TIME', $data['time'].'ms');
		
		// Display memory
		$this->format('MEMORY', $data['memory'].' (PEAK: '.$data['memory_peak'].')');
		
		// Display POST data
		$input = Input::json() ? (array) Input::json() : Input::get();
		if (!empty($input)) {
			$this->format('INPUT');
			$maxlen = 0;
			foreach(array_keys($input) as $key) $maxlen = max($maxlen, strlen($key) + 4);
			foreach ($input as $key => $val) {
				if (is_array($val) || is_object($val)) $val = json_encode($val);
				$this->add(
					Style::wrap('grey', str_pad('  '.$key.': ', $maxlen)).
					Style::wrap('cyan', wordwrap($val, 72, "\n".str_repeat(' ', $maxlen)))
				);
			}
		}
		
		// Display queries
		if (count($data['queries'])) {
			$this->format('SQL', count($data['queries']).' queries');
			foreach($data['queries'] as $query) {
				$this->add(
					Style::wrap('grey', '  ('.$query[1].'ms) ').
					Style::wrap('cyan',wordwrap($query[0], 72, "\n           "))
				);
			}
		}
		
		// Display
		$this->add();
		$this->add(Style::wrap('grey', str_repeat('-', 72)));
		$this->publish();		
	}
	
	// Add a line to the output
	private function add($line = '') {
		$this->output[] = $line;
	}
	
	// Format a line and add it
	private function format($label, $value='', $pad=11) {
		$this->add(
			Style::wrap(array('bold', 'grey'), str_pad($label.':', $pad)).
			Style::wrap('magenta', $value)
		);
	}
	
	// Dump the output to disk
	private function publish() {
		Log::write('Reporter', implode($this->output, "\n"));
		$this->output = array();
	}
	
}