<?php

namespace App\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use App\Domain\Token\Service\TokenService;

final class TokenRefreshAction
{
    private $TokenService;

    public function __construct(TokenService $TokenService)
    {
        $this->TokenService = $TokenService;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {

        try {
            $data = $request->getParsedBody();
            $new_refresh_token = $this->TokenService->createNewTokenRefresh($data['refresh_token']);
        } catch (\Throwable $th) {
            $th = $th;
        }

        $response->getBody()->write((string)json_encode($new_refresh_token));

        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
}
