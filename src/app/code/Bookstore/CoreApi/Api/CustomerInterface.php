<?php

namespace Bookstore\CoreApi\Api;

interface CustomerInterface
{
    /**
     * @return \Bookstore\CoreApi\Api\Data\CustomerMeInterface
     */
    public function me();
}
