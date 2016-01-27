<?php namespace Bkwld\Reporter;

use Exception;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;
use Symfony\Component\Console\Input\ArgvInput;

class ServiceProvider extends LaravelServiceProvider {


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

		$this->publishes([
			__DIR__.'/../../config/config.php' => config_path('reporter.php')
		], 'reporter');

		// Disable
		if (!$this->app->make('config')->get('reporter.enable')) return;

		// Make a timer instance that can be resolved via the facade.
		$this->app->singleton('timer', function() {
			return new Processors\Timer();
		});

		// Init
		$reporter = new Reporter();
		$request = $this->app->make('request');

		// If the request path is being ignored, don't log anything
		if (($path = $request->path())
			&& ($regex = $this->app->make('config')->get('reporter::ignore'))
			&& preg_match('#'.$regex.'#i', $path)) {
			return;
		}

		// If the app is running through console, listen for shutdown.  It's the only
		// event that fires after artisan finishes
		if ($this->app->runningInConsole()) {
			$this->app->shutdown(function() use ($reporter) {
				$command = implode(' ', array_slice($_SERVER['argv'], 1));
				$reporter->write(array('command' => $command));
			});

		// Listen for request to be done and all after() filters to have run
		} else {
			$this->app->finish(function($request, $response) use ($reporter) {
				$reporter->write(array( 'request' => $request ));
			});
		}

		// Add exceptions to what will be written by finish/shutdown
		$this->app->error(function(Exception $exception) use ($reporter, $request) {
			$reporter->exception($exception);
		});

		// Fatal errors abort finish/shutdown, so write the log immediately
		$this->app->fatal(function(Exception $exception) use ($reporter, $request) {
			$reporter->write(array(
				'request' => $request,
				'exception' => $exception
			));
		});

		// Buffer other log messages
		$levels = $this->app->make('config')->get('reporter::levels');
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
