<?php

namespace Bookstore\CoreApi\Api\Data;

interface StockInterface
{
    /** @return bool */
    public function getIsInStock();
    /**
     * @param bool $isInStock
     * @return $this
     */
    public function setIsInStock($isInStock);
    /** @return float */
    public function getQty();
    /**
     * @param float $qty
     * @return $this
     */
    public function setQty($qty);
}
