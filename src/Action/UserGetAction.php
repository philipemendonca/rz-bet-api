<?php

namespace App\Action;

use App\Domain\User\Service\UserGet;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class UserGetAction
{
    private $userGet;

    public function __construct(UserGet $userGet)
    {
        $this->userGet = $userGet;
    }

    public function __invoke(
        ServerRequestInterface $request, 
        ResponseInterface $response
    ): ResponseInterface {
        $data = (array)$request->getParsedBody();
        
        $userId = $this->userGet->getUser($data['email']);
        
        $result = [
            'user_id' => $userId
        ];

        $response->getBody()->write((string)json_encode($result));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }
}