<?php

namespace Bookstore\Utility\Controller\Index;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Result\PageFactory;

class Index implements HttpGetActionInterface
{
    public function __construct(
        private readonly PageFactory $pageFactory,
        private readonly RequestInterface $request
    ) {
    }

    public function execute()
    {
        $page = $this->pageFactory->create();
        $titles = [
            'bookstore_info' => __('Thong tin tien ich'),
            'stores' => __('He thong cua hang'),
            'bookstore_policy' => __('Chinh sach va ho tro'),
            'bookstore_about' => __('Gioi thieu BookStore'),
        ];
        $routeName = (string)$this->request->getRouteName();
        $page->getConfig()->getTitle()->set($titles[$routeName] ?? __('BookStore'));
        return $page;
    }
}
