<?php

namespace Bookstore\CoreApi\Api\Data;

interface CustomerProfileInterface extends CustomerMeInterface
{
    /** @return int */
    public function getGroupId();
    /**
     * @param int $groupId
     * @return $this
     */
    public function setGroupId($groupId);
    /** @return string */
    public function getCreatedAt();
    /**
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);
}
