<?php

namespace Bookstore\Shipping\Model\Carrier;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\ResultFactory;
use Psr\Log\LoggerInterface;

class Express extends AbstractCarrier implements CarrierInterface
{
    protected $_code = 'bookstore_express';

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        private readonly ResultFactory $rateResultFactory,
        private readonly MethodFactory $rateMethodFactory,
        LoggerInterface $logger,
        array $data = []
    ) {
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        $qty = max(1, (int)$request->getPackageQty());
        $price = 18000 + max(0, $qty - 1) * 3000;
        $result = $this->rateResultFactory->create();
        $method = $this->rateMethodFactory->create();
        $method->setCarrier($this->_code);
        $method->setCarrierTitle($this->getConfigData('title') ?: 'Van chuyen Viet Nam');
        $method->setMethod('express');
        $method->setMethodTitle(($this->getConfigData('name') ?: 'Giao hang nhanh') . ' - phi theo so luong sach');
        $method->setPrice($price);
        $method->setCost($price);
        $result->append($method);

        return $result;
    }

    public function getAllowedMethods()
    {
        return [$this->_code => $this->getConfigData('name') ?: 'Giao hang nhanh'];
    }
}
