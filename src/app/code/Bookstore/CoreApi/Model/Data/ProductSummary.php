<?php

namespace Bookstore\CoreApi\Model\Data;

use Bookstore\CoreApi\Api\Data\ProductSummaryInterface;

class ProductSummary implements ProductSummaryInterface
{
    use DataObjectTrait;

    public function getId() { return (int)$this->getValue('id', 0); }
    public function setId($id) { return $this->setValue('id', (int)$id); }
    public function getSku() { return (string)$this->getValue('sku', ''); }
    public function setSku($sku) { return $this->setValue('sku', (string)$sku); }
    public function getName() { return (string)$this->getValue('name', ''); }
    public function setName($name) { return $this->setValue('name', (string)$name); }
    public function getPrice() { return (float)$this->getValue('price', 0); }
    public function setPrice($price) { return $this->setValue('price', (float)$price); }
    public function getType() { return (string)$this->getValue('type', ''); }
    public function setType($type) { return $this->setValue('type', (string)$type); }
    public function getUrlKey() { return (string)$this->getValue('url_key', ''); }
    public function setUrlKey($urlKey) { return $this->setValue('url_key', (string)$urlKey); }
    public function getImage() { return $this->getValue('image'); }
    public function setImage($image) { return $this->setValue('image', $image !== null ? (string)$image : null); }
}
