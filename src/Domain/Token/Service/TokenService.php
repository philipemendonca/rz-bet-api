<?php

namespace App\Domain\Token\Service;

use Firebase\JWT\JWT;
use App\Domain\Token\Repository\TokenRepository;
use App\Domain\USer\Repository\UserGetRepository;

final class TokenService
{
    private $tokenRepository;
    private $userRepository;
    private $key;

    public function __construct(TokenRepository $tokenRepository, UserGetRepository $userRepository)
    {
        $this->tokenRepository = $tokenRepository;
        $this->userRepository = $userRepository;
        $this->key = getenv('JWT_SECRET_KEY');
    }

    public function checkTokenExist(string $Token):void
    {
        if (!$this->tokenRepository->checkTokenInBd($Token)) {
            throw new \Exception('dados inválidos', 401);
        }
    }

    private function getDataByToken(string $Token): ?object
    {
        return JWT::decode($Token, $this->key, ['HS256']);
    }

    private function getDataByRefreshToken(string $RefreshToken): ?object
    {
        return JWT::decode($RefreshToken, $this->key, ['HS256']);
    }

    public function createNewTokenRefresh(string $RefreshToken): ?array
    {
        $RefreshTokenReceivedDecoded = $this->getDataByRefreshToken($RefreshToken);
        $TokensBd = $this->tokenRepository->getTokensByRefreshToken($RefreshToken);
        $usuarioBd = $this->userRepository->getUserById($TokensBd['usuario_id']);

        if ($RefreshTokenReceivedDecoded->email != $usuarioBd['email']) {
            throw new \Exception('dados inválidos', 401);
        }

        return $this->createToken($usuarioBd, false);
    }

    private function createTokenRefresh(array $data): string
    {
        $refreshTokenPayLoad = [
            'email' => $data['email'],
            'random' => uniqid()
        ];

        return JWT::encode($refreshTokenPayLoad, $this->key);
    }

    public function createToken(array $data, bool $checasenha = true): ?array
    {
        $user = $this->userRepository->getUserByEmail($data);

        if ($checasenha) {
            if (!$this->userRepository->checkEmailSenha($data['senha'], $user['senha'])) {
                throw new \Exception('dados inválidos', 401);
            }
        }

        $refreshToken = $this->createTokenRefresh($data);
        $expired_at = (new \DateTime())->modify('+2 days')->format('Y-m-d H:i:s');

        $tokenPayLoad = [
            'sub' => $user['id'],
            'nome' => $user['nome'],
            'email' => $user['email'],
            'expired_at' => $expired_at
        ];

        $token = JWT::encode($tokenPayLoad, $this->key);

        $tokenToSave['usuario_id'] = $user['id'];
        $tokenToSave['token'] = $token;
        $tokenToSave['refresh_token'] = $refreshToken;
        $tokenToSave['expired_at'] = $expired_at;

        $this->tokenRepository->insertToken($tokenToSave);

        return ['token' => $token, 'refresh_token' => $refreshToken];
    }
}
