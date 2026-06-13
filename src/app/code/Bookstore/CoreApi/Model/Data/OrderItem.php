<?php

namespace Bookstore\CoreApi\Model\Data;

use Bookstore\CoreApi\Api\Data\OrderItemInterface;

class OrderItem implements OrderItemInterface
{
    use DataObjectTrait;

    public function getProductId() { return (int)$this->getValue('product_id', 0); }
    public function setProductId($productId) { return $this->setValue('product_id', (int)$productId); }
    public function getSku() { return (string)$this->getValue('sku', ''); }
    public function setSku($sku) { return $this->setValue('sku', (string)$sku); }
    public function getName() { return (string)$this->getValue('name', ''); }
    public function setName($name) { return $this->setValue('name', (string)$name); }
    public function getQty() { return (float)$this->getValue('qty', 0); }
    public function setQty($qty) { return $this->setValue('qty', (float)$qty); }
    public function getPrice() { return (float)$this->getValue('price', 0); }
    public function setPrice($price) { return $this->setValue('price', (float)$price); }
    public function getRowTotal() { return (float)$this->getValue('row_total', 0); }
    public function setRowTotal($rowTotal) { return $this->setValue('row_total', (float)$rowTotal); }
}
