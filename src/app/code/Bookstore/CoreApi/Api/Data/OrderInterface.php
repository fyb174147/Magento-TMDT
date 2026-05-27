<?php

namespace Bookstore\CoreApi\Api\Data;

interface OrderInterface
{
    /**
     * Get customer email
     */
    public function getCustomerEmail();

    /**
     * Set customer email
     */
    public function setCustomerEmail($email);

    /**
     * Get customer firstname
     */
    public function getCustomerFirstname();

    /**
     * Set customer firstname
     */
    public function setCustomerFirstname($firstname);

    /**
     * Get customer lastname
     */
    public function getCustomerLastname();

    /**
     * Set customer lastname
     */
    public function setCustomerLastname($lastname);

    /**
     * Get billing address
     */
    public function getBillingAddress();

    /**
     * Set billing address
     */
    public function setBillingAddress($address);

    /**
     * Get shipping address
     */
    public function getShippingAddress();

    /**
     * Set shipping address
     */
    public function setShippingAddress($address);

    /**
     * Get items
     */
    public function getItems();

    /**
     * Set items
     */
    public function setItems($items);

    /**
     * Get payment method
     */
    public function getPaymentMethod();

    /**
     * Set payment method
     */
    public function setPaymentMethod($method);

    /**
     * Get shipping method
     */
    public function getShippingMethod();

    /**
     * Set shipping method
     */
    public function setShippingMethod($method);
}
