<?php

namespace Bookstore\CoreApi\Api;

interface CatalogInterface
{
    /**
     * @return \Bookstore\CoreApi\Api\Data\ProductListInterface
     */
    public function getProducts();

    /**
     * @return \Bookstore\CoreApi\Api\Data\CategoryInterface
     */
    public function getCategories();
}
