<?php

namespace Bookstore\Payment\Model;

use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Payment\Model\InfoInterface;
use Magento\Framework\DataObject;

class VietQr extends AbstractMethod
{
    protected $_code = 'bookstore_vietqr';
    protected $_isOffline = true;
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canCapturePartial = true;
    protected $_canRefund = true;
    protected $_canVoid = true;
    protected $_canUseCheckout = true;
    protected $_canUseForMultishipping = false;

    /**
     * Validate payment method
     */
    public function validate()
    {
        parent::validate();

        // Always return true for offline simulated payment
        return $this;
    }

    /**
     * Authorize payment
     */
    public function authorize(InfoInterface $payment, $amount)
    {
        // Simulated authorization - always succeed
        $payment->setIsTransactionPending(false)
            ->setStatus(self::STATUS_APPROVED);

        return $this;
    }

    /**
     * Capture payment
     */
    public function capture(InfoInterface $payment, $amount)
    {
        // Simulated capture - always succeed
        $payment->setStatus(self::STATUS_APPROVED)
            ->setIsTransactionClosed(false);

        // Add transaction info
        $payment->setAdditionalInformation('transaction_id', 'VIETQR-' . time())
            ->setAdditionalInformation('status', 'CAPTURED')
            ->setAdditionalInformation('amount', $amount);

        return $this;
    }

    /**
     * Refund payment
     */
    public function refund(InfoInterface $payment, $amount)
    {
        // Simulated refund - always succeed
        $payment->setStatus(self::STATUS_APPROVED);
        return $this;
    }

    /**
     * Void payment
     */
    public function void(InfoInterface $payment)
    {
        return $this;
    }

    /**
     * Check if payment method is available
     */
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        return parent::isAvailable($quote);
    }
}

