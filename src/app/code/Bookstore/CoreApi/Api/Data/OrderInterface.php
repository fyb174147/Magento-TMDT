<?php

namespace Bookstore\CoreApi\Api\Data;

interface OrderInterface
{
    /**
     * Get customer email
     *
     * @return string
     */
    public function getCustomerEmail();

    /**
     * Set customer email
     *
     * @param string $email
     * @return $this
     */
    public function setCustomerEmail($email);

    /**
     * Get customer firstname
     *
     * @return string
     */
    public function getCustomerFirstname();

    /**
     * Set customer firstname
     *
     * @param string $firstname
     * @return $this
     */
    public function setCustomerFirstname($firstname);

    /**
     * Get customer lastname
     *
     * @return string
     */
    public function getCustomerLastname();

    /**
     * Set customer lastname
     *
     * @param string $lastname
     * @return $this
     */
    public function setCustomerLastname($lastname);

    /**
     * Get billing address
     *
     * @return \Bookstore\CoreApi\Api\Data\AddressInterface|null
     */
    public function getBillingAddress();

    /**
     * Set billing address
     *
     * @param \Bookstore\CoreApi\Api\Data\AddressInterface|mixed[] $address
     * @return $this
     */
    public function setBillingAddress($address);

    /**
     * Get shipping address
     *
     * @return \Bookstore\CoreApi\Api\Data\AddressInterface|null
     */
    public function getShippingAddress();

    /**
     * Set shipping address
     *
     * @param \Bookstore\CoreApi\Api\Data\AddressInterface|mixed[] $address
     * @return $this
     */
    public function setShippingAddress($address);

    /**
     * Get items
     *
     * @return \Bookstore\CoreApi\Api\Data\OrderItemInterface[]|mixed[]
     */
    public function getItems();

    /**
     * Set items
     *
     * @param \Bookstore\CoreApi\Api\Data\OrderItemInterface[]|mixed[] $items
     * @return $this
     */
    public function setItems($items);

    /**
     * Get payment method
     *
     * @return string|null
     */
    public function getPaymentMethod();

    /**
     * Set payment method
     *
     * @param string|null $method
     * @return $this
     */
    public function setPaymentMethod($method);

    /**
     * Get shipping method
     *
     * @return string|null
     */
    public function getShippingMethod();

    /**
     * Set shipping method
     *
     * @param string|null $method
     * @return $this
     */
    public function setShippingMethod($method);

    /** @return int|null */
    public function getId();

    /**
     * @param int|null $id
     * @return $this
     */
    public function setId($id);

    /** @return string|null */
    public function getIncrementId();

    /**
     * @param string|null $incrementId
     * @return $this
     */
    public function setIncrementId($incrementId);

    /** @return string|null */
    public function getStatus();

    /**
     * @param string|null $status
     * @return $this
     */
    public function setStatus($status);

    /** @return string|null */
    public function getState();

    /**
     * @param string|null $state
     * @return $this
     */
    public function setState($state);

    /** @return float */
    public function getGrandTotal();

    /**
     * @param float $total
     * @return $this
     */
    public function setGrandTotal($total);

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
     * @param float $amount
     * @return $this
     */
    public function setShippingAmount($amount);

    /** @return float */
    public function getTaxAmount();

    /**
     * @param float $amount
     * @return $this
     */
    public function setTaxAmount($amount);

    /** @return string|null */
    public function getCreatedAt();

    /**
     * @param string|null $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);
}
