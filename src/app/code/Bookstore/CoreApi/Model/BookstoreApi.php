<?php

namespace Bookstore\CoreApi\Model;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Sales\Api\Data\OrderInterface as MagentoOrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Bookstore\CoreApi\Model\Data\ProductDetail;
use Bookstore\CoreApi\Model\Data\Stock;
use Bookstore\CoreApi\Model\Data\CustomerProfile;
use Bookstore\CoreApi\Model\Data\OrderItem;
use Bookstore\CoreApi\Model\Data\OrderList;
use Bookstore\CoreApi\Model\Data\OrderStatus;
use Bookstore\CoreApi\Model\Data\OrderSummary;
use Bookstore\CoreApi\Model\Data\VietQr;

class BookstoreApi
{
    private const VIETQR_BANK_ID = 'VCB';
    private const VIETQR_ACCOUNT_NO = '0123456789';
    private const VIETQR_ACCOUNT_NAME = 'BOOKSTORE DEMO';
    private const VIETQR_TEMPLATE = 'compact2';

    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly StockRegistryInterface $stockRegistry,
        private readonly CustomerRepositoryInterface $customerRepository,
        private readonly StoreManagerInterface $storeManager,
        private readonly UserContextInterface $userContext,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly OrderCollectionFactory $orderCollectionFactory
    ) {
    }

    /**
     * @param int $productId
     * @return \Bookstore\CoreApi\Api\Data\ProductDetailInterface
     * @throws NoSuchEntityException
     */
    public function getProduct($productId)
    {
        $product = $this->productRepository->getById((int)$productId);
        $stock = $this->stockRegistry->getStockItem((int)$product->getId());
        $image = (string)$product->getData('image');
        $mediaUrl = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);

        $stockData = (new Stock())
            ->setIsInStock((bool)$stock->getIsInStock())
            ->setQty((float)$stock->getQty());

        return (new ProductDetail())
            ->setId((int)$product->getId())
            ->setSku((string)$product->getSku())
            ->setName((string)$product->getName())
            ->setPrice((float)$product->getPrice())
            ->setSpecialPrice($product->getSpecialPrice() !== null ? (float)$product->getSpecialPrice() : null)
            ->setType((string)$product->getTypeId())
            ->setStatus((int)$product->getStatus())
            ->setVisibility((int)$product->getVisibility())
            ->setUrlKey((string)$product->getUrlKey())
            ->setShortDescription((string)$product->getShortDescription())
            ->setDescription((string)$product->getDescription())
            ->setImage($image && $image !== 'no_selection' ? $mediaUrl . 'catalog/product' . $image : null)
            ->setStock($stockData);
    }

    /**
     * @return \Bookstore\CoreApi\Api\Data\CustomerProfileInterface
     * @throws AuthorizationException
     * @throws NoSuchEntityException
     */
    public function getCustomerProfile()
    {
        $customer = $this->customerRepository->getById($this->getCustomerId());

        return (new CustomerProfile())
            ->setLoggedIn(true)
            ->setId((int)$customer->getId())
            ->setEmail((string)$customer->getEmail())
            ->setFirstname((string)$customer->getFirstname())
            ->setLastname((string)$customer->getLastname())
            ->setGroupId((int)$customer->getGroupId())
            ->setCreatedAt((string)$customer->getCreatedAt());
    }

    /**
     * @return \Bookstore\CoreApi\Api\Data\OrderListInterface
     * @throws AuthorizationException
     */
    public function getCustomerOrders()
    {
        $customerId = $this->getCustomerId();
        $collection = $this->orderCollectionFactory->create()
            ->addFieldToFilter('customer_id', $customerId)
            ->setOrder('created_at', 'DESC')
            ->setPageSize(20);

        $items = [];
        foreach ($collection as $order) {
            $items[] = $this->mapOrderSummary($order);
        }

        return (new OrderList())->setItems($items)->setTotal(count($items));
    }

    /**
     * @param int $orderId
     * @return \Bookstore\CoreApi\Api\Data\OrderStatusInterface
     * @throws AuthorizationException
     * @throws NoSuchEntityException
     */
    public function getOrderStatus($orderId)
    {
        $order = $this->getCustomerOrder((int)$orderId);

        return (new OrderStatus())
            ->setId((int)$order->getEntityId())
            ->setIncrementId((string)$order->getIncrementId())
            ->setStatus((string)$order->getStatus())
            ->setState((string)$order->getState())
            ->setGrandTotal((float)$order->getGrandTotal())
            ->setCreatedAt((string)$order->getCreatedAt());
    }

    /**
     * @param int|null $orderId
     * @param float|null $amount
     * @param string|null $description
     * @return \Bookstore\CoreApi\Api\Data\VietQrInterface
     * @throws AuthorizationException
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function getVietQr($orderId = null, $amount = null, $description = null)
    {
        if ($orderId) {
            $order = $this->getCustomerOrder((int)$orderId);
            $amount = $amount !== null ? (float)$amount : (float)$order->getGrandTotal();
            $description = $description ?: 'BOOKSTORE ' . $order->getIncrementId();
        }

        $amount = (float)$amount;
        if ($amount <= 0) {
            throw new InputException(__('Amount must be greater than zero.'));
        }

        $addInfo = substr((string)($description ?: 'BOOKSTORE'), 0, 25);
        $baseUrl = sprintf(
            'https://img.vietqr.io/image/%s-%s-%s.png',
            rawurlencode(self::VIETQR_BANK_ID),
            rawurlencode(self::VIETQR_ACCOUNT_NO),
            rawurlencode(self::VIETQR_TEMPLATE)
        );
        $qrUrl = $baseUrl . '?' . http_build_query([
            'amount' => (int)round($amount),
            'addInfo' => $addInfo,
            'accountName' => self::VIETQR_ACCOUNT_NAME,
        ]);

        return (new VietQr())
            ->setQrUrl($qrUrl)
            ->setBankId(self::VIETQR_BANK_ID)
            ->setAccountNo(self::VIETQR_ACCOUNT_NO)
            ->setAccountName(self::VIETQR_ACCOUNT_NAME)
            ->setAmount((int)round($amount))
            ->setAddInfo($addInfo);
    }

    /**
     * @throws AuthorizationException
     */
    private function getCustomerId(): int
    {
        $customerId = (int)$this->userContext->getUserId();
        if (!$customerId || (int)$this->userContext->getUserType() !== UserContextInterface::USER_TYPE_CUSTOMER) {
            throw new AuthorizationException(__('Customer token is required.'));
        }

        return $customerId;
    }

    /**
     * @throws AuthorizationException
     * @throws NoSuchEntityException
     */
    private function getCustomerOrder(int $orderId): MagentoOrderInterface
    {
        $order = $this->orderRepository->get($orderId);
        if ((int)$order->getCustomerId() !== $this->getCustomerId()) {
            throw new NoSuchEntityException(__('Order #%1 not found.', $orderId));
        }

        return $order;
    }

    private function mapOrderSummary(MagentoOrderInterface $order): OrderSummary
    {
        $items = [];
        foreach ($order->getAllVisibleItems() as $item) {
            $items[] = (new OrderItem())
                ->setProductId((int)$item->getProductId())
                ->setSku((string)$item->getSku())
                ->setName((string)$item->getName())
                ->setQty((float)$item->getQtyOrdered())
                ->setPrice((float)$item->getPrice())
                ->setRowTotal((float)$item->getRowTotal());
        }

        return (new OrderSummary())
            ->setId((int)$order->getEntityId())
            ->setIncrementId((string)$order->getIncrementId())
            ->setStatus((string)$order->getStatus())
            ->setState((string)$order->getState())
            ->setGrandTotal((float)$order->getGrandTotal())
            ->setSubtotal((float)$order->getSubtotal())
            ->setShippingAmount((float)$order->getShippingAmount())
            ->setCreatedAt((string)$order->getCreatedAt())
            ->setPaymentMethod($order->getPayment() ? (string)$order->getPayment()->getMethod() : null)
            ->setShippingMethod((string)$order->getShippingMethod())
            ->setItems($items);
    }
}
