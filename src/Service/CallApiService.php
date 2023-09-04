<?php 

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class CallApiService
{
    private const API_URL = 'https://api.exchangerate-api.com/v4/latest/';

    private $client;
    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
        
    }
    public function getExchangeRate(string $baseCurrency, string $targetCurrency): float
    {
        $response = $this->client->request('GET', self::API_URL . $baseCurrency);
        $exchangeRates = json_decode($response->getContent(), true)['rates'];

        return $exchangeRates[$targetCurrency];
    }
public function getData(): array
{
    $response = $this->client->request(
        'GET',
    'https://api.apilayer.com/currency_data/live?base=USD&symbols=EUR,GBP',
    [
        'query' => ['apikey' => '7AbZcUYLk05e4MXGSjA8feJERSCGKDqs'],
    ]

    );

    return $response->toArray();
}

}