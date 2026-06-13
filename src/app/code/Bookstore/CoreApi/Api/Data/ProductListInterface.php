<?php

namespace Bookstore\CoreApi\Api\Data;

interface ProductListInterface
{
    /** @return \Bookstore\CoreApi\Api\Data\ProductSummaryInterface[] */
    public function getItems();
    /**
     * @param \Bookstore\CoreApi\Api\Data\ProductSummaryInterface[] $items
     * @return $this
     */
    public function setItems($items);
    /** @return int */
    public function getTotal();
    /**
     * @param int $total
     * @return $this
     */
    public function setTotal($total);
}
