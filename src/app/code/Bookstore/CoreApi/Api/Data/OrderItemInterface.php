<?php

namespace Bookstore\CoreApi\Api\Data;

interface OrderItemInterface
{
    /** @return int */
    public function getProductId();
    /**
     * @param int $productId
     * @return $this
     */
    public function setProductId($productId);
    /** @return string */
    public function getSku();
    /**
     * @param string $sku
     * @return $this
     */
    public function setSku($sku);
    /** @return string */
    public function getName();
    /**
     * @param string $name
     * @return $this
     */
    public function setName($name);
    /** @return float */
    public function getQty();
    /**
     * @param float $qty
     * @return $this
     */
    public function setQty($qty);
    /** @return float */
    public function getPrice();
    /**
     * @param float $price
     * @return $this
     */
    public function setPrice($price);
    /** @return float */
    public function getRowTotal();
    /**
     * @param float $rowTotal
     * @return $this
     */
    public function setRowTotal($rowTotal);
}
