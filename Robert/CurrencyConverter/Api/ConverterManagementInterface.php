<?php

namespace Robert\CurrencyConverter\Api;

use Robert\CurrencyConverter\Exception\BadApiResponseException;
use Robert\CurrencyConverter\Exception\InvalidApiUrlException;
use Robert\CurrencyConverter\Exception\InvalidCurrencyValueException;

interface ConverterManagementInterface
{
    /**
     * Convert currencies. Connect to api, get exchange rate and calculate second currency value
     *
     * @param mixed $value
     * @param string $currencyFrom
     * @param string $currencyTo
     * @throws BadApiResponseException
     * @throws InvalidApiUrlException
     * @throws InvalidCurrencyValueException
     * @return float
     */
    public function convert($value, string $currencyFrom, string $currencyTo) : float;
}
