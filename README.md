# Personnel Info Package via Hwacom HRepository

<a href="https://github.com/mozielin/Client-SSO/actions"><img src="https://github.com/mozielin/Client-SSO/workflows/PHP Composer/badge.svg" alt="Build Status"></a>
[![Total Downloads](http://poser.pugx.org/hwacom/Personnel-Info/downloads)](https://packagist.org/packages/hwacom/client-sso)
[![Latest Stable Version](http://poser.pugx.org/hwacom/Personnel-Info/v)](https://packagist.org/packages/hwacom/client-sso)
## 前言

要使用EIP後端登入，請先確定User表格式與EIP後端相同

## 安裝說明

```bash
composer require hwacom/personnel-info
```

## Service Provider設定 (Laravel 5.5^ 會自動掛載)

Composer安裝完後要需要修改 `config/app.php` 找到 providers 區域並添加:

```php
\Hwacom\PersonnelInfo\PersonnelInfoServiceProvider::class,
```

## Config設定檔發佈 

用下列指令會建立eip.php設定檔，需要在 `.env` 檔案中增加設定，
同時建立出eip_login語系檔

```bash
php artisan vendor:publish
```

 下列設定會自動增加在 `config/database.php(全域設定不會修改原本檔案)`

```php
    'hr'       => [
        'driver'         => 'mysql',
        'host'           => env('HR_DB_HOST', '127.0.0.1'),
        'port'           => env('HR_DB_PORT', '3306'),
        'database'       => env('HR_DB_DATABASE', 'forge'),
        'username'       => env('HR_DB_USERNAME', 'forge'),
        'password'       => env('HR_DB_PASSWORD', ''),
        'charset'        => 'utf8mb4',
        'collation'      => 'utf8mb4_unicode_ci',
        'prefix'         => '',
        'prefix_indexes' => true,
        'strict'         => false,
        'engine'         => null,
        'options'        => extension_loaded('pdo_mysql') ? array_filter([
            PDO::MYSQL_ATTR_SSL_CA     => env('MYSQL_ATTR_SSL_CA'),
            PDO::ATTR_EMULATE_PREPARES => true,
            PDO::MYSQL_ATTR_COMPRESS   => true,
        ]) : [],
    ],
```

在`.env` 中增加設定

```php
HR_DB_HOST     = 
HR_DB_PORT     = 
HR_DB_DATABASE = 
HR_DB_USERNAME = 
HR_DB_PASSWORD = 
```

指令建立相關檔案
```
php artisan personnel:install
```

