<?php

namespace App\Domain\Token\Service;

use Firebase\JWT\JWT;

final class TokenService
{
    public function createRefreshToken(string $data): string
    {

        return $data;
    }

    public function createToken(array $data): ?string
    {
        $expired_at = (new \DateTime())->modify('+1 hour')->format('Y-m-d H:i:s');
        $key = getenv('JWT_SECRET_KEY');

        $tokenPayLoad = [
            'sub' => $data['id'],
            'nome' => $data['nome'],
            'email' => $data['email'],
            'expired_at' => $expired_at
        ];

        $refreshTokenPayLoad = [
            'email' => $data['email']
        ];

        $token = JWT::encode($tokenPayLoad, $key);
        $refreshToken = JWT::encode($refreshTokenPayLoad, $key);

        /*if (!is_null($dados)) {
            if (password_verify($senha, $dados['senha'])) {
                $payLoad = [
                    'id' => $dados['id'],
                    'nome' => $dados['nome'],
                    'email' => $dados['email'],
                    'expired_at' => (new DateTime())->modify('+1 hour')->format('Y-m-d H:i:s')
                ];

                $token = JWT::encode($payLoad, getenv('JWT_SECRET_KEY'));
                $refreshToken = [
                    'email' => $email
                ];
                
                return $token;
            }
        }*/

        return '';
    }
}
