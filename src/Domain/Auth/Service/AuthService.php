<?php

namespace App\Domain\Auth\Service;

use \Tuupola\Middleware\JwtAuthentication;

function jwtAuth(): JwtAuthentication
{
    return new JwtAuthentication([
        "attribute" => "jwt",
        "secret" => getenv('JWT_SECRET_KEY')
    ]);
}
