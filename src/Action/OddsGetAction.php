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

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface {
        // Recebe os dados
        $Odds = $this->OddsGet->getOdds();

        // Cria a resposta HTTP
        $response->getBody()->write((string) json_encode(
            count($Odds) == 0 ? [
                'message' => 'problema ao baixar dados do bet365'
            ] : $Odds
        ));

        // Envia a resposta
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }
}
