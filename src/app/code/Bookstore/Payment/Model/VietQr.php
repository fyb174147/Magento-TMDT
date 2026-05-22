<?php

namespace Bookstore\Payment\Model;

use Magento\Payment\Model\Method\AbstractMethod;

class VietQr extends AbstractMethod
{
    protected $_code = 'bookstore_vietqr';
    protected $_isOffline = true;
    protected $_canAuthorize = true;
    protected $_canCapture = true;
}
