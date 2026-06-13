<?php

namespace Bookstore\CoreApi\Model\Data;

use Bookstore\CoreApi\Api\Data\ProductListInterface;

class ProductList implements ProductListInterface
{
    use DataObjectTrait;

    public function getItems() { return $this->getValue('items', []); }
    public function setItems($items) { return $this->setValue('items', is_array($items) ? $items : []); }
    public function getTotal() { return (int)$this->getValue('total', 0); }
    public function setTotal($total) { return $this->setValue('total', (int)$total); }
}
