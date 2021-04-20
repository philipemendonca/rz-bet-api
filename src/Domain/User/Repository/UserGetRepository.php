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

    public function getUserByEmail(array $data): ?array
    {
        $email = $data['email'];
        $sql = $this->connection->prepare("SELECT * FROM sis_usuarios_api WHERE email = :email");
        $sql->bindParam('email', $email);
        $sql->execute();

        return $sql->fetch(\PDO::FETCH_ASSOC);
    }

    public function checkEmailSenha(string $senha, string $hash): bool
    {
        if (password_verify($senha, $hash)) {
            return true;
        }else{
            return false;
        }
    }

    public function getUserById(int $Id): ?array
    {
        $sql = $this->connection->prepare("SELECT * FROM sis_usuarios_api WHERE id = :id");
        $sql->bindParam('id', $Id);
        $sql->execute();

        return $sql->fetch(\PDO::FETCH_ASSOC);
    }
}
