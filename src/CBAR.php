<?php

namespace Orkhanahmadov\CBARCurrency;

use GuzzleHttp\Client;
use Orkhanahmadov\CBARCurrency\Exceptions\CurrencyException;

class CBAR
{
    /**
     * @var Client
     */
    private $client;
    /**
     * @var array
     */
    private $currencies = [];

    /**
     * Parser constructor.
     */
    public function __construct()
    {
        $this->client = new Client();
    }

    public function rateFor(string $date)
    {
        $response = $this->client->get('https://www.cbar.az/currencies/'.$date.'.xml');

        $xml = simplexml_load_string($response->getBody()->getContents());

        foreach ($xml->ValType[1]->Valute as $currency) {
            $this->currencies[(string) $currency->attributes()['Code']] = [
                'rate' => (float) $currency->Value,
                'nominal' => (int) $currency->Nominal
            ];
        }

        return $this;
    }

    /**
     * @param string $currency
     * @return mixed
     * @throws CurrencyException
     */
    public function __get(string $currency)
    {
        if (isset($this->currencies[$currency])) {
            return $this->currencies[$currency]['rate'] / $this->currencies[$currency]['nominal'];
        }

//        if (isset($this->currencies[$currency])) {
//            return new CBARCurrency($this->currencies[$currency]['rate'], $this->currencies[$currency]['nominal']);
//        }

        throw new CurrencyException('Currency with '.$currency.' code is not available');
    }

    /**
     * @param Client $client
     */
    public function setClient(Client $client): void
    {
        $this->client = $client;
    }

    /**
     * @return array
     */
    public function getCurrencies(): array
    {
        return $this->currencies;
    }

    /**
     * @param array $currencies
     */
    public function setCurrencies(array $currencies): void
    {
        $this->currencies = $currencies;
    }
}
