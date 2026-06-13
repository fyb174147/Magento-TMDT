<?php

namespace Bookstore\CoreApi\Model\Data;

use Bookstore\CoreApi\Api\Data\OrderStatusInterface;

class OrderStatus implements OrderStatusInterface
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
    public function getCreatedAt() { return (string)$this->getValue('created_at', ''); }
    public function setCreatedAt($createdAt) { return $this->setValue('created_at', (string)$createdAt); }
}
