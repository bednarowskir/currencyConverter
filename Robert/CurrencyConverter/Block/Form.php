<?php
namespace Robert\CurrencyConverter\Block;

use Magento\Framework\View\Element\Template;

class Form extends Template
{
    public function getCurrencyConverterFormUrl(): string
    {
        return $this->getUrl('*/index/convert');
    }
}
