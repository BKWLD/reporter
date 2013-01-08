# Reporter

Reporter is a bundle for Laravel that is alternative to the build in profiler.  While the browser based profiler is great, Reporter exists to add some functionality that is missing from it:

* Inspecting AJAX requests
* Displaying POST variables
* Rendering output NOT in the browser

The latter feature is nice when you need to debug an app that is in production or if you have CSS/JS that conflicts with the built in web view.  It's output is dumped to the Laravel's standard log and you can watch it in realtime with `tail -f` or an app like Mac's Console.  The output looks like:

```
REQUEST:   //momentum.dev/admin/projects/27 (POST)
TIME:      150.12ms
MEMORY:    6 MiB (PEAK: 6 MiB)
POST::     
  csrf_token:      sKFUNhime8BU1LC2cudOeEH60VNMMk6lqQCyf9Ir
  title:           Barley Coffin Devise
  client_id:       31
  slug:            barley-coffin-devisee
  status:          draft
SQL::      
  (0.29ms) SELECT * FROM `projects` WHERE `id` = '27' LIMIT 1
  (0.18ms) SELECT * FROM `projects` WHERE `id` = '27' LIMIT 1
  (0.97ms) SELECT COUNT(*) AS `aggregate` FROM `projects` WHERE `slug` =
           'barley-coffin-devisee' AND `id` <> '27'
  (51.23ms) UPDATE `projects` SET `headline` = 'New headline', `updated_at` =
           '2013-01-08 10:31:32' WHERE `id` = '27'

```