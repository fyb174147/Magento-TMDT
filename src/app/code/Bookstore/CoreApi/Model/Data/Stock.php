<?php

namespace Bookstore\CoreApi\Model\Data;

use Bookstore\CoreApi\Api\Data\StockInterface;

class Stock implements StockInterface
{
    use DataObjectTrait;

    public function getIsInStock() { return (bool)$this->getValue('is_in_stock', false); }
    public function setIsInStock($isInStock) { return $this->setValue('is_in_stock', (bool)$isInStock); }
    public function getQty() { return (float)$this->getValue('qty', 0); }
    public function setQty($qty) { return $this->setValue('qty', (float)$qty); }
}
