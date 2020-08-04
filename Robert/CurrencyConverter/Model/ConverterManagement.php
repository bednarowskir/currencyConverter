<?php

namespace Robert\CurrencyConverter\Model;

use Robert\CurrencyConverter\Api\ConverterManagementInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Serialize\Serializer\Json;
use Robert\CurrencyConverter\Exception\BadApiResponseException;
use Robert\CurrencyConverter\Exception\InvalidApiUrlException;
use Robert\CurrencyConverter\Exception\InvalidCurrencyValueException;

class ConverterManagement implements ConverterManagementInterface
{
    const CURRENCY_CONVERTER_API_URL = 'http://free.currencyconverterapi.com/api/v5/convert?compact=ultra&apiKey=52bcb22b6206823325e3';

    /**
     * @var Curl
     */
    protected $curlClient;

    /**
     * @var Json
     */
    protected $json;

    public function __construct(Curl $curl, Json $json)
    {
        $this->curlClient = $curl;
        $this->json = $json;
    }

    public function convert($currencyValue, string $currencyFrom, string $currencyTo): float
    {
        if (false === $this->isValid($currencyValue)) {
            throw new InvalidCurrencyValueException('Sorry, currency must be a positive number, try again');
        }

        $currenciesConverterAPIUrl = $this->getCurrenciesConverterAPIUrl($currencyFrom, $currencyTo);
        $this->curlClient->addHeader('Content-Type', 'application/json');

        try {
            $this->curlClient->get($currenciesConverterAPIUrl);
        } catch (\Exception $e) {
            throw new InvalidApiUrlException('Invalid Converter Api url');
        }

        try {
            $currenciesRates = $this->json->unserialize($this->curlClient->getBody());
        } catch (\Exception $e) {
            throw new BadApiResponseException('Converter Api response is not valid');
        }

        if (true === is_array($currenciesRates) && true === isset($currenciesRates[$currencyFrom . '_' . $currencyTo])) {
            return floatval($currenciesRates[$currencyFrom . '_' . $currencyTo]) * $currencyValue;
        }

        throw new BadApiResponseException('Converter Api response is not valid');
    }

    private function isValid($currencyValue): bool
    {
        if (false === is_numeric($currencyValue) || $currencyValue < 0) {
            return false;
        }

        return true;
    }

    private function getCurrenciesConverterAPIUrl(string $currencyFrom, string $currencyTo): string
    {
        return self::CURRENCY_CONVERTER_API_URL . '&q=' . $currencyFrom . '_' . $currencyTo;
    }
}
