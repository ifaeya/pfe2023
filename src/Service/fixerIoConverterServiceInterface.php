<?php


namespace App\Service;


use Symfony\Contracts\HttpClient\HttpClientInterface;

interface fixerIoConverterServiceInterface
{

    public const BASE_URL = 'http://data.fixer.io/api/';
    public const API_KEY = '412e09694588a610eee73b014122cb69';

    public const   SYMBOLS_ENDPOINT = 'symbols';
    public const   LATEST_ENDPOINT = 'latest';

    public function getSymbols(): string;

    public function calcConvert(string $currency, float $amount): float;

}