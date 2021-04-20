<?php

namespace App\Domain\Auth\Repository;

final class JwtRefreshTokenRepository
{
    private $connection;

    public function __construct(\PDO $connection)
    {
        $this->connection = $connection;
    }

    
}
