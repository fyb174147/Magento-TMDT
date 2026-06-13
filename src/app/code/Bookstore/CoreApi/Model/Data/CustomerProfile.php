<?php

namespace Bookstore\CoreApi\Model\Data;

use Bookstore\CoreApi\Api\Data\CustomerProfileInterface;

class CustomerProfile extends CustomerMe implements CustomerProfileInterface
{
    public function getGroupId() { return (int)$this->getValue('group_id', 0); }
    public function setGroupId($groupId) { return $this->setValue('group_id', (int)$groupId); }
    public function getCreatedAt() { return (string)$this->getValue('created_at', ''); }
    public function setCreatedAt($createdAt) { return $this->setValue('created_at', (string)$createdAt); }
}
