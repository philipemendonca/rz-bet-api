<?php

namespace App\Domain\User\Repository;

use PDO;

class UserGetRepository
{
    private $connection;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    public function getUserByEmailPassword(array $data): ?array
    {
        $sql = $this->connection->prepare("SELECT * FROM sis_usuarios_api WHERE email = :email");
        $sql->bindParam('email', $data['email']);
        $sql->execute();
        $user = $sql->fetch(\PDO::FETCH_ASSOC);

        if ($user && password_verify($data['senha'], $user['senha'])) {
            return $user;
        }
        
        throw new \Exception('dados inválidos', 401);
 
        return null;
    }
}
