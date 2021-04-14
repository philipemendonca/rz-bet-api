<?php

namespace App\Domain\Odds\Service;

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
     * os jogos e mercados da liga.
     *
     * @return array
     */
    public function getOdds(): array
    {
        $liga = 'G100387529';
        $odds = [];
        $odds_to_merge = [];

        //lista de mercados
        $mercados['resultado_final']                 = '#AC#B1#C1#D7#E40#F4#'    . $liga . '#H3#';
        //$mercados['para_ambos_os_times_marcarem']    = '#AC#B1#C1#D7#E10150#F4#' . $liga . '#H3#';
        //$mercados['marcadores_de_gol']               = '#AC#B1#C1#D7#E45#F4#'    . $liga . '#H3#';
        //$mercados['dupla_chance']                    = '#AC#B1#C1#D7#E50401#F4#' . $liga . '#H3#';
        // $mercados['empate_anula_aposta']             = '#AC#B1#C1#D7#E10544#F4#' . $liga . '#H3#';
        // $mercados['gols_mais_menos']                 = '#AC#B1#C1#D7#E981#F4#'   . $liga . '#H3#';
        // $mercados['total_de_gols_mais_alternativas'] = '#AC#B1#C1#D7#E10202#F4#' . $liga . '#H3#';
        // $mercados['gols_mais_ou_menos']              = '#AC#B1#C1#D7#E10143#F4#' . $liga . '#H3#';
        // $mercados['handcap']                         = '#AC#B1#C1#D7#E171#F4#'   . $liga . '#H3#';

        //pegando o fonte de todos os mercados
        foreach ($mercados as $mercado => $str) {
            $mercados[$mercado] = $this->getSrcBet365($str);
        }

        //parser nos resultados
        $odds_to_merge['resultado_final'] = $this->parse_resultado_final($mercados['resultado_final']);
        //$odds_to_merge['para_ambos_os_times_marcarem'] = $this->parse_para_ambos_os_times_marcarem($mercados['para_ambos_os_times_marcarem']);
        //$odds_to_merge['marcadores_de_gol'] = $this->parse_marcadores_de_gol($mercados['marcadores_de_gol']);

        foreach ($odds_to_merge['resultado_final'] as $mercado => $e) {
            array_push($odds, array(
                'event'     => $e['home_team'] . ' x ' . $e['away_team'],
                'home_team' => $e['home_team'],
                'away_team' => $e['away_team'],
                'datetime' => $e['datetime'],
            ));
        }

        foreach ($odds_to_merge as $category => $events) {
            foreach ($events as $i => $event) {
                if ($event['home_team'] == $odds[$i]['home_team'] && $event['away_team'] == $odds[$i]['away_team']) {
                    $odds[$i][$category] = $event['odds'];
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

        if ($session == '') {
            throw new \Exception("Erro ao pegar header");
        }

        $session = json_decode($session, true);
        $headers = $this->GetHeadersFromSession($session['headers']);
        $cookies = $this->GetCookiesFromSession($session['cookies']);
        $useragent = $headers[0];
        $fields = ['lid' => '33', 'zid' => '0', 'pd' => $data, 'cid' => '28', 'cgid' => '1', 'ctid' => '28'];

        $data = $this->cURL(self::base_url . http_build_query($fields), $headers, $cookies, $useragent);
        
        return $data;
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


    function parseGames($data)
    {
        $games = [];
        $values = $this->getValues($data, 'FD');

        foreach ($values as $game) {
            if (strpos($game, ' v ')) {
                array_push($games, $game);
            }
        }

        return $games;
    }

    function getValues($data, $value)
    {
        $values = [];
        $rows = explode('|', $data);

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
    }

    function  parseDateTimes($data)
    {
        $datetimes = [];
        $values = $this->getValues($data, 'BC');

        foreach ($values as $dt) {
            $dt = date("Y/m/d h:i:s", strtotime(substr($dt, 0, 4) . '/' . substr($dt, 4, 2) . '/' . substr($dt, 6, 2) . ' ' . substr($dt, 8, 2) . ':' . substr($dt, 10, 2) . ':' . substr($dt, 12, 2)));
            array_push($datetimes, $dt);
        }

        return $datetimes;
    }

    function parseEvents($data)
    {
        $games = $this->parseGames($data);
        $datetimes = $this->parseDateTimes($data);

        $events = array_map(function ($game, $datetime) {
            $team = explode(' v ', $game);
            $home_team = $team[0];
            $away_team = $team[1];

            return array('home_team' => $home_team, 'away_team' => $away_team, 'datetime' => $datetime);
        }, $games, $datetimes);

        return $events;
    }

    function _xor($msg, $key)
    {
        $msg = str_split($msg);
        $value = '';

        foreach ($msg as $char) {
            $value .= chr(ord($char) ^ $key);
        }

        return $value;
    }

    function parseOdds($data)
    {
        $odds = [];
        $values = $this->getValues($data, 'OD');

        if (count($values) == 0) return $odds;

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
    }

    function parse_marcadores_de_gol($data)
    {
        $events = explode('MG;', $data);
        $events = array_slice($events, 2, count($events) - 1);
        $para_ambos_os_times_marcarem = $this->parseOdds($data);

        $jogadores = [];
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
    }

    function parse_para_ambos_os_times_marcarem($data)
    {
        $events = $this->parseEvents($data);
        $para_ambos_os_times_marcarem = $this->parseOdds($data);
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
    }

    function parse_resultado_final($data)
    {
        $events = $this->parseEvents($data);
        $resultado_final = $this->parseOdds($data);
        $len = count($events);

        $_1s = array_slice($resultado_final, 0, $len);
        $_Xs = array_slice($resultado_final, $len, $len);
        $_2s = array_slice($resultado_final, 2 * $len, $len);

        $resultado_final = array_map(function ($event, $_1, $_X, $_2) {
            return array(
                'home_team' => $event['home_team'],
                'away_team' => $event['away_team'],
                'datetime' => $event['datetime'],
                'odds' => array(
                    '1' => $_1,
                    'X' => $_X,
                    '2' => $_2
                )
            );
        }, $events, $_1s, $_Xs, $_2s);

        return $resultado_final;
    }

    function getMercadosByLiga($liga)
    {
    }
}
