<?php

namespace Bookstore\CoreApi\Model\Data;

use Bookstore\CoreApi\Api\Data\OrderSummaryInterface;

class OrderSummary implements OrderSummaryInterface
{
    use DataObjectTrait;

    public function getId() { return (int)$this->getValue('id', 0); }
    public function setId($id) { return $this->setValue('id', (int)$id); }
    public function getIncrementId() { return (string)$this->getValue('increment_id', ''); }
    public function setIncrementId($incrementId) { return $this->setValue('increment_id', (string)$incrementId); }
    public function getStatus() { return (string)$this->getValue('status', ''); }
    public function setStatus($status) { return $this->setValue('status', (string)$status); }
    public function getState() { return (string)$this->getValue('state', ''); }
    public function setState($state) { return $this->setValue('state', (string)$state); }
    public function getGrandTotal() { return (float)$this->getValue('grand_total', 0); }
    public function setGrandTotal($grandTotal) { return $this->setValue('grand_total', (float)$grandTotal); }
    public function getSubtotal() { return (float)$this->getValue('subtotal', 0); }
    public function setSubtotal($subtotal) { return $this->setValue('subtotal', (float)$subtotal); }
    public function getShippingAmount() { return (float)$this->getValue('shipping_amount', 0); }
    public function setShippingAmount($shippingAmount) { return $this->setValue('shipping_amount', (float)$shippingAmount); }
    public function getCreatedAt() { return (string)$this->getValue('created_at', ''); }
    public function setCreatedAt($createdAt) { return $this->setValue('created_at', (string)$createdAt); }
    public function getPaymentMethod() { return $this->getValue('payment_method'); }
    public function setPaymentMethod($paymentMethod) { return $this->setValue('payment_method', $paymentMethod !== null ? (string)$paymentMethod : null); }
    public function getShippingMethod() { return (string)$this->getValue('shipping_method', ''); }
    public function setShippingMethod($shippingMethod) { return $this->setValue('shipping_method', (string)$shippingMethod); }
    public function getItems() { return $this->getValue('items', []); }
    public function setItems($items) { return $this->setValue('items', is_array($items) ? $items : []); }
}
