# Reporter

Reporter is a bundle for Laravel that is an alternative to the built in Profiler.  Instead of displaying output in the browser, it writes it to the disk in a log file that you can watch in realtime with `tail -f` or an app like Mac's Console.  This allows us to supplemental the built in Profiler output with some additional functionality:

* You can inspect AJAX requests
* POST variables are displayed
* The output rendering isn't affected by your application's CSS or JS
* You can inspect apps that are in a production environment

The output looks like:

```
2013-01-08 14:17:15 REPORTER - 

REQUEST:   //momentum.dev/admin/users/attach/1 (POST,XHR)
TIME:      50.70ms
MEMORY:    5.75 MiB (PEAK: 5.75 MiB)
POST:      
  parent_id: 27
SQL:       2 queries
  (0.27ms) SELECT * FROM `users` WHERE `id` = '1' LIMIT 1
  (2.24ms) INSERT INTO `project_user` (`user_id`, `project_id`, `created_at`,
           `updated_at`) VALUES ('1', '27', '2013-01-08 14:17:15', '2013-01-08
           14:17:15')

------------------------------------------------------------------------
```

Or, if you have style turned on:

![image](http://f.cl.ly/items/0c381c1J1W1d2w1a1k3k/Screen%20Shot%202013-01-08%20at%202.18.50%20PM.png)

Currently the output is inserted into the standard Laravel log location (/storage/logs/yyyy-mm-dd.log).

## Installation

You'll probably want to set it to autostart in your bundles.php:

    'reporter' => array('auto' => true),

Also, if you want your SQL to be logged, make sure the config variable `database.profile` is set to true.

## Config

* `enable` - If false, Reporter will do nothing
* `style` - Add color and style codes for output in a Terminal
