<?php

namespace App\Domain\Odds\Service;

use App\Exception\GetOddsException;

/**
 * Service OddsGet
 * Pega todos os mercados de uma liga no site https://www.bet365.com/.
 */
final class OddsGet
{
    /**
     * Um handle cURL retornado por curl_init().
     *
     * @var [resource]
     */
    private $ch;

    const base_url = 'https://www.bet365.com/SportsBook.API/web?';

    /**
     * O construtor
     */
    public function __construct()
    {
        $this->ch = curl_init();
    }

    /**
     * O destrutor
     */
    public function __destruct()
    {
        curl_close($this->ch);
    }

    /**
     * Método que retorna um objeto JSON contendo informações sobre os
     * os jogos e mercados.
     *
     * @return array
     */
    public function getOdds(): array
    {
        $ligas = array('G101284889', 'G101334640', 'G101751187');
        $mercados = [];
        $odds['jogos'] = [];
        $odds['campeonatos'] = [];
        $odds_to_merge = [];

        foreach ($ligas as $i => $liga) {
            //lista de mercados
            //$mercados['resultado_final']                 = '#AC#B1#C1#D7#E40#F4#'    . $liga . '#H3#';
            //$mercados['para_ambos_os_times_marcarem']    = '#AC#B1#C1#D7#E10150#F4#' . $liga . '#H3#';
            //$mercados['marcadores_de_gol']               = '#AC#B1#C1#D7#E45#F4#'    . $liga . '#H3#';
            //$mercados['dupla_chance']                    = '#AC#B1#C1#D7#E50401#F4#' . $liga . '#H3#';
            //$mercados['empate_anula_aposta']             = '#AC#B1#C1#D7#E10544#F4#' . $liga . '#H3#';
            //$mercados['gols_mais_menos']                 = '#AC#B1#C1#D7#E981#F4#'   . $liga . '#H3#';
            //$mercados['total_de_gols_mais_alternativas'] = '#AC#B1#C1#D7#E10202#F4#' . $liga . '#H3#';
            //$mercados['gols_mais_ou_menos']              = '#AC#B1#C1#D7#E10143#F4#' . $liga . '#H3#';
            //$mercados['handcap']                         = '#AC#B1#C1#D7#E171#F4#'   . $liga . '#H3#';
            array_push($mercados, ['resultado_final' => '#AC#B1#C1#D7#E40#F4#'    . $liga . '#H3#']);
        }

        //pegando o fonte de todos os mercados e de todas as ligas
        foreach ($mercados as $index => $mercado) {
            foreach ($mercado as $nomeMercado => $strLink) {
                $src = $this->getSrcBet365($strLink);
                $mercados[$index][$nomeMercado] = $src;
            }
        }

        //parser nos resultados
        foreach ($mercados as $i => $mercado) {
            foreach ($mercado as $index => $data) {
                array_push($odds_to_merge, [
                    'resultado_final' => $this->parse_resultado_final($data)
                ]);
                //$odds_to_merge['para_ambos_os_times_marcarem'] = $this->parse_para_ambos_os_times_marcarem($mercados['para_ambos_os_times_marcarem']);
                //$odds_to_merge['marcadores_de_gol'] = $this->parse_marcadores_de_gol($mercados['marcadores_de_gol']);
            }
        }

        //definindo o array de odds
        foreach ($odds_to_merge as $index => $jogos) {
            foreach ($jogos as $mercado => $jogosMercado) {
                foreach ($jogosMercado as $jogo) {
                    $odds['jogos'][$jogo['jogo']] = [
                        'campeonato' => $jogo['campeonato'],
                        'timecasa' => $jogo['timecasa'],
                        'timefora' => $jogo['timefora'],
                        'timecasaimg' => $jogo['timecasaimg'],
                        'timeforaimg' => $jogo['timeforaimg'],
                        'refid' => $jogo['refid'],
                        'data' => $jogo['data'],
                        'hora' => $jogo['hora']
                    ];
                }
            }
        }

        //criando o objeto a ser retornado com os mercados encontrados
        foreach ($odds_to_merge as $index => $mercados) {
            foreach ($mercados as $mercado => $jogos) {
                foreach ($jogos as $jogo) {
                    if ($jogo['timecasa'] == $odds['jogos'][$jogo['jogo']]['timecasa'] && $jogo['timefora'] == $odds['jogos'][$jogo['jogo']]['timefora']) {
                        $odds['jogos'][$jogo['jogo']]['cotacoes'][$mercado] = $jogo['odds'];
                        $odds['campeonatos'][$jogo['campeonato']] = $jogo['campeonato'];
                    }
                }
            }
        }

        return $odds;
    }

    /**
     * Método que retorna cookies em formato compatível com cURL
     *
     * @param array $session: objeto json capturado de uma pagina que 
     * gera sessões válidas.
     * 
     * @return string
     */
    function getCookiesFromSession(array $session): string
    {
        $cookies = '';
        foreach ($session as $key => $value) {
            $cookies .= $value['name'] . '=' . $value['value'] . '; ';
        }

        return $cookies;
    }

    /**
     * Método que retorna um array contendo os headers compatíveis com
     * cURL capturado de uma pagina que gera sessões válidas.
     *
     * @param array $session
     * 
     * @return array
     */
    function getHeadersFromSession(array $session): array
    {
        $headers = array(
            'User-Agent: ' . $session['User-Agent'],
            'Referer: ' . $session['Referer'],
            'X-Net-Sync-Term: ' . $session['X-Net-Sync-Term'],
        );

        return $headers;
    }

    /**
     * Pega os dados retornados da API do https://www.bet365.com/
     *
     * @param string $data: é uma string contendo dados referentes a 
     * liga e o tipo de mercado.
     * 
     * @return string
     */
    function getSrcBet365(string $data): string
    {
        $session = $this->cURL('http://localhost:5000/bet365', null, null, null, 1000);

        if (!empty($session)) {
            $session = json_decode($session, true);
            $headers = $this->GetHeadersFromSession($session['headers']);
            $cookies = $this->GetCookiesFromSession($session['cookies']);
            $useragent = $headers[0];
            $fields = ['lid' => '33', 'zid' => '0', 'pd' => $data, 'cid' => '28', 'cgid' => '1', 'ctid' => '28'];
            $data = $this->cURL(self::base_url . http_build_query($fields), $headers, $cookies, $useragent);

            if (!empty($data)) {
                return $data;
            } else {
                throw new GetOddsException('Erro ao pegar dados do bet365.');
            }
        } else {
            throw new GetOddsException('Erro ao recuperar session.');
        }
    }

    /**
     * Executa um cURL
     *
     * @param string $url
     * @param array $headers
     * @param string $cookies
     * @param string $useragent
     * 
     * @return string
     */
    function cURL(string $url, array $headers = null, string $cookies = null, string $useragent = null, int $timeout = 0): string
    {
        curl_setopt($this->ch, CURLOPT_URL, $url);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($this->ch, CURLOPT_TIMEOUT_MS, $timeout);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, is_null($headers) ? [] : $headers);
        curl_setopt($this->ch, CURLOPT_USERAGENT, is_null($useragent) ? '' : $useragent);
        curl_setopt($this->ch, CURLOPT_COOKIE, is_null($cookies) ? '' : $cookies);

        return curl_exec($this->ch);
    }

    /**
     * Método que retorna um array contendo todos os jogos da liga
     *
     * @param string $data é o fonte retornado pelo bet365
     * @return array
     */
    function parseGames(string $data): array
    {
        if (!empty($data)) {
            $games = [];
            $values = $this->getValues($data, 'FD');

            try {
                $campeonato = explode('NA=', $data);
                $campeonato = explode('CD=', $campeonato[2]);
                $campeonato = explode(',', $campeonato[1]);

                foreach ($values as $game) {
                    if (strpos($game, ' v ')) {
                        array_push($games, ['jogo' => $game, 'campeonato' => $campeonato[0]]);
                    }
                }

                return $games;
            } catch (\Throwable $th) {
                throw new GetOddsException('Erro parseGames: ' . $th->getMessage());
            }
        } else {
            throw new GetOddsException('Erro parseGames, $data veio vazio.');
        }
    }

    /**
     * Método que retorna um array com valores de um campo específico do fonte
     *
     * @param string $data é o fonte retornado pelo bet365
     * @param string $value é o alvo a pesquisar
     * @return array
     */
    function getValues(string $data, string $value): array
    {
        if (!empty($data)) {
            $values = [];
            $rows = explode('|', $data);

            try {
                foreach ($rows as $row) {
                    if (substr($row, 0, 2) == 'PA') {
                        $tmp = explode(';', $row);
                        foreach ($tmp as $key) {
                            if (substr($key, 0, 2) == $value) {
                                array_push($values, substr($key, 3));
                            }
                        }
                    }
                }

                return $values;
            } catch (\Throwable $th) {
                throw new GetOddsException('Erro getValues: ' . $th->getMessage());
            }
        } else {
            throw new GetOddsException('Erro getValues, $data veio vazio.');
        }
    }

    /**
     * Método que retorna um array com as datas e horas das partidas
     *
     * @param string $data é o fonte retornado pelo bet365
     * @return array
     */
    function  parseDateTimes(string $data): array
    {
        if (!empty($data)) {
            try {
                $datetimes = [];
                $values = $this->getValues($data, 'BC');

                foreach ($values as $dt) {
                    $datahora = [];
                    $datacompleta = substr($dt, 0, 4) . '/' . substr($dt, 4, 2) . '/' . substr($dt, 6, 2) . ' ' . substr($dt, 8, 2) . ':' . substr($dt, 10, 2) . ':' . substr($dt, 12, 2);
                    $datacompleta = new \DateTime($datacompleta);
                    $datacompleta->modify('-4 hour');
                    $datahora['data'] = $datacompleta->format('Y/m/d');
                    $datahora['hora'] = $datacompleta->format('H:i:s');
                    array_push($datetimes, $datahora);
                }

                return $datetimes;
            } catch (\Throwable $th) {
                throw new GetOddsException('Erro parseDateTimes: ' . $th->getMessage());
            }
        } else {
            throw new GetOddsException('Erro parseDateTimes, $data veio vazio.');
        }
    }

    /**
     * Método que retorna um array contendo todas as partidas
     *
     * @param string $data é o fonte retornado pelo bet365
     * @return array
     */
    function parseEvents(string $data): array
    {
        if (!empty($data)) {
            try {
                $games = $this->parseGames($data);
                $datetimes = $this->parseDateTimes($data);

                $events = array_map(function ($game, $datetime) {
                    $team = explode(' v ', $game['jogo']);
                    $home_team = $team[0];
                    $away_team = $team[1];

                    return array(
                        'jogo' => $game['jogo'],
                        'campeonato' => $game['campeonato'],
                        'timecasa' => $home_team,
                        'timefora' => $away_team,
                        'timecasaimg' => 'http://localhost/BetPlataform/public_html/cdn/img/25/25/crop/100/default.jpg',
                        'timeforaimg' => 'http://localhost/BetPlataform/public_html/cdn/img/25/25/crop/100/default.jpg',
                        'refid' => md5($game['campeonato'] . $home_team . $away_team . $datetime['data'] . $datetime['hora'], false),
                        'data' => $datetime['data'],
                        'hora' => $datetime['hora']
                    );
                }, $games, $datetimes);

                return $events;
            } catch (\Throwable $th) {
                throw new GetOddsException('Erro parseEvents: ' . $th->getMessage());
            }
        } else {
            throw new GetOddsException('Erro parseEvents, $data veio vazio.');
        }
    }

    /**
     * Método que desofusca dados ofuscados pelo bet365
     *
     * @param string $msg
     * @param string $key
     * @return string
     */
    function _xor(string $msg, string $key): string
    {
        try {
            $value = '';
            $msg = str_split($msg);

            foreach ($msg as $char) {
                $value .= chr(ord($char) ^ $key);
            }

            return $value;
        } catch (\Throwable $th) {
            throw new GetOddsException('Erro parseEvents: ' . $th->getMessage());
        }
    }

    /**
     * Método para retornar partidas
     *
     * @param string $data é o fonte retornado pelo bet365
     * @return array
     */
    function parseOdds(string $data): array
    {
        if (!empty($data)) {
            try {
                $odds = [];
                $values = $this->getValues($data, 'OD');
                $TK = explode(';', $data);
                $TK = substr($TK[1], 3);
                $key = ord(substr($TK, 0, 1)) ^ ord(substr($TK, 1, 1));

                foreach ($values as $obfuscated_odd) {
                    if ($obfuscated_odd != '') {
                        $xor = explode('/', $this->_xor($obfuscated_odd, $key));
                        $n = intval($xor[0]);
                        $d = intval($xor[1]);
                        $odd = round(($n / $d + 1) * 10 * 100);
                        array_push($odds, $odd);
                    }
                }

                return $odds;
            } catch (\Throwable $th) {
                throw new GetOddsException('Erro parseOdds: ' . $th->getMessage());
            }
        } else {
            throw new GetOddsException('Erro parseOdds, $data veio vazio.');
        }
    }

    /**
     * Método que retorna dados do mercado: Marcadores de gol
     *
     * @param string $data é o fonte retornado pelo bet365
     * @return array
     */
    function parse_marcadores_de_gol(string $data): array
    {
        if (!empty($data)) {
            $events = explode('MG;', $data);
            $events = array_slice($events, 2, count($events) - 1);
            $para_ambos_os_times_marcarem = $this->parseOdds($data);

            $jogadores = [];

            try {
                foreach ($events as $event) {
                    $gamers = explode('PA;', $event);
                }

                //$primeiro
                //$ultimo
                //$qualquer


                $len = count($events);

                $yess = array_slice($para_ambos_os_times_marcarem, 0, $len);
                $nos = array_slice($para_ambos_os_times_marcarem, $len);

                $para_ambos_os_times_marcarem = array_map(function ($event, $yes, $no) {
                    return array(
                        'home_team' => $event['home_team'],
                        'away_team' => $event['away_team'],
                        'datetime' => $event['datetime'],
                        'odds' => array('yes' => $yes, 'no' => $no)
                    );
                }, $events, $yess, $nos);

                return $para_ambos_os_times_marcarem;
            } catch (\Throwable $th) {
                throw new GetOddsException('Erro parse_marcadores_de_gol: ' . $th->getMessage());
            }
        } else {
            throw new GetOddsException('Erro parse_marcadores_de_gol, $data veio vazio.');
        }
    }

    /**
     * Método que retorna dados do mercado: Para ambos os times marcarem
     *
     * @param string $data é o fonte retornado pelo bet365
     * @return array
     */
    function parse_para_ambos_os_times_marcarem(string $data): array
    {
        if (!empty($data)) {
            $events = $this->parseEvents($data);
            $para_ambos_os_times_marcarem = $this->parseOdds($data);
            $len = count($events);

            try {
                $yess = array_slice($para_ambos_os_times_marcarem, 0, $len);
                $nos = array_slice($para_ambos_os_times_marcarem, $len);

                $para_ambos_os_times_marcarem = array_map(function ($event, $yes, $no) {
                    return array(
                        'home_team' => $event['home_team'],
                        'away_team' => $event['away_team'],
                        'datetime' => $event['datetime'],
                        'odds' => array('yes' => $yes, 'no' => $no)
                    );
                }, $events, $yess, $nos);

                return $para_ambos_os_times_marcarem;
            } catch (\Throwable $th) {
                throw new GetOddsException('Erro parse_para_ambos_os_times_marcarem: ' . $th->getMessage());
            }
        } else {
            throw new GetOddsException('Erro parse_para_ambos_os_times_marcarem, $data veio vazio.');
        }
    }

    /**
     * Método que retorna dados do mercado: Resultado final
     *
     * @param string $data é o fonte retornado pelo bet365
     * @return array
     */
    function parse_resultado_final(string $data): array
    {
        if (!empty($data)) {
            $events = $this->parseEvents($data);
            $resultado_final = $this->parseOdds($data);
            $len = count($events);

            try {
                $_1s = array_slice($resultado_final, 0, $len);
                $_Xs = array_slice($resultado_final, $len, $len);
                $_2s = array_slice($resultado_final, 2 * $len, $len);

                $resultado_final = array_map(function ($event, $_1, $_X, $_2) {
                    return array(
                        'jogo' => $event['jogo'],
                        'campeonato' => $event['campeonato'],
                        'timecasa' => $event['timecasa'],
                        'timefora' => $event['timefora'],
                        'timecasaimg' => $event['timecasaimg'],
                        'timeforaimg' => $event['timeforaimg'],
                        'refid' => $event['refid'],
                        'data' => $event['data'],
                        'hora' => $event['hora'],
                        'odds' => array(
                            '1' => $_1,
                            'X' => $_X,
                            '2' => $_2
                        )
                    );
                }, $events, $_1s, $_Xs, $_2s);

                return $resultado_final;
            } catch (\Throwable $th) {
                throw new GetOddsException('Erro parse_resultado_final: ' . $th->getMessage());
            }
        } else {
            throw new GetOddsException('Erro parse_resultado_final, $data veio vazio.');
        }
    }
}
