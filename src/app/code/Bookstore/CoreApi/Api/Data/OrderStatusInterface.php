<?php

namespace Bookstore\CoreApi\Api\Data;

interface OrderStatusInterface
{
    /** @return int */
    public function getId();
    /**
     * @param int $id
     * @return $this
     */
    public function setId($id);
    /** @return string */
    public function getIncrementId();
    /**
     * @param string $incrementId
     * @return $this
     */
    public function setIncrementId($incrementId);
    /** @return string */
    public function getStatus();
    /**
     * @param string $status
     * @return $this
     */
    public function setStatus($status);
    /** @return string */
    public function getState();
    /**
     * @param string $state
     * @return $this
     */
    public function setState($state);
    /** @return float */
    public function getGrandTotal();
    /**
     * @param float $grandTotal
     * @return $this
     */
    public function setGrandTotal($grandTotal);
    /** @return string */
    public function getCreatedAt();
    /**
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);
}
