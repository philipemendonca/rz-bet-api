<?php

namespace App\Action;

use App\Domain\Odds\Service\OddsGet;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class OddsGetAction
{
    private $OddsGet;

    public function __construct(OddsGet $OddsGet)
    {
        $this->OddsGet = $OddsGet;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $status = 200;
        try {
            $Odds = $this->OddsGet->getOdds();
            $result = [
                'result' => 1,
                'jogos' => $Odds['jogos'],
                'campeonatos' => $Odds['campeonatos']
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
