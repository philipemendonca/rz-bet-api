<?php

use Slim\App;

return function (App $app) {
    $app->get('/', \App\Action\SiteAction::class);

    $app->post('/users', \App\Action\UserCreateAction::class);
    $app->get('/odds', \App\Action\OddsGetAction::class);
};