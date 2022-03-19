# Aliyun SLS Log For Laravel

# 说明
**此包来源于 [lokielse/laravel-sls](https://github.com/lokielse/laravel-sls)**

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Total Downloads][ico-downloads]][link-downloads]

## Install

Via Composer

``` bash
$ composer require pandasir/laravel-sls
```

## Config
If you’re on Laravel 5.5 or later the package will be auto-discovered. Otherwise you will need to manually configure it in your config/app.php.

Add following service providers into your providers array in `config/app.php`
```php
'providers' => array(
    // ...
    Lokielse\LaravelSLS\LaravelSLSServiceProvider::class,
),
'aliases' => array(
    // ...
    'SLSLog' => Lokielse\LaravelSLS\Facades\SlSLog::class,
),
```

If you’re need Log you can replace Log alias in your config/app.php
```php
//'Log'               => Illuminate\Support\Facades\Log::class,
'Log'                 => Lokielse\LaravelSLS\Facades\SLSLogWriter::class,
```

Publish `sls.php` to `config` folder

```sh
php artisan vendor:publish --provider="Lokielse\LaravelSLS\LaravelSLSServiceProvider" 
```

Edit your `.env` file

```bash
ALIYUN_ACCESS_KEY_ID=...
ALIYUN_ACCESS_KEY_SECRET=...
# https://help.aliyun.com/document_detail/29008.html
# 如杭州公网 cn-hangzhou.log.aliyuncs.com
# 如杭州内网 cn-hangzhou-intranet.log.aliyuncs.com
SLS_ENDPOINT=cn-hangzhou.log.aliyuncs.com
SLS_PROJECT=test-project
SLS_STORE=test-store
```
You should update `SLS_ENDPOINT` to `internal endpoint` in production mode

## Usage

First create a project and store at [Aliyun SLS Console](https://sls.console.aliyun.com/)

Then update `SLS_ENDPOINT`, `SLS_PROJECT`, `SLS_STORE` in `.env`

Push a test message to queue

```php
Log::info('Test Message', ['foobar'=>'2003']);

//or you can use `app('sls')` 

app('sls')->putLogs([
	'type' => 'test',
	'message' => json_encode(['This should use json_encode'])
]);

//or you can use `SLSLog` directly 

SLSLog::putLogs([
	'type' => 'test',
	'message' => json_encode(['This should use json_encode'])
]);
```

## Security

Create RAM access control at [Aliyun RAM Console](https://ram.console.aliyun.com)

1. Create a custom policy such as `AliyunSLSFullAccessFoobar`

	```
	{
	  "Version": "1",
	  "Statement": [
		{
		  "Action": "log:*",
		  "Resource": [
			"acs:log:*:*:project/test-project/logstore/test-store",
		  ],
		  "Effect": "Allow"
		}
	  ]
	}
	```

2. Create a user for you app such as `foobar`

3. Assign the policy `AliyunSLSFullAccessFoobar` to the user `foobar`

4. Create and get the `AccessKeyId` and `AccessKeySecret` for user `foorbar`

5. update `QUEUE_SLS_ACCESS_KEY` and `QUEUE_SLS_ACCESS_SECRET` in `.env`

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CONDUCT](CONDUCT.md) for details.

## Credits

- [Loki Else][link-author]
- [abrahamgreyson](https://github.com/abrahamgreyson/laravel-sls)
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/lokielse/laravel-sls.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/lokielse/laravel-sls/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/lokielse/laravel-sls.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/lokielse/laravel-sls.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/lokielse/laravel-sls.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/lokielse/laravel-sls
[link-travis]: https://travis-ci.org/lokielse/laravel-sls
[link-scrutinizer]: https://scrutinizer-ci.com/g/lokielse/laravel-sls/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/lokielse/laravel-sls
[link-downloads]: https://packagist.org/packages/lokielse/laravel-sls
[link-author]: https://github.com/lokielse
[link-contributors]: ../../contributors
