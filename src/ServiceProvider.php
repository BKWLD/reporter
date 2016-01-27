<?php namespace Bkwld\Reporter;

use Exception;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;
use Monolog\Logger;
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

		// Disable based on config
		if (!config('reporter.enable')) return;

		// If the request path is being ignored, don't log anything
		if (($path = request()->path())
			&& ($regex = config('reporter.ignore'))
			&& preg_match('#'.$regex.'#i', $path)) {
			return;
		}

		// Listen for the http kernel to finish handling the request
		$this->app['events']->listen('kernel.handled', function ($request, $response) {

			// Log an error
			if ($response->exception) {
				// $this->app['reporter']->write([
				// 	'request' => $request,
				// 	'exception' => $response->exception,
				// ]);

			// Log a normal request
			} else {
				$this->app['reporter']->write([
					'request' => $request,
				]);
			}
		});

		$handler = new Handlers\Forwarder($this->app['reporter'], Logger::ERROR);
		$this->app['log']->getMonolog()->pushHandler($handler);



		throw new Exception("Error Processing Request", 1)


		/*


		// Buffer other log messages
		$levels = $this->app->make('config')->get('reporter.levels');
		if (!empty($levels)) {
			app('log')->listen(function($level, $message, $context) use ($reporter, $levels) {
				if (in_array($level, $levels)) $reporter->buffer($level, $message, $context);
			});
		}
		*/
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
		$this->app->singleton('reporter', function($app) {
			return (new Reporter($app['reporter.monolog']))->boot();
		});

		// The reporter monolog instance
		$this->app->singleton('reporter.monolog', function($app) {
			return new Logger('reporter');
		});

		// Make a timer instance that can be resolved via the facade.
		$this->app->singleton('reporter.timer', function($app) {
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
			'reporter.monolog',
			'reporter.timer',
		];
	}

}
