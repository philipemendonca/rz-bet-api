<?php

namespace App\Action;

use App\Domain\Token\Service\TokenService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class TokenCreateAction
{
    private $tokenService;

    public function __construct(TokenService $tokenService)
    {
        $this->tokenService = $tokenService;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $data = [];
        $status = 200;

        try {
            $data = $this->tokenService->createToken((array)$request->getParsedBody(), true);
        } catch (\Throwable $th) {
            $data = ['message' => $th->getMessage()];
            $status = $th->getCode();
        }

        $response->getBody()->write((string)json_encode($data));

        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }
}
