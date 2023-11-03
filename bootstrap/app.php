<?php

$app = new Illuminate\Foundation\Application(
    realpath(__DIR__.'/../')
);

$env = $app->detectEnvironment(function()
{
    return getenv('APP_ENV') ?: 'local';
});
$fn = ".env.{$env}";

$app->loadEnvironmentFrom(file_exists(base_path($fn)) ? $fn : '.env');


return $app;

