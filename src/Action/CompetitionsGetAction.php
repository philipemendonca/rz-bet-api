<?php

namespace App\Action;

use App\Domain\Competitions\Service\CompetitionsGet;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class CompetitionsGetAction
{
    private $CompetitionsGet;

    public function __construct(CompetitionsGet $CompetitionsGet)
    {
        $this->CompetitionsGet = $CompetitionsGet;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $status = 200;
        try {
            $Competitions = $this->CompetitionsGet->getCompetitions();
            $result = [
                'result' => 1,
                'campeonatos' => $Competitions
            ];
        } catch (\Throwable $th) {
            $result = [
                'result' => 0,
                'message' => $th->getMessage()
            ];
            $status = $th->getCode();
        }

        $response->getBody()->write((string)json_encode($result));

        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }
}
