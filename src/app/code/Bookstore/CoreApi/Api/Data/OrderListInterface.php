<?php

namespace Bookstore\CoreApi\Api\Data;

interface OrderListInterface
{
    /** @return \Bookstore\CoreApi\Api\Data\OrderSummaryInterface[] */
    public function getItems();
    /**
     * @param \Bookstore\CoreApi\Api\Data\OrderSummaryInterface[] $items
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
