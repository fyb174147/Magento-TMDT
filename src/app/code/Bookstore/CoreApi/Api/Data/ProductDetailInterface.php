<?php

namespace Bookstore\CoreApi\Api\Data;

interface ProductDetailInterface extends ProductSummaryInterface
{
    /** @return float|null */
    public function getSpecialPrice();
    /**
     * @param float|null $specialPrice
     * @return $this
     */
    public function setSpecialPrice($specialPrice);
    /** @return int */
    public function getStatus();
    /**
     * @param int $status
     * @return $this
     */
    public function setStatus($status);
    /** @return int */
    public function getVisibility();
    /**
     * @param int $visibility
     * @return $this
     */
    public function setVisibility($visibility);
    /** @return string */
    public function getShortDescription();
    /**
     * @param string $shortDescription
     * @return $this
     */
    public function setShortDescription($shortDescription);
    /** @return string */
    public function getDescription();
    /**
     * @param string $description
     * @return $this
     */
    public function setDescription($description);
    /** @return \Bookstore\CoreApi\Api\Data\StockInterface */
    public function getStock();
    /**
     * @param \Bookstore\CoreApi\Api\Data\StockInterface $stock
     * @return $this
     */
    public function setStock($stock);
}
