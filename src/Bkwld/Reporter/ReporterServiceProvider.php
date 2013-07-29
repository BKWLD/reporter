<?php namespace Bkwld\Reporter;

use Exception;
use Illuminate\Support\ServiceProvider;
use Config;

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

		// Listen for request to be done.  Using "close" because "finish" comes too
		// late for ChromePHP but close should happen after regular "after" handlers
		$this->app->close(function($request, $response) use ($reporter) {
			$reporter->write($request);
		});
		
		// Write logs on fatal errors and exceptions
		$request = $this->app->make('request');
		$this->app->error(function(Exception $exception) use ($reporter, $request) {
			$reporter->write($request, $exception);
		});
		
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