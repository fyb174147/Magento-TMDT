<?php

namespace Bookstore\CoreApi\Api;

interface OrderRepositoryInterface
{
    /**
     * Create a new order with provided order data
     *
     * @param \Bookstore\CoreApi\Api\Data\OrderInterface $order
     * @return \Bookstore\CoreApi\Api\Data\OrderInterface
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\State\InvalidTransitionException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function create(\Bookstore\CoreApi\Api\Data\OrderInterface $order);

    /**
     * Get order by order ID
     *
     * @param int $orderId
     * @return \Bookstore\CoreApi\Api\Data\OrderInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($orderId);
}
