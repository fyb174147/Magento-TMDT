<?php

namespace Bookstore\CoreApi\Api\Data;

interface CategoryInterface
{
    /** @return int */
    public function getId();
    /**
     * @param int $id
     * @return $this
     */
    public function setId($id);
    /** @return string */
    public function getName();
    /**
     * @param string $name
     * @return $this
     */
    public function setName($name);
    /** @return bool */
    public function getIsActive();
    /**
     * @param bool $isActive
     * @return $this
     */
    public function setIsActive($isActive);
    /** @return \Bookstore\CoreApi\Api\Data\CategoryInterface[] */
    public function getChildren();
    /**
     * @param \Bookstore\CoreApi\Api\Data\CategoryInterface[] $children
     * @return $this
     */
    public function setChildren($children);
}
