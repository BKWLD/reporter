<?php namespace Bkwld\Reporter;

// Deps
use DB;
use Exception;
use Illuminate\Foundation\Http\Events\RequestHandled;
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
			__DIR__.'/../config/reporter.php' => config_path('reporter.php')
		], 'config');

		// Disable based on config
		if (!config('reporter.enable')) return;

		// If the request path is being ignored, don't log anything
		if (($path = request()->path())
			&& ($regex = config('reporter.ignore'))
			&& preg_match('#'.$regex.'#i', $path)) {
			return;
		}

		// Turn on Query logging
		DB::connection()->enableQueryLog();

		// Listen for the http kernel to finish handling the request
		$this->app['events']->listen(RequestHandled::class, function(RequestHandled $event) {

			// Exceptions will get caught by the log listener
			if (isset($event->response->exception)) return;

			// Log a normal request
			$this->app['reporter']->write([
				'request' => $event->request,
			]);
		});

		// Listen for Laravel to log errors or manual Log::info() (etc) calls
		$this->app['events']->listen('illuminate.log', function($level, $message, $context) {

			// Log exceptions
			if (is_a($message, Exception::class)) {
				$this->app['reporter']->write([
					'request' => request(),
					'exception' => $message,
				]);

			// Log developer messages
			} else if (($levels = config('reporter.levels')) && in_array($level, $levels)) {
				$this->app['reporter']->buffer($level, $message, $context);
			}
		});

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
