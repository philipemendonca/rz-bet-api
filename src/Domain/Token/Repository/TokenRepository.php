<?php

namespace App\Domain\Token\Repository;

use Firebase\JWT\JWT;
use App\Domain\User\Repository\UserGetRepository;

class TokenRepository
{
    private $connection;
    private $userRepository;

    public function __construct(\PDO $connection, UserGetRepository $userRepository)
    {
        $this->connection = $connection;
        $this->userRepository =  $userRepository;
    }

    public function insertToken(array $data): void
    {
        $statement = $this->connection
            ->prepare('INSERT INTO sis_tokens_api
                (
                    token,
                    refresh_token,
                    expired_at,
                    usuario_id
                )
                VALUES
                (
                    :token,
                    :refresh_token,
                    :expired_at,
                    :usuario_id
                );
            ');
        $statement->execute([
            'token' => $data['token'],
            'refresh_token' =>  $data['refresh_token'],
            'expired_at' =>  $data['expired_at'],
            'usuario_id' =>  $data['usuario_id']
        ]);
    }

    public function getUsuarioByRefreshToken(string $RefreshToken): ?array
    {
        $statement = $this->connection->prepare('SELECT * FROM sis_tokens_api WHERE refresh_token = :refresh_token');
        $statement->bindParam('refresh_token', $RefreshToken);
        $statement->execute();
        $userId = $statement->fetch(\PDO::FETCH_ASSOC);

        return $this->userRepository->getUserById($userId['usuario_id']);
    }

    public function getTokensByRefreshToken(string $RefreshToken): ?array
    {
        $statement = $this->connection->prepare('SELECT * FROM sis_tokens_api WHERE refresh_token = :refresh_token');
        $statement->bindParam('refresh_token', $RefreshToken);
        $statement->execute();
        $Tokens = $statement->fetch(\PDO::FETCH_ASSOC);

        return $Tokens;
    }

    public function checkTokenInBd(string $token): ?bool
    {
        $statement = $this->connection->prepare('SELECT * FROM sis_tokens_api WHERE token = :token');
        $statement->bindParam('refresh_token', $token);
        $statement->execute();
        $Tokens = $statement->fetch(\PDO::FETCH_ASSOC);

        return $Tokens;
    }
}
