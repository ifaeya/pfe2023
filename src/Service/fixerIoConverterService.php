<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class fixerIoConverterService implements fixerIoConverterServiceInterface
{

    private $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function getSymbols(): string
    {

        $serviceLocation = self::BASE_URL . self::SYMBOLS_ENDPOINT . '?access_key=' . self::API_KEY;
        return $this->getResponse($serviceLocation)->getContent();
    }

    public function calcConvert(string $currency, float $amount): float
    {

        $serviceLocation = self::BASE_URL . self::LATEST_ENDPOINT . '?access_key=' . self::API_KEY;
        $res = $this->getResponse($serviceLocation)->toArray();
        $rates = $res['rates'];
        $convert = floatval(0);

        if (true === $res['success'] && array_key_exists($currency, $rates)) {
            $rate = $rates[$currency];
            $convert = $amount * $rate;
            return $convert;
        } else {
            return $convert; }

    }

    private function getResponse($url)
    {
        $res = null;
        $res = $this->client->request('GET', $url);
        return $res;
    }

}
