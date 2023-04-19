<?php

use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | This option defines the default log channel that gets used when writing
    | messages to the logs. The name specified in this option should match
    | one of the channels defined in the "channels" configuration array.
    |
    */

    'default' => env('LOG_CHANNEL', 'stack'),

    /*
    |--------------------------------------------------------------------------
    | Deprecations Log Channel
    |--------------------------------------------------------------------------
    |
    | This option controls the log channel that should be used to log warnings
    | regarding deprecated PHP and library features. This allows you to get
    | your application ready for upcoming major versions of dependencies.
    |
    */

    'deprecations' => [
        'channel' => env('LOG_DEPRECATIONS_CHANNEL', 'null'),
        'trace' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application. Out of
    | the box, Laravel uses the Monolog PHP logging library. This gives
    | you a variety of powerful log handlers / formatters to utilize.
    |
    | Available Drivers: "single", "daily", "slack", "syslog",
    |                    "errorlog", "monolog",
    |                    "custom", "stack"
    |
    */

    'channels' => [
        'stack' => [
            'driver' => 'stack',
            'channels' => ['single'],
            'ignore_exceptions' => false,
        ],

        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
        ],

        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
        ],

        'slack' => [
            'driver' => 'slack',
            'url' => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => 'Laravel Log',
            'emoji' => ':boom:',
            'level' => env('LOG_LEVEL', 'critical'),
        ],

        'papertrail' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'debug'),
            'handler' => env('LOG_PAPERTRAIL_HANDLER', SyslogUdpHandler::class),
            'handler_with' => [
                'host' => env('PAPERTRAIL_URL'),
                'port' => env('PAPERTRAIL_PORT'),
                'connectionString' => 'tls://'.env('PAPERTRAIL_URL').':'.env('PAPERTRAIL_PORT'),
            ],
        ],

        'stderr' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'debug'),
            'handler' => StreamHandler::class,
            'formatter' => env('LOG_STDERR_FORMATTER'),
            'with' => [
                'stream' => 'php://stderr',
            ],
        ],

        'syslog' => [
            'driver' => 'syslog',
            'level' => env('LOG_LEVEL', 'debug'),
        ],

        'errorlog' => [
            'driver' => 'errorlog',
            'level' => env('LOG_LEVEL', 'debug'),
        ],

        'null' => [
            'driver' => 'monolog',
            'handler' => NullHandler::class,
        ],

        'emergency' => [
            'path' => storage_path('logs/laravel.log'),
        ],

        'login' => [
            'driver' => 'daily',
            'path' => storage_path('logs/login/login.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
            'max_files' => 10,
            'max_size' => 1024 * 1024 * 10, // 10MB
        ],

        'login_error' => [
            'driver' => 'daily',
            'path' => storage_path('logs/login/login_error.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
            'max_files' => 10,
            'max_size' => 1024 * 1024 * 10, // 10MB
        ],


        'register' => [
            'driver' => 'daily',
            'path' => storage_path('logs/register/register.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
            'max_files' => 10,
            'max_size' => 1024 * 1024 * 10, // 10MB
        ],

        'agreement_update' => [
            'driver' => 'daily',
            'path' => storage_path('logs/agreement/agreement_update.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
            'max_files' => 10,
            'max_size' => 1024 * 1024 * 10, // 10MB
        ],

        'agreement_error' => [
            'driver' => 'daily',
            'path' => storage_path('logs/agreement/agreement_update.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
            'max_files' => 10,
            'max_size' => 1024 * 1024 * 10, // 10MB
        ],

        'article_store' => [
            'driver' => 'daily',
            'path' => storage_path('logs/article/article_store.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
            'max_files' => 10,
            'max_size' => 1024 * 1024 * 10, // 10MB
        ],

        'article_store_error' => [
            'driver' => 'daily',
            'path' => storage_path('logs/article/article_store_error.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
            'max_files' => 10,
            'max_size' => 1024 * 1024 * 10, // 10MB
        ],

        'article_update' => [
            'driver' => 'daily',
            'path' => storage_path('logs/article/article_update.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
            'max_files' => 10,
            'max_size' => 1024 * 1024 * 10, // 10MB
        ],

        'article_update_error' => [
            'driver' => 'daily',
            'path' => storage_path('logs/article/article_update_error.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
            'max_files' => 10,
            'max_size' => 1024 * 1024 * 10, // 10MB
        ],

        'article_destroy' => [
            'driver' => 'daily',
            'path' => storage_path('logs/article/article_destroy.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
            'max_files' => 10,
            'max_size' => 1024 * 1024 * 10, // 10MB
        ],

        'article_destroy_error' => [
            'driver' => 'daily',
            'path' => storage_path('logs/article/article_destroy_error.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
            'max_files' => 10,
            'max_size' => 1024 * 1024 * 10, // 10MB
        ],

        'asset_check_update' => [
            'driver' => 'daily',
            'path' => storage_path('logs/asset_check/asset_check_update.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
            'max_files' => 10,
            'max_size' => 1024 * 1024 * 10, // 10MB
        ],

        'asset_check_update_error' => [
            'driver' => 'daily',
            'path' => storage_path('logs/asset_check/asset_check_update_error.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
            'max_files' => 10,
            'max_size' => 1024 * 1024 * 10, // 10MB
        ],

        'asset_check_status' => [
            'driver' => 'daily',
            'path' => storage_path('logs/asset_check/asset_check_status.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
            'max_files' => 10,
            'max_size' => 1024 * 1024 * 10, // 10MB
        ],

        'asset_check_status_error' => [
            'driver' => 'daily',
            'path' => storage_path('logs/asset_check/asset_check_status_error.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
            'max_files' => 10,
            'max_size' => 1024 * 1024 * 10, // 10MB
        ],

        'award_update' => [
            'driver' => 'daily',
            'path' => storage_path('logs/award/award_update.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
            'max_files' => 10,
            'max_size' => 1024 * 1024 * 10, // 10MB
        ],

        'award_update_error' => [
            'driver' => 'daily',
            'path' => storage_path('logs/award/award_update_error.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
            'max_files' => 10,
            'max_size' => 1024 * 1024 * 10, // 10MB
        ],

        'balance_check' => [
            'driver' => 'daily',
            'path' => storage_path('logs/balance_check/balance_check.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
            'max_files' => 10,
            'max_size' => 1024 * 1024 * 10, // 10MB
        ],

        'balance_check_error' => [
            'driver' => 'daily',
            'path' => storage_path('logs/balance_check/balance_check_error.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
            'max_files' => 10,
            'max_size' => 1024 * 1024 * 10, // 10MB
        ],

        'blockip_store' => [
            'driver' => 'daily',
            'path' => storage_path('logs/blockip/blockip_store.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
            'max_files' => 10,
            'max_size' => 1024 * 1024 * 10, // 10MB
        ],

        'blockip_store_error' => [
            'driver' => 'daily',
            'path' => storage_path('logs/blockip/blockip_store_error.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
            'max_files' => 10,
            'max_size' => 1024 * 1024 * 10, // 10MB
        ],

        'blockip_destroy' => [
            'driver' => 'daily',
            'path' => storage_path('logs/blockip/blockip_destroy.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
            'max_files' => 10,
            'max_size' => 1024 * 1024 * 10, // 10MB
        ],

        'blockip_destroy_error' => [
            'driver' => 'daily',
            'path' => storage_path('logs/blockip/blockip_destroy_error.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
            'max_files' => 10,
            'max_size' => 1024 * 1024 * 10, // 10MB
        ],

        'category_store' => [
            'driver' => 'daily',
            'path' => storage_path('logs/category/category_store.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
            'max_files' => 10,
            'max_size' => 1024 * 1024 * 10, // 10MB
        ],

        'category_store_error' => [
            'driver' => 'daily',
            'path' => storage_path('logs/category/category_store_error.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
            'max_files' => 10,
            'max_size' => 1024 * 1024 * 10, // 10MB
        ],

        'category_update' => [
            'driver' => 'daily',
            'path' => storage_path('logs/category/category_update.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
            'max_files' => 10,
            'max_size' => 1024 * 1024 * 10, // 10MB
        ],

        'category_update_error' => [
            'driver' => 'daily',
            'path' => storage_path('logs/category/category_update_error.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
            'max_files' => 10,
            'max_size' => 1024 * 1024 * 10, // 10MB
        ],

        'category_destroy' => [
            'driver' => 'daily',
            'path' => storage_path('logs/category/category_destroy.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
            'max_files' => 10,
            'max_size' => 1024 * 1024 * 10, // 10MB
        ],

        'category_destroy_error' => [
            'driver' => 'daily',
            'path' => storage_path('logs/category/category_destroy_error.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
            'max_files' => 10,
            'max_size' => 1024 * 1024 * 10, // 10MB
        ],

        'currency_store' => [
            'driver' => 'daily',
            'path' => storage_path('logs/currency/currency_store.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
            'max_files' => 10,
            'max_size' => 1024 * 1024 * 10, // 10MB
        ],

        'currency_store_error' => [
            'driver' => 'daily',
            'path' => storage_path('logs/currency/currency_store_error.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
            'max_files' => 10,
            'max_size' => 1024 * 1024 * 10, // 10MB
        ],

        'currency_update' => [
            'driver' => 'daily',
            'path' => storage_path('logs/currency/currency_update.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
            'max_files' => 10,
            'max_size' => 1024 * 1024 * 10, // 10MB
        ],

        'currency_update_error' => [
            'driver' => 'daily',
            'path' => storage_path('logs/currency/currency_update_error.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
            'max_files' => 10,
            'max_size' => 1024 * 1024 * 10, // 10MB
        ],

        'currency_destroy' => [
            'driver' => 'daily',
            'path' => storage_path('logs/currency/currency_destroy.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
            'max_files' => 10,
            'max_size' => 1024 * 1024 * 10, // 10MB
        ],

        'currency_destroy_error' => [
            'driver' => 'daily',
            'path' => storage_path('logs/currency/currency_destroy_error.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
            'max_files' => 10,
            'max_size' => 1024 * 1024 * 10, // 10MB
        ],

        'customer_store' => [
            'driver' => 'daily',
            'path' => storage_path('logs/customer/customer_store.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
            'max_files' => 10,
            'max_size' => 1024 * 1024 * 10, // 10MB
        ],

        'customer_store_error' => [
            'driver' => 'daily',
            'path' => storage_path('logs/customer/customer_store_error.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
            'max_files' => 10,
            'max_size' => 1024 * 1024 * 10, // 10MB
        ],

        'customer_update' => [
            'driver' => 'daily',
            'path' => storage_path('logs/customer/customer_update.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
            'max_files' => 10,
            'max_size' => 1024 * 1024 * 10, // 10MB
        ],

        'customer_update_error' => [
            'driver' => 'daily',
            'path' => storage_path('logs/customer/customer_update_error.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
            'max_files' => 10,
            'max_size' => 1024 * 1024 * 10, // 10MB
        ],

        'customer_destroy' => [
            'driver' => 'daily',
            'path' => storage_path('logs/customer/customer_destroy.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
            'max_files' => 10,
            'max_size' => 1024 * 1024 * 10, // 10MB
        ],

        'customer_destroy_error' => [
            'driver' => 'daily',
            'path' => storage_path('logs/customer/customer_destroy_error.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
            'max_files' => 10,
            'max_size' => 1024 * 1024 * 10, // 10MB
        ],

        'customer_kick' => [
            'driver' => 'daily',
            'path' => storage_path('logs/customer/customer_kick.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
            'max_files' => 10,
            'max_size' => 1024 * 1024 * 10, // 10MB
        ],

        'customer_kick_error' => [
            'driver' => 'daily',
            'path' => storage_path('logs/customer/customer_kick_error.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
            'max_files' => 10,
            'max_size' => 1024 * 1024 * 10, // 10MB
        ],

        'customer_password1' => [
            'driver' => 'daily',
            'path' => storage_path('logs/customer/customer_password1.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
            'max_files' => 10,
            'max_size' => 1024 * 1024 * 10, // 10MB
        ],

        'customer_password1_error' => [
            'driver' => 'daily',
            'path' => storage_path('logs/customer/customer_password1_error.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
            'max_files' => 10,
            'max_size' => 1024 * 1024 * 10, // 10MB
        ],

        'customer_password2' => [
            'driver' => 'daily',
            'path' => storage_path('logs/customer/customer_password2.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
            'max_files' => 10,
            'max_size' => 1024 * 1024 * 10, // 10MB
        ],

        'customer_password2_error' => [
            'driver' => 'daily',
            'path' => storage_path('logs/customer/customer_password2_error.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
            'max_files' => 10,
            'max_size' => 1024 * 1024 * 10, // 10MB
        ],

        'financial_balance' => [
            'driver' => 'daily',
            'path' => storage_path('logs/customer/financial_balance.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
            'max_files' => 10,
            'max_size' => 1024 * 1024 * 10, // 10MB
        ],

        'financial_balance_error' => [
            'driver' => 'daily',
            'path' => storage_path('logs/customer/financial_balance_error.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
            'max_files' => 10,
            'max_size' => 1024 * 1024 * 10, // 10MB
        ],

        'financial_asset' => [
            'driver' => 'daily',
            'path' => storage_path('logs/customer/financial_asset.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
            'max_files' => 10,
            'max_size' => 1024 * 1024 * 10, // 10MB
        ],

        'financial_asset_error' => [
            'driver' => 'daily',
            'path' => storage_path('logs/customer/financial_asset_error.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
            'max_files' => 10,
            'max_size' => 1024 * 1024 * 10, // 10MB
        ],

        'financial_asset' => [
            'driver' => 'daily',
            'path' => storage_path('logs/customer/financial_asset.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
            'max_files' => 10,
            'max_size' => 1024 * 1024 * 10, // 10MB
        ],

        'financial_asset_error' => [
            'driver' => 'daily',
            'path' => storage_path('logs/customer/financial_asset_error.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
            'max_files' => 10,
            'max_size' => 1024 * 1024 * 10, // 10MB
        ],

        'financial_integration' => [
            'driver' => 'daily',
            'path' => storage_path('logs/customer/financial_integration.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
            'max_files' => 10,
            'max_size' => 1024 * 1024 * 10, // 10MB
        ],

        'financial_integration_error' => [
            'driver' => 'daily',
            'path' => storage_path('logs/customer/financial_integration_error.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
            'max_files' => 10,
            'max_size' => 1024 * 1024 * 10, // 10MB
        ],

        'financial_platform_coin' => [
            'driver' => 'daily',
            'path' => storage_path('logs/customer/financial_platform_coin.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
            'max_files' => 10,
            'max_size' => 1024 * 1024 * 10, // 10MB
        ],

        'financial_platform_coin_error' => [
            'driver' => 'daily',
            'path' => storage_path('logs/customer/financial_platform_coin_error.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
            'max_files' => 10,
            'max_size' => 1024 * 1024 * 10, // 10MB
        ],

        'withdraw_financial_balance' => [
            'driver' => 'daily',
            'path' => storage_path('logs/customer/withdraw_financial_balance.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
            'max_files' => 10,
            'max_size' => 1024 * 1024 * 10, // 10MB
        ],

        'withdraw_financial_balance_error' => [
            'driver' => 'daily',
            'path' => storage_path('logs/customer/withdraw_financial_balance_error.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
            'max_files' => 10,
            'max_size' => 1024 * 1024 * 10, // 10MB
        ],

        'withdraw_financial_asset' => [
            'driver' => 'daily',
            'path' => storage_path('logs/customer/withdraw_financial_asset.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
            'max_files' => 10,
            'max_size' => 1024 * 1024 * 10, // 10MB
        ],

        'withdraw_financial_asset_error' => [
            'driver' => 'daily',
            'path' => storage_path('logs/customer/withdraw_financial_asset_error.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
            'max_files' => 10,
            'max_size' => 1024 * 1024 * 10, // 10MB
        ],

        'withdraw_financial_integration' => [
            'driver' => 'daily',
            'path' => storage_path('logs/customer/withdraw_financial_integration.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
            'max_files' => 10,
            'max_size' => 1024 * 1024 * 10, // 10MB
        ],

        'withdraw_financial_integration_error' => [
            'driver' => 'daily',
            'path' => storage_path('logs/customer/withdraw_financial_integration_error.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
            'max_files' => 10,
            'max_size' => 1024 * 1024 * 10, // 10MB
        ],

        'withdraw_financial_platform_coin' => [
            'driver' => 'daily',
            'path' => storage_path('logs/customer/withdraw_financial_platform_coin.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
            'max_files' => 10,
            'max_size' => 1024 * 1024 * 10, // 10MB
        ],

        'withdraw_financial_platform_coin_error' => [
            'driver' => 'daily',
            'path' => storage_path('logs/customer/withdraw_financial_platform_coin_error.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
            'max_files' => 10,
            'max_size' => 1024 * 1024 * 10, // 10MB
        ],


        'debug' => [
            'driver' => 'daily',
            'path' => storage_path('logs/debug.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
            'max_files' => 10,
            'max_size' => 1024 * 1024 * 10, // 10MB
        ],

        'warning' => [
            'driver' => 'daily',
            'path' => storage_path('logs/warning.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
            'max_files' => 10,
            'max_size' => 1024 * 1024 * 10, // 10MB
        ],
    ],

];
