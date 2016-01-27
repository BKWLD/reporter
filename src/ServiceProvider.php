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
	public function boot() 	{

		// Define config publishing
		$this->publishes([
			__DIR__.'/../../config/reporter.php' => config_path('reporter.php')
		], 'config');

		// Disable
		if (!$this->app->make('config')->get('reporter.enable')) return;

		// If the request path is being ignored, don't log anything
		if (($path = request()->path())
			&& ($regex = config('reporter.ignore'))
			&& preg_match('#'.$regex.'#i', $path)) {
			return;
		}

		// If the app is running through console, listen for shutdown.  It's the only
		// event that fires after artisan finishes
		if ($this->app->runningInConsole()) {
			$this->app->shutdown(function() {
				$command = implode(' ', array_slice($_SERVER['argv'], 1));
				app('reporter')->write(['command' => $command]);
			});

		// Listen for request to be done and all after() filters to have run
		} else {
			$this->app->finish(function($request, $response) {
				app('reporter')->write([ 'request' => $request ]);
			});
		}

		// Add exceptions to what will be written by finish/shutdown
		$this->app->error(function(Exception $exception) {
			app('reporter')->exception($exception);
		});

		// Fatal errors abort finish/shutdown, so write the log immediately
		$this->app->fatal(function(Exception $exception) {
			app('reporter')->write(array(
				'request' => request(),
				'exception' => $exception
			));
		});

		// Buffer other log messages
		$levels = $this->app->make('config')->get('reporter.levels');
		if (!empty($levels)) {
			app('log')->listen(function($level, $message, $context) use ($reporter, $levels) {
				if (in_array($level, $levels)) $reporter->buffer($level, $message, $context);
			});
		}
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register() 	{

		// Merge own configs into user configs
		$this->mergeConfigFrom(__DIR__.'/../config/reporter.php', 'reporter');

		// Main reporter instance
		$this->app->singleton('reporter', function() {
			return new Reporter;
		});

		// Make a timer instance that can be resolved via the facade.
		$this->app->singleton('reporter.timer', function() {
			return new Processors\Timer();
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides() {
		return [
			'reporter',
			'reporter.timer',
		];
	}

}
