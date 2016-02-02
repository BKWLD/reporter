# Reporter

Reporter is a package for Laravel 5 (versions 3.x) and Laravel 4 (versions 2.x) that brings back functionality found in the profiler of Laravel 3.  Instead of displaying output in the browser, it writes to the disk in a log file that you can watch in realtime with `tail -f` or an app like Mac's Console.  This adds some advantages over a browser based profiler:

* You can inspect AJAX requests
* POST variables are logged
* The output rendering isn't affected by your application's CSS or JS
* You can inspect apps that are in a production environment

The output looks like:

```
---------------------------------------------------------------------
1/28/16 1:21:09 AM

REQUEST:   /admin/articles/19/edit?search=example
TIME:      111.46ms
TIMERS:    
  example: 2.36ms
MEMORY:    14 MB (PEAK: 14 MB)
INPUT:     
  search: example
SQL:       5 queries
  (0.46 ms) select * from `admins` where `admins`.`id` = '1' limit 1
  (0.30 ms) select * from `articles` where `articles`.`id` = '19' limit 1
  (0.44 ms) select * from `images` where `images`.`imageable_id` in ('19') and
            `images`.`imageable_type` = 'App\Article'
  (0.49 ms) select * from `articles` where `articles`.`id` = '19' limit 1
  (0.43 ms) select * from `images` where `images`.`imageable_id` in ('19') and
           `images`.`imageable_type` = 'App\Article'

INFO:      Hey, make sure to wear pants
```

Or, if you have style turned on:

![image](http://yo.bkwld.com/2b173b2z0M1f/Image%202016-01-27%20at%205.22.04%20PM.png)

Reporter also adds the ability to time blocks of code (as displayed in the examples "TIMERS" line).

## Installation

1. Add Reporter to composer.json: `"bkwld/reporter": "~3.0",` and do a composer install.
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

Reporter writes it's log to app/storage/logs/reporter.log.  I'd recommend running `tail -f storage/logs/reporter.log` from your project directory to follow it.

### Timer

Laravel 3 had a way to time events with it's profiler.  Reporter re-introduces this.

	Timer::start('example');
	// Some code that you are benchmarking
	Timer::stop('example');


Start() and stop() take a string as their argument that is used to match up the start and stop calls.  It is also displayed as the key for the timer in the log.  The above example would add lines like this to the log:

	TIMERS:
	  example: 20.02ms
