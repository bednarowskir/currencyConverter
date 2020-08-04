<?php

namespace Robert\CurrencyConverter\Controller\Index;

use Magento\Framework\App\Action\Action;
use Robert\CurrencyConverter\Api\ConverterManagementInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;

class Convert extends Action
{
    /**
     * @var ConverterManagementInterface
     */
    protected $currencyConverter;

    /**
     * @var ResultFactory
     */
    protected $resultRedirect;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    public function __construct(
        Context $context,
        ConverterManagementInterface $currencyConverter,
        ResultFactory $resultRedirect,
        JsonFactory $resultJsonFactory
    ) {
        $this->currencyConverter = $currencyConverter;
        $this->resultRedirect = $resultRedirect;
        $this->resultJsonFactory = $resultJsonFactory;

        return parent::__construct($context);
    }

    public function execute()
    {
        if (false === $this->getRequest()->isAjax()) {
            $resultRedirect = $this->resultRedirect->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());

            return $resultRedirect;
        }

        $currencyValueToConvert = $this->getRequest()->getPost('currency_from');

        try {
            $result = [
                'success' => true,
                'converted_value' => $this->currencyConverter->convert($currencyValueToConvert, 'USD', 'PLN'),
            ];
        } catch (\Exception $ex) {
            $result = [
                'success' => false,
                'error_message' => $ex->getMessage(),
            ];
        }

        $jsonResult = $this->resultJsonFactory->create();
        $jsonResult->setData($result);

        return $jsonResult;
    }
}
