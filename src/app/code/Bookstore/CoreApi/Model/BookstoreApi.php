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
     * @return mixed[]
     * @throws NoSuchEntityException
     */
    public function getProduct($productId)
    {
        $product = $this->productRepository->getById((int)$productId);
        $stock = $this->stockRegistry->getStockItem((int)$product->getId());
        $image = (string)$product->getData('image');
        $mediaUrl = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);

        return [
            'id' => (int)$product->getId(),
            'sku' => (string)$product->getSku(),
            'name' => (string)$product->getName(),
            'price' => (float)$product->getPrice(),
            'special_price' => $product->getSpecialPrice() !== null ? (float)$product->getSpecialPrice() : null,
            'type' => (string)$product->getTypeId(),
            'status' => (int)$product->getStatus(),
            'visibility' => (int)$product->getVisibility(),
            'url_key' => (string)$product->getUrlKey(),
            'short_description' => (string)$product->getShortDescription(),
            'description' => (string)$product->getDescription(),
            'image' => $image && $image !== 'no_selection' ? $mediaUrl . 'catalog/product' . $image : null,
            'stock' => [
                'is_in_stock' => (bool)$stock->getIsInStock(),
                'qty' => (float)$stock->getQty(),
            ],
        ];
    }

    /**
     * @return mixed[]
     * @throws AuthorizationException
     * @throws NoSuchEntityException
     */
    public function getCustomerProfile()
    {
        $customer = $this->customerRepository->getById($this->getCustomerId());

        return [
            'id' => (int)$customer->getId(),
            'email' => (string)$customer->getEmail(),
            'firstname' => (string)$customer->getFirstname(),
            'lastname' => (string)$customer->getLastname(),
            'group_id' => (int)$customer->getGroupId(),
            'created_at' => (string)$customer->getCreatedAt(),
        ];
    }

    /**
     * @return mixed[]
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

        return ['items' => $items, 'total' => count($items)];
    }

    /**
     * @param int $orderId
     * @return mixed[]
     * @throws AuthorizationException
     * @throws NoSuchEntityException
     */
    public function getOrderStatus($orderId)
    {
        $order = $this->getCustomerOrder((int)$orderId);

        return [
            'id' => (int)$order->getEntityId(),
            'increment_id' => (string)$order->getIncrementId(),
            'status' => (string)$order->getStatus(),
            'state' => (string)$order->getState(),
            'grand_total' => (float)$order->getGrandTotal(),
            'created_at' => (string)$order->getCreatedAt(),
        ];
    }

    /**
     * @param int|null $orderId
     * @param float|null $amount
     * @param string|null $description
     * @return mixed[]
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

        return [
            'qr_url' => $qrUrl,
            'bank_id' => self::VIETQR_BANK_ID,
            'account_no' => self::VIETQR_ACCOUNT_NO,
            'account_name' => self::VIETQR_ACCOUNT_NAME,
            'amount' => (int)round($amount),
            'add_info' => $addInfo,
        ];
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

    private function mapOrderSummary(MagentoOrderInterface $order): array
    {
        $items = [];
        foreach ($order->getAllVisibleItems() as $item) {
            $items[] = [
                'sku' => (string)$item->getSku(),
                'name' => (string)$item->getName(),
                'qty_ordered' => (float)$item->getQtyOrdered(),
                'price' => (float)$item->getPrice(),
                'row_total' => (float)$item->getRowTotal(),
            ];
        }

        return [
            'id' => (int)$order->getEntityId(),
            'increment_id' => (string)$order->getIncrementId(),
            'status' => (string)$order->getStatus(),
            'state' => (string)$order->getState(),
            'grand_total' => (float)$order->getGrandTotal(),
            'subtotal' => (float)$order->getSubtotal(),
            'shipping_amount' => (float)$order->getShippingAmount(),
            'created_at' => (string)$order->getCreatedAt(),
            'payment_method' => $order->getPayment() ? (string)$order->getPayment()->getMethod() : null,
            'shipping_method' => (string)$order->getShippingMethod(),
            'items' => $items,
        ];
    }
}
