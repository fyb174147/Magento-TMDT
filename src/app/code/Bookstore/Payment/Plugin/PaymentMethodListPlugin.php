<?php

namespace Bookstore\Payment\Plugin;

use Magento\Payment\Model\MethodInterface;
use Magento\Payment\Model\MethodList;

class PaymentMethodListPlugin
{
    private const COD_METHOD = 'cashondelivery';
    private const VIETQR_METHOD = 'bookstore_vietqr';

    private const ALLOWED_METHODS = [
        self::COD_METHOD,
        self::VIETQR_METHOD,
    ];

    private const METHOD_TITLES = [
        self::COD_METHOD => 'Thanh toán khi nhận hàng (COD)',
        self::VIETQR_METHOD => 'Chuyển khoản VietQR',
    ];

    /**
     * Keep the storefront checkout focused on COD and VietQR only.
     *
     * @param MethodList $subject
     * @param MethodInterface[] $methods
     * @return MethodInterface[]
     */
    public function afterGetAvailableMethods(MethodList $subject, array $methods): array
    {
        $filteredMethods = [];

        foreach ($methods as $method) {
            if (!in_array($method->getCode(), self::ALLOWED_METHODS, true)) {
                continue;
            }

            if (method_exists($method, 'setTitle') && isset(self::METHOD_TITLES[$method->getCode()])) {
                $method->setTitle(self::METHOD_TITLES[$method->getCode()]);
            }

            $filteredMethods[] = $method;
        }

        usort($filteredMethods, function (MethodInterface $left, MethodInterface $right): int {
            return array_search($left->getCode(), self::ALLOWED_METHODS, true)
                <=> array_search($right->getCode(), self::ALLOWED_METHODS, true);
        });

        return array_values($filteredMethods);
    }
}
