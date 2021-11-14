# LaravelESLogs

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]

Structured logging for Laravel into Elasticsearch.  Log your Requests and Jobs easily alongside key contextual information to elastic search indexes for easy searching and analysis.



Take a look at [contributing.md](contributing.md) to see a to do list.

## Installation

Via Composer

``` bash
$ composer require twentysix22/laraveleslogs
```

## Usage

Publish the provider in laravel:
``` bash
php artisan vendor:publish --provider="Twentysix22\LaravelESLogs\LaravelESLogsServiceProvider"
```
Add the route middleware alias to the app/Http/Kernel.php
``` bash
protected $routeMiddleware = [
...,
...,

'log' => \Twentysix22\LaravelESLogs\Services\Logging\Requests\LogRequest::class,
```
Configure .env
``` bash
LOG_GROUP=some-group
LOG_ELASTICSEARCH_HOST=localhost:9200
LOG_ELASTICSEARCH_KEEP_DAYS=5
LOG_JOBS=true
LOG_JOB_ATTEMPTS=true
LOG_REQUESTS=true
LOG_REDACTION_INK=REDACTED
LOG_ELASTICSEARCH_AUTH_TYPE=url   [url or apikey]
LOG_ELASTICSEARCH_AUTH_API_ID=
LOG_ELASTICSEARCH_AUTH_API_KEY=
LOG_REQUEST_MAX_SIZE= (size in characters)
LOG_RESPONSE_MAX_SIZE= (size in characters)
LOG_ELASTICSEARCH_INDEX_DATE_PATTERN="Y.m.d"
```
Add the service provider to your providers array in config/app.php
``` bash
\Twentysix22\LaravelESLogs\LaravelESLogsServiceProvider::class,
```


## Initialise the Elasticsearch indices.
This step is not strictly necessary unless you wish to reset any current indices.
``` bash 
php artisan laraveleslogs:configure
```

## Configure auto cleanup of logs by retention days.
You can run a daily command to clear up old logs which will use the `LOG_ELASTICSEARCH_KEEP_DAYS` env variable number of days retention.
``` bash 
php artisan laraveleslogs:tidy
```

## Logging Requests
Apply the middleware 'log:{namespace}' to your routes to start logging requests. (you can omit the namespace param if you want to but its useful to specify for clarity in logs)
eg:
``` bash 
Route::get('/', function () {
    return view('welcome');
})->middleware('log:home');
```

You can add this middleware to individual routes, groups, or even your global route middlware if you wish to simply log out every request/response. (but we recommend you be more specific in your logging :-) ) 
## Adding Request Context
You can use the Trait `use ReportsRequestContext;` in your request controllers.  This gives a coupld of options to apply context to your logs.

``` php 
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Twentysix22\LaravelESLogs\Services\Logging\Requests\ReportsRequestContext;

class TestController extends Controller
{
    use ReportsRequestContext;

    public function test()
    {
        $this->logContext([
            'key' => 'value',
            'key2' => 'value2',
        ]);

        $this->logGlobalContext([
            'globalkey' => 'value',
            'globalkey2' => 'value2',
        ]);

        return response()->json([
           'hello' => 'world',
        ]);
    }
}
```


## Logging Jobs
Logging of jobs will be enabled by default if you configure your .env as above.

## Logging Job Context
You can log out context to your jobs using similar trait to requests above - add `use ReportsJobContext;` to your Job.
eg:
``` php 
use Twentysix22\LaravelESLogs\Services\Logging\Jobs\ReportsJobContext;

class TestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    use ReportsJobContext;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->logContext([
            'key' => 'value',
            'key2' => 'value2',
        ]);

        $this->logGlobalContext([
            'globalkey' => 'value',
            'globalkey2' => 'value2',
        ]);
    }
}
```

#### Setting custom context name.
You can set a custom context name using traits `ReportsJobContext` or `ReportsRequestContext` 
``` php 
$this->setContextName('custom-context-name');
```


## Redaction of sensitive information from logs.
Its important that you do not log out sensitive information in logs for various data protection reasons.  You can configure a list of sensitive keys that you would like to redact from your logs in your `/config/laraveleslogs.php`:

``` php 
// Keys used to catch elements in logging array keys - will be replaced with redaction ink
    // and not displayed in logs.
    'redaction' => [
        'password',
        'password_confirmation',
        'authorization',
        'telephone_number',
        'email',
        'algolia_key',
        'access-token'
    ],

    // The ink used to redact sensitive keys in logs.
    'redaction_ink' => config('LOG_REDACTION_INK', '[--REDACTED--]'),
```

You can also specify the redaction ink string that will replace these sensitive keys by configuring the `LOG_REDACTION_INK` env variable.

## Change log

Please see the [changelog](changelog.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [contributing.md](contributing.md) for details and a todolist.

## Security

If you discover any security related issues, please email author email instead of using the issue tracker.

## Credits

- [author name][link-author]
- [All Contributors][link-contributors]

## License

license. Please see the [license file](license.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/twentysix22/laraveleslogs.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/twentysix22/laraveleslogs.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/twentysix22/laraveleslogs/master.svg?style=flat-square
[ico-styleci]: https://styleci.io/repos/12345678/shield

[link-packagist]: https://packagist.org/packages/twentysix22/laraveleslogs
[link-downloads]: https://packagist.org/packages/twentysix22/laraveleslogs
[link-travis]: https://travis-ci.org/twentysix22/laraveleslogs
[link-styleci]: https://styleci.io/repos/12345678
[link-author]: https://github.com/twentysix22
[link-contributors]: ../../contributors
