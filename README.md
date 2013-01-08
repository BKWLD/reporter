# Reporter

Reporter is a bundle for Laravel that is an alternative to the built in Profiler.  Instead of displaying output in the browser, it writes it to the disk in a log file that you can watch in realtime with `tail -f` or an app like Mac's Console.  This allows us to supplemental the built in Profiler output with some additional functionality:

* You can inspect AJAX requests
* POST variables are displayed
* The output rendering isn't affected by your application's CSS or JS
* You can inspect apps that are in a production environment

The output looks like:

```
REQUEST:   //momentum.dev/admin/projects/27 (POST)
TIME:      150.12ms
MEMORY:    6 MiB (PEAK: 6 MiB)
POST:     
  csrf_token: sKFUNhime8BU1LC2cudOeEH60VNMMk6lqQCyf9Ir
  title:      Barley Coffin Devise
  client_id:  31
  slug:       barley-coffin-devisee
  status:     draft
SQL:       4 queries
  (0.29ms) SELECT * FROM `projects` WHERE `id` = '27' LIMIT 1
  (0.18ms) SELECT * FROM `projects` WHERE `id` = '27' LIMIT 1
  (0.97ms) SELECT COUNT(*) AS `aggregate` FROM `projects` WHERE `slug` =
           'barley-coffin-devisee' AND `id` <> '27'
  (51.23ms) UPDATE `projects` SET `headline` = 'New headline', `updated_at` =
           '2013-01-08 10:31:32' WHERE `id` = '27'

```

Currently the output is inserted into the standard Laravel log location (/storage/logs/yyyy-mm-dd.log).

## Config

* enable - If false, reporter will not log anything

