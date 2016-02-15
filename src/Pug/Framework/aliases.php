<?php

$aliases = [
    'App'        => Pug\Framework\Application::class,
    'Cache'      => Molovo\Amnesia\Cache::class,
    'Collection' => Molovo\Interrogate\Collection::class,
    'Config'     => Pug\Framework\Config::class,
    'Controller' => Pug\Http\Controller::class,
    'Crypt'      => Pug\Crypt\Encrypter::class,
    'Csrf'       => Pug\Http\Middleware\Csrf::class,
    'Database'   => Molovo\Interrogate\Database::class,
    'Env'        => Pug\Framework\Environment::class,
    'Honeypot'   => Pug\Http\Middleware\Honeypot::class,
    'Input'      => Pug\Http\Request\Input::class,
    'Middleware' => Pug\Http\Interfaces\Middleware::class,
    'Model'      => Molovo\Interrogate\Model::class,
    'Query'      => Molovo\Interrogate\Query::class,
    'Redirect'   => Pug\Http\Redirect::class,
    'Request'    => Pug\Http\Request::class,
    'Response'   => Pug\Http\Response::class,
    'Route'      => Molovo\Traffic\Route::class,
    'Router'     => Molovo\Traffic\Router::class,
    'Session'    => Pug\Framework\Session::class,
    'Url'        => Pug\Http\Url::class,
    'View'       => Pug\Framework\View::class,
];

foreach ($aliases as $alias => $class) {
    class_alias($class, $alias);
}
