<?php

namespace Bookstore\CoreApi\Model\Data;

use Bookstore\CoreApi\Api\Data\VietQrInterface;

class VietQr implements VietQrInterface
{
    use DataObjectTrait;

    public function getQrUrl() { return (string)$this->getValue('qr_url', ''); }
    public function setQrUrl($qrUrl) { return $this->setValue('qr_url', (string)$qrUrl); }
    public function getBankId() { return (string)$this->getValue('bank_id', ''); }
    public function setBankId($bankId) { return $this->setValue('bank_id', (string)$bankId); }
    public function getAccountNo() { return (string)$this->getValue('account_no', ''); }
    public function setAccountNo($accountNo) { return $this->setValue('account_no', (string)$accountNo); }
    public function getAccountName() { return (string)$this->getValue('account_name', ''); }
    public function setAccountName($accountName) { return $this->setValue('account_name', (string)$accountName); }
    public function getAmount() { return (int)$this->getValue('amount', 0); }
    public function setAmount($amount) { return $this->setValue('amount', (int)$amount); }
    public function getAddInfo() { return (string)$this->getValue('add_info', ''); }
    public function setAddInfo($addInfo) { return $this->setValue('add_info', (string)$addInfo); }
}
