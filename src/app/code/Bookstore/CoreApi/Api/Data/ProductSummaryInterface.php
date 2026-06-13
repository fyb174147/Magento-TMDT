<?php

namespace Bookstore\CoreApi\Api\Data;

interface ProductSummaryInterface
{
    /** @return int */
    public function getId();
    /**
     * @param int $id
     * @return $this
     */
    public function setId($id);
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
    public function getPrice();
    /**
     * @param float $price
     * @return $this
     */
    public function setPrice($price);
    /** @return string */
    public function getType();
    /**
     * @param string $type
     * @return $this
     */
    public function setType($type);
    /** @return string */
    public function getUrlKey();
    /**
     * @param string $urlKey
     * @return $this
     */
    public function setUrlKey($urlKey);
    /** @return string|null */
    public function getImage();
    /**
     * @param string|null $image
     * @return $this
     */
    public function setImage($image);
}
