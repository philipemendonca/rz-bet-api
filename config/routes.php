<?php

use Slim\App;

return function (App $app) {
    $app->get('/', \App\Action\SiteAction::class);

    $app->get('/odds', \App\Action\OddsGetAction::class);

    $app->get('/competitions', \App\Action\CompetitionsGetAction::class);

    //$app->post('/users', \App\Action\UserCreateAction::class);

    //$app->post('/getusers', \App\Action\UserGetAction::class);

};
