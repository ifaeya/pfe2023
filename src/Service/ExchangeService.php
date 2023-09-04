<?php 
// src/Service/ExchangeService.php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class ExchangeService
{
    private $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function buyCurrency(string $fromCurrency, string $toCurrency, float $amount): array
    {
        // Récupérer le taux de change actuel entre les deux devises
        $response = $this->httpClient->request('GET', 'https://api.apilayer.com/currency_data/live', [
            'query' => [
                'apikey' => '7AbZcUYLk05e4MXGSjA8feJERSCGKDqs',
                'source' => $fromCurrency,
                'currencies' => $toCurrency,
            ]
        ]);

        $responseData = $response->toArray();

        if (!isset($responseData['quotes'])) {
            return ['error' => 'Invalid API response: "quotes" key is missing'];
        }

        $exchangeRate = $responseData['quotes'][$fromCurrency.$toCurrency];

        // Calculer le montant de la devise d'arrivée en fonction du taux de change et du montant de la devise de départ
        $result = $amount * $exchangeRate;

        // Effectuer l'achat de devises enregistrer la transaction dans une base de données
       

        // Retourner le montant de la devise d'arrivée
        return ['result' => $result];
    }
}
