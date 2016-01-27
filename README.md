# Reporter

Reporter is a package for Laravel 4 that brings back functionality found in the profiler of Laravel 3.  Instead of displaying output in the browser, it writes to the disk in a log file that you can watch in realtime with `tail -f` or an app like Mac's Console.  This adds some advantages over a browser based profiler:

* You can inspect AJAX requests
* POST variables are logged
* The output rendering isn't affected by your application's CSS or JS
* You can inspect apps that are in a production environment

The output looks like:

```
------------------------------------------------------------------------
7/15/13 2:21:17 PM

REQUEST:   /about?example=val
TIME:      18.53ms
TIMERS:    
  content: 9.04ms
MEMORY:    5 MB (PEAK: 5.25 MB)
INPUT:     
  example: val
SQL:       2 queries
  (0.66 ms) select * from `articles` where `visible` = 1 order by
           `articles`.`created_at` desc limit 3
  (0.62 ms) select * from `ticker_posts` where `visible` = 1 order by
           `ticker_posts`.`created_at` desc

------------------------------------------------------------------------
```

Or, if you have style turned on:

![image](http://f.cl.ly/items/233e2H0V042S1L0v2r3m/Screen%20Shot%202013-07-15%20at%202.50.57%20PM.png)

Reporter also adds the ability to time blocks of code (as displayed in the examples "TIMERS" line).

## Installation

1. Add reporter to composer.json: `"bkwld/reporter": "~2.0",` and do a composer install.
2. Add `'Bkwld\Reporter\ServiceProvider',` to your app/config/app.php's providers array.
3. If you plan to use timers, add `'Timer' => 'Bkwld\Reporter\Facades\Timer',` to the app/config/app.php's aliases array.

### Config

* `enable` - If false, Reporter will do nothing.  By default, this is set to false for the "production" enviornment **IF** you publish the package's config file to your app/config directory.
* `style` - Add color and style codes for output in a Terminal.
* `error_log` - If true, also write logs to the PHP `error_log()`
* `levels` - An array of log levels that should be shown
* `ignore` -  A regex string.  If the request path matches, no log will be written.

## Usage

### Logging

Reporter writes it's log to app/storage/logs/reporter.log.  I'd recommend running `tail -f app/storage/logs/reporter.log` from your project directory to follow it.

### Timer

Laravel 3 had a way to time events with it's profiler.  Reporter re-introduces this.

	Timer::start('example');
	// Some code that you are benchmarking
	Timer::stop('example');


Start() and stop() take a string as their argument that is used to match up the start and stop calls.  It is also displayed as the key for the timer in the log.  The above example would add lines like this to the log:

	TIMERS:
	  example: 20.02ms
