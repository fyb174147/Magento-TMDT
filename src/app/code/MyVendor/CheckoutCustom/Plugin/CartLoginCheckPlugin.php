<?php
namespace MyVendor\CheckoutCustom\Plugin;

class CartLoginCheckPlugin
{
    /** @var \Magento\Customer\Model\Session */
    protected $customerSession;

    /** @var \Magento\Framework\App\Response\Http */
    protected $response;

    /** @var \Magento\Framework\UrlInterface */
    protected $urlBuilder;

    /** @var \Magento\Framework\Message\ManagerInterface */
    protected $messageManager;

    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Response\Http $response,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->customerSession = $customerSession;
        $this->response = $response;
        $this->urlBuilder = $urlBuilder;
        $this->messageManager = $messageManager;
    }

    // Hàm rào chắn chạy TRƯỚC KHI trang giỏ hàng kịp render dữ liệu
    public function beforeExecute(\Magento\Checkout\Controller\Cart\Index $subject)
    {
        // Kiểm tra Session thật từ máy chủ Server của User
        if (!$this->customerSession->isLoggedIn()) {
            // Bắn một thông báo màu đỏ ngoài màn hình login
            $this->messageManager->addErrorMessage(__('Vui lòng đăng nhập tài khoản để xem giỏ hàng và mua sách.'));
            
            // Ép hướng quay xe về thẳng trang login
            $loginUrl = $this->urlBuilder->getUrl('customer/account/login');
            $this->response->setRedirect($loginUrl);
            
            // Chặn đứng không cho Controller của Magento chạy tiếp các lệnh dưới
            $this->response->sendResponse();
            exit;
        }
    }
}