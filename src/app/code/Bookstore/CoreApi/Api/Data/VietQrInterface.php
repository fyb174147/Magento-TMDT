<?php

namespace Bookstore\CoreApi\Api\Data;

interface VietQrInterface
{
    /** @return string */
    public function getQrUrl();
    /**
     * @param string $qrUrl
     * @return $this
     */
    public function setQrUrl($qrUrl);
    /** @return string */
    public function getBankId();
    /**
     * @param string $bankId
     * @return $this
     */
    public function setBankId($bankId);
    /** @return string */
    public function getAccountNo();
    /**
     * @param string $accountNo
     * @return $this
     */
    public function setAccountNo($accountNo);
    /** @return string */
    public function getAccountName();
    /**
     * @param string $accountName
     * @return $this
     */
    public function setAccountName($accountName);
    /** @return int */
    public function getAmount();
    /**
     * @param int $amount
     * @return $this
     */
    public function setAmount($amount);
    /** @return string */
    public function getAddInfo();
    /**
     * @param string $addInfo
     * @return $this
     */
    public function setAddInfo($addInfo);
}
