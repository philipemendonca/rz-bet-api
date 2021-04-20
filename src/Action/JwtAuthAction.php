<?php

namespace App\Action;

use Psr\Http\{
    Message\ServerRequestInterface as Request,
    Message\ResponseInterface as Response,
    Server\RequestHandlerInterface as RequestHandler
};

final class JwtAuthAction
{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $response = $handler->handle($request);
        $token = $request->getAttribute('jwt');

        if (!isset($token['expired_at'])) {
            $response->getBody()->write((string)json_encode(['message' => 'token inválido']));

            return $response->withStatus(401);
        }
        $expireDate = new \DateTime($token['expired_at']);
        $now = new \DateTime();

        if ($expireDate < $now) {
            $response->getBody()->write((string)json_encode(['message' => 'token expirado']));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

        return $response;
    }
}
