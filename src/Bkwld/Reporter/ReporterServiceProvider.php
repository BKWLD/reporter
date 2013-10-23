<?php namespace Bkwld\Reporter;

use Config;
use Exception;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Console\Input\ArgvInput;

class ReporterServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('bkwld/reporter');
		
		// Disable
		if (!Config::get('reporter::enable')) return;
		
		// Make a timer instance that can be resolved via the facade.
		$this->app->singleton('timer', function() {
			return new Processors\Timer();
		});
		
		// Init
		$reporter = new Reporter();
		$request = $this->app->make('request');
		
		// If the app is running through console, listen for shutdown.  It's the only
		// event that fires after artisan finishes
		if ($this->app->runningInConsole()) {
			$this->app->shutdown(function() use ($reporter) {
				$command = implode(' ', array_slice($_SERVER['argv'], 1));
				$reporter->write(array('command' => $command));
			});
		
		// Listen for request to be done.  Using "close" because "finish" comes too
		// late for ChromePHP but close should happen after regular "after" handlers
		} else {
			$this->app->close(function($request, $response) use ($reporter) {
				$reporter->write(array( 'request' => $request ));
			});
		}
		
		
		// Write logs on fatal errors and exceptions
		$this->app->error(function(Exception $exception) use ($reporter, $request) {
			$reporter->write(array(
				'request' => $request,
				'exception' => $exception
			));
		});
		
		// Buffer other log messages.
		$levels = Config::get('reporter::levels');
		if (!empty($levels)) {
			$this->app->make('log')->listen(function($level, $message, $context) use ($reporter, $levels) {
				if (in_array($level, $levels)) $reporter->buffer($level, $message, $context);
			});
		}		
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		//
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}

}