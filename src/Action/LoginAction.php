<?php

namespace App\Action;

use App\Domain\{
    User\Repository\UserGetRepository,
    Token\Service\TokenService
};

use Psr\Http\Message\{
    ResponseInterface,
    ServerRequestInterface
};

final class LoginAction
{
    private $userRepository;
    private $tokenService;

    public function __construct(UserGetRepository $userRepository, TokenService $tokenService)
    {
        $this->userRepository = $userRepository;
        $this->tokenService = $tokenService;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $data = [];
        $status = 0;

        try {
            $user = $this->userRepository->getUserByEmailPassword((array)$request->getParsedBody());
            $token = $this->tokenService->createToken($user);
            //$refreshToken = $this->tokenService->createRefreshToken('email');


            $status = 200;
        } catch (\Throwable $th) {
            $data = ['message' => $th->getMessage()];
            $status = $th->getCode();
        }

        $response->getBody()->write((string)json_encode($data));

        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }
}
