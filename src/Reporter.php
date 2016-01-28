<?php namespace Bkwld\Reporter;

// Dependencies
use Monolog\Logger;
use Monolog\Handler\BufferHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Processor\MemoryUsageProcessor;
use Monolog\Processor\MemoryPeakUsageProcessor;
use Monolog\Processor\WebProcessor;
use DB;
use Input;
use Timer;

// Assemble stats and write them to the file
class Reporter {


	/**
	 * App's monolog instance
	 *
	 * @var Logger
	 */
	protected $logger;

	/**
	 * Array of message arguments
	 *
	 * @var array
	 */
	protected $buffered = array();

	/**
	 * Depedency inject
	 *
	 * @param Logger $logger
	 */
	public function __construct(Logger $logger) {
		$this->logger = $logger;
	}

	/**
	 * Add Reporter configuration to the main monolog
	 *
	 * @return $this
	 */
	public function boot() {

		// Create a new log file for reporter
		$stream = new StreamHandler(storage_path('logs/reporter.log'), Logger::DEBUG);
		$this->logger->pushHandler($stream);

		// Apply the Reporter formatter
		$formatter = new Formatter();
		$stream->setFormatter($formatter);

		// Log to standard PHP log
		if (config('reporter.error_log')) {
			$stdout = new ErrorLogHandler();
			$this->logger->pushHandler($stdout);
			$stdout->setFormatter($formatter);
		}

		// Add custom and built in processors
		$this->logger->pushProcessor(Timer::getFacadeRoot());
		$this->logger->pushProcessor(new MemoryUsageProcessor());
		$this->logger->pushProcessor(new MemoryPeakUsageProcessor());
		$this->logger->pushProcessor(new WebProcessor());

		// Enable chainig
		return $this;
	}

	/**
	 * Buffer other Laravel log messages
	 *
	 * @param  string $level
	 * @param  string $message
	 * @param  array  $context
	 * @return void
	 */
	public function buffer($level, $message, $context = array()) {
		$this->buffered[] = (object) array(
			'level' => $level,
			'message' => $message,
			'context' => $context,
		);
	}

	/**
	 * Write a new report
	 *
	 * @return void
	 */
	public function write($params = array()) {
		$defaults = array();

		// Test for DB, in case it's not able to connect yet
		try {
			$defaults['database'] = DB::getQueryLog();
		} catch (\Exception $e) {

			// Continue running even if DB could not be logged, but display
			// a note in the log
			$this->buffer('error', 'Reporter could not connect to the database');
		}

		// Default params
		$defaults['input'] = Input::get();
		$defaults['logs'] = $this->buffered;

		// Apply default params
		$params = array_merge($defaults, $params);

		// Do a debug log, passing it all the extra data that it needs.  This will ultimately
		// write to the log file
		$this->logger->addDebug('Reporter', $params);

	}
}
