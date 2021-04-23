<?php

namespace App\Action;

use App\Domain\User\Service\UserCreate;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class UserGetAction
{
    private $userCreate;

    public function __construct(UserCreate $userCreate)
    {
        $this->userCreate = $userCreate;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $data = [];
        $status = 0;

        try {
            $data = (array)$request->getParsedBody();
            $userId = $this->userCreate->createUser($data);
            $result = [
                'user_id' => $userId
            ];
            $status = 201;
        } catch (\Throwable $th) {
            $result = [
                'message' => $th->getMessage()
            ];
            $status = $th->getCode();
        }

        $response->getBody()->write((string)json_encode($result));

        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }
}
