<?php

namespace Bookstore\Payment\Model;

use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Payment\Model\InfoInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;

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
     * Store confirmation data submitted by the VietQR renderer.
     */
    public function assignData(DataObject $data)
    {
        parent::assignData($data);

        $additionalData = $data->getData('additional_data');

        if ($additionalData instanceof DataObject) {
            $additionalData = $additionalData->getData();
        }

        if (is_array($additionalData)) {
            $this->getInfoInstance()
                ->setAdditionalInformation(
                    'vietqr_self_confirmed',
                    !empty($additionalData['vietqr_self_confirmed']) ? 1 : 0
                )
                ->setAdditionalInformation(
                    'vietqr_amount',
                    $additionalData['vietqr_amount'] ?? null
                )
                ->setAdditionalInformation(
                    'vietqr_content',
                    $additionalData['vietqr_content'] ?? null
                );
        }

        return $this;
    }

    /**
     * Validate payment method
     */
    public function validate()
    {
        parent::validate();

        if ((int)$this->getInfoInstance()->getAdditionalInformation('vietqr_self_confirmed') !== 1) {
            throw new LocalizedException(
                __('Vui lòng quét mã VietQR và xác nhận đã chuyển khoản trước khi đặt hàng.')
            );
        }

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
