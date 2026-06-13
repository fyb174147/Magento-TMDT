<?php

namespace Bookstore\CoreApi\Api\Data;

interface OrderSummaryInterface
{
    /** @return int */
    public function getId();
    /**
     * @param int $id
     * @return $this
     */
    public function setId($id);
    /** @return string */
    public function getIncrementId();
    /**
     * @param string $incrementId
     * @return $this
     */
    public function setIncrementId($incrementId);
    /** @return string */
    public function getStatus();
    /**
     * @param string $status
     * @return $this
     */
    public function setStatus($status);
    /** @return string */
    public function getState();
    /**
     * @param string $state
     * @return $this
     */
    public function setState($state);
    /** @return float */
    public function getGrandTotal();
    /**
     * @param float $grandTotal
     * @return $this
     */
    public function setGrandTotal($grandTotal);
    /** @return float */
    public function getSubtotal();
    /**
     * @param float $subtotal
     * @return $this
     */
    public function setSubtotal($subtotal);
    /** @return float */
    public function getShippingAmount();
    /**
     * @param float $shippingAmount
     * @return $this
     */
    public function setShippingAmount($shippingAmount);
    /** @return string */
    public function getCreatedAt();
    /**
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);
    /** @return string|null */
    public function getPaymentMethod();
    /**
     * @param string|null $paymentMethod
     * @return $this
     */
    public function setPaymentMethod($paymentMethod);
    /** @return string */
    public function getShippingMethod();
    /**
     * @param string $shippingMethod
     * @return $this
     */
    public function setShippingMethod($shippingMethod);
    /** @return \Bookstore\CoreApi\Api\Data\OrderItemInterface[] */
    public function getItems();
    /**
     * @param \Bookstore\CoreApi\Api\Data\OrderItemInterface[] $items
     * @return $this
     */
    public function setItems($items);
}
