<?php

namespace App;

class StandardMarketPair
{
    public $symbol;
    public $baseCurrency;
    public $quoteCurrency;
    public $volumeBase;
    public $volumeQuote;
    public $buyTokenA;

    public function __construct(string $symbol, string $baseCurrency, string $quoteCurrency, ?string $volumeBase,?string $volumeQuote, ?string $buyPrice)
    {
        $this->symbol = $symbol;
        $this->baseCurrency = $baseCurrency;
        $this->quoteCurrency = $quoteCurrency;
        $this->volumeBase = $this->fltoStr($volumeBase);
        $this->volumeQuote = $this->fltoStr($volumeQuote);
        $this->buyTokenA = $this->fltoStr($buyPrice);
    }

    private function fltoStr($exp)
    {
        if (is_null($exp) || empty($exp) || stripos($exp, 'e') === false) {
            return $exp;
        }
        list($mantissa, $exponent) = explode("e", strtolower($exp));
        list($int, $dec) = explode(".", $mantissa);
        bcscale(abs($exponent - strlen($dec)));
        return bcmul($mantissa, bcpow("10", $exponent));
    }
}