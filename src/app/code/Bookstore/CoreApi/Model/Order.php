<?php

namespace Bookstore\CoreApi\Model;

use Bookstore\CoreApi\Api\Data\OrderInterface;

class Order implements OrderInterface
{
    private $id;
    private $incrementId;
    private $status;
    private $state;
    private $customerEmail;
    private $customerFirstname;
    private $customerLastname;
    private $billingAddress;
    private $shippingAddress;
    private $items = [];
    private $paymentMethod;
    private $shippingMethod;
    private $grandTotal = 0;
    private $subtotal = 0;
    private $shippingAmount = 0;
    private $taxAmount = 0;
    private $createdAt;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function getIncrementId()
    {
        return $this->incrementId;
    }

    public function setIncrementId($incrementId)
    {
        $this->incrementId = $incrementId;
        return $this;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    public function getState()
    {
        return $this->state;
    }

    public function setState($state)
    {
        $this->state = $state;
        return $this;
    }

    public function getCustomerEmail()
    {
        return $this->customerEmail;
    }

    public function setCustomerEmail($email)
    {
        $this->customerEmail = $email;
        return $this;
    }

    public function getCustomerFirstname()
    {
        return $this->customerFirstname;
    }

    public function setCustomerFirstname($firstname)
    {
        $this->customerFirstname = $firstname;
        return $this;
    }

    public function getCustomerLastname()
    {
        return $this->customerLastname;
    }

    public function setCustomerLastname($lastname)
    {
        $this->customerLastname = $lastname;
        return $this;
    }

    public function getBillingAddress()
    {
        return $this->billingAddress;
    }

    public function setBillingAddress($address)
    {
        $this->billingAddress = $address;
        return $this;
    }

    public function getShippingAddress()
    {
        return $this->shippingAddress;
    }

    public function setShippingAddress($address)
    {
        $this->shippingAddress = $address;
        return $this;
    }

    public function getItems()
    {
        return $this->items;
    }

    public function setItems($items)
    {
        $this->items = $items;
        return $this;
    }

    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    public function setPaymentMethod($method)
    {
        $this->paymentMethod = $method;
        return $this;
    }

    public function getShippingMethod()
    {
        return $this->shippingMethod;
    }

    public function setShippingMethod($method)
    {
        $this->shippingMethod = $method;
        return $this;
    }

    public function getGrandTotal()
    {
        return $this->grandTotal;
    }

    public function setGrandTotal($total)
    {
        $this->grandTotal = $total;
        return $this;
    }

    public function getSubtotal()
    {
        return $this->subtotal;
    }

    public function setSubtotal($subtotal)
    {
        $this->subtotal = $subtotal;
        return $this;
    }

    public function getShippingAmount()
    {
        return $this->shippingAmount;
    }

    public function setShippingAmount($amount)
    {
        $this->shippingAmount = $amount;
        return $this;
    }

    public function getTaxAmount()
    {
        return $this->taxAmount;
    }

    public function setTaxAmount($amount)
    {
        $this->taxAmount = $amount;
        return $this;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }
}
