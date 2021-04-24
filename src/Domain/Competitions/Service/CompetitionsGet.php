<?php

namespace App\Domain\Competitions\Service;

use App\Exception\GetCompetitionsException;

/**
 * Service OddsGet
 * Pega todos os mercados de uma liga no site https://www.bet365.com/.
 */
final class CompetitionsGet
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

    private function getValueByDelimit(string $delimit, string $data): string
    {
        try {
            $value = explode($delimit, $data);
            $value = $value[1];
            $value = explode(';', $value);
            $value = $value[0];
        } catch (\Throwable $th) {
            throw new GetCompetitionsException('Erro getValues: ' . $th->getMessage());
        }


        return $value;
    }

    private function sliceStringToArray(string $data, string $delimit, int $start, int $stop = 0): array
    {
        try {
            $data = explode($delimit, $data);
            $stop = count($data) - $stop;
            $data = array_slice($data, $start, $stop);
        } catch (\Throwable $th) {
            throw new GetCompetitionsException('Erro sliceStringToArray: ' . $th->getMessage());
        }

        return $data;
    }

    private function getValueByMid(string $data, string $start_del, string $stop_del): string
    {
        $in = strpos($data, $start_del) + strlen($start_del);
        $data = substr($data, $in);
        $out = strpos($data, $stop_del);
        $data = substr($data, 0, $out);

        return $data;
    }

    /**
     * Método que retorna um objeto JSON contendo informações sobre os
     * os jogos e mercados.
     *
     * @return array
     */
    public function getCompetitions(): array
    {
        $table = [];
        $table_country = $this->sliceStringToArray($this->getSrcBet365('#AM#B1#C1#D7#E40#F4#G96703929#H3#Z^1#Y^1_7_40_4_96703929#S1#'), '|', 2);

        foreach ($table_country as $row) {
            if (!empty($row)) {
                $country_name = $this->getValueByDelimit('NA=', $row);
                $country_link = $this->getValueByDelimit('PD=', $row);

                $table[$country_name] = [];

                $table_league = $this->sliceStringToArray($this->getSrcBet365($country_link), '|', 3);

                foreach ($table_league as $row2) {
                    if (!empty($row2)) {
                        $league_name = $this->getValueByDelimit('NA=', $row2);
                        $league_link = $this->getValueByDelimit('PD=', $row2);
                        $table[$country_name][$league_name] = [
                            'link' => $league_link,
                            'code' => $this->getValueByMid($league_link, 'F4#', '#H3')
                        ];
                    }
                }
            }
        }

        return $table;
    }

    /**
     * Método que retorna cookies em formato compatível com cURL
     *
     * @param array $session: objeto json capturado de uma pagina que 
     * gera sessões válidas.
     * 
     * @return string
     */
    private function getCookiesFromSession(array $session): string
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
    private function getHeadersFromSession(array $session): array
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
    private function getSrcBet365(string $data): string
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
                throw new GetCompetitionsException('Erro ao pegar dados do bet365.');
            }
        } else {
            throw new GetCompetitionsException('Erro ao recuperar session.');
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
    private function cURL(string $url, array $headers = null, string $cookies = null, string $useragent = null, int $timeout = 0): string
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
}
