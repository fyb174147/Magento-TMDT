<?php

namespace Bookstore\CoreApi\Model\Data;

use Bookstore\CoreApi\Api\Data\ProductDetailInterface;

class ProductDetail extends ProductSummary implements ProductDetailInterface
{
    public function getSpecialPrice() { return $this->getValue('special_price'); }
    public function setSpecialPrice($specialPrice) { return $this->setValue('special_price', $specialPrice !== null ? (float)$specialPrice : null); }
    public function getStatus() { return (int)$this->getValue('status', 0); }
    public function setStatus($status) { return $this->setValue('status', (int)$status); }
    public function getVisibility() { return (int)$this->getValue('visibility', 0); }
    public function setVisibility($visibility) { return $this->setValue('visibility', (int)$visibility); }
    public function getShortDescription() { return (string)$this->getValue('short_description', ''); }
    public function setShortDescription($shortDescription) { return $this->setValue('short_description', (string)$shortDescription); }
    public function getDescription() { return (string)$this->getValue('description', ''); }
    public function setDescription($description) { return $this->setValue('description', (string)$description); }
    public function getStock() { return $this->getValue('stock'); }
    public function setStock($stock) { return $this->setValue('stock', $stock); }
}
