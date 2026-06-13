<?php

namespace Bookstore\CoreApi\Api\Data;

interface CustomerMeInterface
{
    /** @return bool */
    public function getLoggedIn();
    /**
     * @param bool $loggedIn
     * @return $this
     */
    public function setLoggedIn($loggedIn);
    /** @return int */
    public function getId();
    /**
     * @param int $id
     * @return $this
     */
    public function setId($id);
    /** @return string */
    public function getEmail();
    /**
     * @param string $email
     * @return $this
     */
    public function setEmail($email);
    /** @return string */
    public function getFirstname();
    /**
     * @param string $firstname
     * @return $this
     */
    public function setFirstname($firstname);
    /** @return string */
    public function getLastname();
    /**
     * @param string $lastname
     * @return $this
     */
    public function setLastname($lastname);
}
