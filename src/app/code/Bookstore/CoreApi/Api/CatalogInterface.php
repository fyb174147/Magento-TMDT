<?php

namespace Bookstore\CoreApi\Api;

interface CatalogInterface
{
    /**
     * @return mixed[]
     */
    public function getProducts();

    /**
     * @return mixed[]
     */
    public function getCategories();
}
