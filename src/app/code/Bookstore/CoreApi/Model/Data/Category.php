<?php

namespace Bookstore\CoreApi\Model\Data;

use Bookstore\CoreApi\Api\Data\CategoryInterface;

class Category implements CategoryInterface
{
    use DataObjectTrait;

    public function getId() { return (int)$this->getValue('id', 0); }
    public function setId($id) { return $this->setValue('id', (int)$id); }
    public function getName() { return (string)$this->getValue('name', ''); }
    public function setName($name) { return $this->setValue('name', (string)$name); }
    public function getIsActive() { return (bool)$this->getValue('is_active', false); }
    public function setIsActive($isActive) { return $this->setValue('is_active', (bool)$isActive); }
    public function getChildren() { return $this->getValue('children', []); }
    public function setChildren($children) { return $this->setValue('children', is_array($children) ? $children : []); }
}
