<?php

use Slim\App;
use App\Action\JwtAuthAction;
use function App\Domain\Auth\Service\jwtAuth;

return function (App $app) {
    $app->get('/', \App\Action\SiteAction::class);
        //->add((new JwtAuthAction()))
        //->add(jwtAuth());

    //$app->post('/users', \App\Action\UserCreateAction::class);

    //$app->post('/getusers', \App\Action\UserGetAction::class);

    $app->post('/token-create', \App\Action\TokenCreateAction::class);

    $app->post('/refresh-token', \App\Action\TokenRefreshAction::class);

    $app->get('/odds', \App\Action\OddsGetAction::class);
        //->add((new JwtAuthAction()))
        //->add(jwtAuth());
};
