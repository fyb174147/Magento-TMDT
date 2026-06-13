<?php

namespace Bookstore\Payment\ViewModel;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class SuccessPayment implements ArgumentInterface
{
    private const VIETQR_METHOD = 'bookstore_vietqr';
    private const COD_METHOD = 'cashondelivery';
    private const BANK_ID = 'BIDV';
    private const ACCOUNT_NO = '8870380558';
    private const ACCOUNT_NAME = 'BOOKSTORE DEMO';
    private const QR_TEMPLATE = 'compact';

    private CheckoutSession $checkoutSession;

    public function __construct(CheckoutSession $checkoutSession)
    {
        $this->checkoutSession = $checkoutSession;
    }

    public function getOrder()
    {
        $order = $this->checkoutSession->getLastRealOrder();

        return $order && $order->getId() ? $order : null;
    }

    public function getPaymentCode($order): string
    {
        $payment = $order ? $order->getPayment() : null;

        return $payment ? (string)$payment->getMethod() : '';
    }

    public function getPaymentTitle($order): string
    {
        $code = $this->getPaymentCode($order);

        if ($code === self::VIETQR_METHOD) {
            return 'Chuyển khoản VietQR';
        }

        if ($code === self::COD_METHOD) {
            return 'Thanh toán khi nhận hàng (COD)';
        }

        $payment = $order ? $order->getPayment() : null;

        return $payment ? (string)$payment->getMethodInstance()->getTitle() : 'Chưa xác định';
    }

    public function isVietQr($order): bool
    {
        return $this->getPaymentCode($order) === self::VIETQR_METHOD;
    }

    public function getFormattedAmount($order): string
    {
        $amount = $order ? (float)$order->getGrandTotal() : 0.0;

        return number_format($amount, 0, ',', '.') . ' đ';
    }

    public function getTransferContent($order): string
    {
        $incrementId = $order ? (string)$order->getIncrementId() : '';

        return trim('BOOKSTORE ' . $incrementId);
    }

    public function getQrUrl($order): string
    {
        $amount = $order ? (int)round((float)$order->getGrandTotal()) : 0;

        return 'https://img.vietqr.io/image/' . rawurlencode(self::BANK_ID)
            . '-' . rawurlencode(self::ACCOUNT_NO)
            . '-' . rawurlencode(self::QR_TEMPLATE)
            . '.png?amount=' . rawurlencode((string)$amount)
            . '&addInfo=' . rawurlencode($this->getTransferContent($order))
            . '&accountName=' . rawurlencode(self::ACCOUNT_NAME);
    }

    public function getBankLabel(): string
    {
        return self::BANK_ID;
    }

    public function getAccountNo(): string
    {
        return self::ACCOUNT_NO;
    }

    public function getAccountName(): string
    {
        return self::ACCOUNT_NAME;
    }
}
