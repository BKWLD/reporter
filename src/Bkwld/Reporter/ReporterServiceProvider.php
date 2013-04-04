<?php namespace Bkwld\Reporter;

use Illuminate\Support\ServiceProvider;
use \Config;

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
		return;
		
		// Disable
		if (!Config::get('reporter::enable')) return;

		/**
		 * STOPPING WORKING ON THIS UNIL L4 IS A OFFICIAL OR A PROFILING
		 * COMPONENT IS ADDED.
		 */

		// Attach built in profiler listeners if it's not turned on
		if (!Config::get('application.profiler')) {
			Laravel\Profiling\Profiler::attach();
		}

		// Listen for request to be done
		Event::listen('laravel.done', function($response) {
			$r = new Reporter();
			$r->write();
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