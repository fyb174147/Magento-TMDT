<?php

namespace Bookstore\CoreApi\Model\Data;

use Bookstore\CoreApi\Api\Data\CustomerMeInterface;

class CustomerMe implements CustomerMeInterface
{
    use DataObjectTrait;

    public function getLoggedIn() { return (bool)$this->getValue('logged_in', false); }
    public function setLoggedIn($loggedIn) { return $this->setValue('logged_in', (bool)$loggedIn); }
    public function getId() { return (int)$this->getValue('id', 0); }
    public function setId($id) { return $this->setValue('id', (int)$id); }
    public function getEmail() { return (string)$this->getValue('email', ''); }
    public function setEmail($email) { return $this->setValue('email', (string)$email); }
    public function getFirstname() { return (string)$this->getValue('firstname', ''); }
    public function setFirstname($firstname) { return $this->setValue('firstname', (string)$firstname); }
    public function getLastname() { return (string)$this->getValue('lastname', ''); }
    public function setLastname($lastname) { return $this->setValue('lastname', (string)$lastname); }
}
