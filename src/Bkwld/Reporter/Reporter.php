<?php namespace Bkwld\Reporter;

// Dependencies
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Processor\MemoryUsageProcessor;
use Monolog\Processor\MemoryPeakUsageProcessor;
use Monolog\Processor\WebProcessor;
use DB;
use Log;
use URI;
use Request;
use Input;
use Timer;

// Assemble stats and write them to the file
class Reporter {
	
	/**
	 * Private vars
	 */
	private $logger;
	
	/**
	 * Init
	 */
	public function __construct() {
		
		// Create a new log file for reporter
		$this->logger = new Logger('reporter');
		$stream = new StreamHandler(storage_path().'/logs/reporter.log', Logger::DEBUG);
		$this->logger->pushHandler($stream);
		
		// Apply the Reporter formatter
		$formatter = new Formatter();
		$stream->setFormatter($formatter);
		
		// Add custom and built in processors
		$this->logger->pushProcessor(Timer::getFacadeRoot());
		$this->logger->pushProcessor(new MemoryUsageProcessor());
		$this->logger->pushProcessor(new MemoryPeakUsageProcessor());
		$this->logger->pushProcessor(new WebProcessor());
		
	}
	
	/**
	 * Write a new report
	 */
	public function write($request, $exception = null) {

		// Do a debug log, passing it all the extra data that it needs.  This will ultimately
		// write to the log file
		$this->logger->addDebug('Reporter', array(
			'request' => $request,
			'database' => DB::connection()->getQueryLog(),
			'input' => Input::get(),
			'exception' => $exception,
		));

	}	
}