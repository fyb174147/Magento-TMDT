<?php

namespace Bookstore\CoreApi\Model;

use Bookstore\CoreApi\Api\OrderRepositoryInterface;
use Bookstore\CoreApi\Api\Data\OrderInterface;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\QuoteManagement;
use Magento\Sales\Api\OrderRepositoryInterface as MagentoOrderRepository;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Bookstore\CoreApi\Model\Data\Address;
use Bookstore\CoreApi\Model\Data\OrderItem;

class OrderRepository implements OrderRepositoryInterface
{
    public function __construct(
        private readonly QuoteFactory $quoteFactory,
        private readonly QuoteManagement $quoteManagement,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly MagentoOrderRepository $magentoOrderRepository,
        private readonly StoreManagerInterface $storeManager,
        private readonly LoggerInterface $logger,
        private ?UserContextInterface $userContext = null
    ) {
        $this->userContext = $this->userContext ?: ObjectManager::getInstance()->get(UserContextInterface::class);
    }

    /**
     * Create order from API request
     */
    public function create(OrderInterface $order)
    {
        try {
            // Create quote (shopping cart)
            $quote = $this->quoteFactory->create();
            $quote->setStore($this->storeManager->getStore());

            // Set customer info
            $quote->setCustomerEmail($order->getCustomerEmail());
            $quote->setCustomerFirstname($order->getCustomerFirstname());
            $quote->setCustomerLastname($order->getCustomerLastname());

            // Add items to quote
            $items = $order->getItems();
            if (!is_array($items) || empty($items)) {
                throw new InputException(__('No items provided in order'));
            }

            foreach ($items as $item) {
                if (is_object($item) && method_exists($item, 'getProductId')) {
                    $productId = $item->getProductId();
                    $qty = method_exists($item, 'getQty') ? $item->getQty() : 1;
                } else {
                    $productId = $item['product_id'] ?? null;
                    $qty = $item['qty'] ?? 1;
                }

                if (!$productId) {
                    throw new InputException(__('Product ID is required for each item'));
                }

                try {
                    $product = $this->productRepository->getById($productId);
                    $quote->addProduct($product, $qty);
                } catch (NoSuchEntityException $e) {
                    throw new NoSuchEntityException(
                        __('Product with ID %1 not found', $productId)
                    );
                }
            }

            // Set billing address
            $billingAddress = $order->getBillingAddress();
            if ($billingAddress && is_object($billingAddress) && method_exists($billingAddress, 'getFirstname')) {
                $quote->getBillingAddress()->addData($this->addressToArray($billingAddress));
            } elseif ($billingAddress && is_array($billingAddress)) {
                $quote->getBillingAddress()
                    ->addData($billingAddress);
            }

            // Set shipping address
            $shippingAddress = $order->getShippingAddress();
            if ($shippingAddress && is_object($shippingAddress) && method_exists($shippingAddress, 'getFirstname')) {
                $quote->getShippingAddress()->addData($this->addressToArray($shippingAddress));
            } elseif ($shippingAddress && is_array($shippingAddress)) {
                $quote->getShippingAddress()
                    ->addData($shippingAddress);
            }

            // Collect shipping rates
            $shippingMethod = $order->getShippingMethod() ?: 'bookstore_express_express';
            $quote->getShippingAddress()
                ->setCollectShippingRates(true)
                ->collectShippingRates()
                ->setShippingMethod($shippingMethod);

            // Set payment method
            $paymentMethod = $order->getPaymentMethod() ?? 'bookstore_vietqr';
            $quote->getPayment()->setMethod($paymentMethod);

            // Convert quote to order
            $magentoOrder = $this->quoteManagement->submit($quote);

            if (!$magentoOrder->getId()) {
                throw new InputException(__('Unable to create order'));
            }

            $this->logger->info('Order created successfully: Order ID = ' . $magentoOrder->getId());

            return $this->createOrderDataObject($magentoOrder);

        } catch (\Exception $e) {
            $this->logger->error('Error creating order: ' . $e->getMessage());
            throw new InputException(__('Failed to create order: ' . $e->getMessage()));
        }
    }

    /**
     * Get order by ID
     */
    public function get($orderId)
    {
        try {
            $magentoOrder = $this->magentoOrderRepository->get($orderId);
            $customerId = (int)$this->userContext->getUserId();
            $isCustomerToken = (int)$this->userContext->getUserType() === UserContextInterface::USER_TYPE_CUSTOMER;

            if (!$customerId || !$isCustomerToken || (int)$magentoOrder->getCustomerId() !== $customerId) {
                throw new NoSuchEntityException(__('Order #%1 not found', $orderId));
            }

            return $this->createOrderDataObject($magentoOrder);
        } catch (\Exception $e) {
            throw new NoSuchEntityException(__('Order #%1 not found', $orderId));
        }
    }

    /**
     * Convert Magento Order to OrderInterface
     */
    private function createOrderDataObject($magentoOrder)
    {
        $order = new Order();
        $order->setId($magentoOrder->getId())
            ->setIncrementId($magentoOrder->getIncrementId())
            ->setStatus($magentoOrder->getStatus())
            ->setState($magentoOrder->getState())
            ->setCustomerEmail($magentoOrder->getCustomerEmail())
            ->setCustomerFirstname($magentoOrder->getCustomerFirstname())
            ->setCustomerLastname($magentoOrder->getCustomerLastname())
            ->setGrandTotal($magentoOrder->getGrandTotal())
            ->setSubtotal($magentoOrder->getSubtotal())
            ->setShippingAmount($magentoOrder->getShippingAmount())
            ->setTaxAmount($magentoOrder->getTaxAmount())
            ->setCreatedAt($magentoOrder->getCreatedAt());

        if ($magentoOrder->getBillingAddress()) {
            $order->setBillingAddress($this->mapAddress($magentoOrder->getBillingAddress()));
        }

        if ($magentoOrder->getShippingAddress()) {
            $order->setShippingAddress($this->mapAddress($magentoOrder->getShippingAddress()));
        }

        $items = [];
        foreach ($magentoOrder->getAllVisibleItems() as $item) {
            $items[] = (new OrderItem())
                ->setProductId((int)$item->getProductId())
                ->setSku((string)$item->getSku())
                ->setName((string)$item->getName())
                ->setQty((float)$item->getQtyOrdered())
                ->setPrice((float)$item->getPrice())
                ->setRowTotal((float)$item->getRowTotal());
        }

        $order->setItems($items)
            ->setPaymentMethod($magentoOrder->getPayment() ? (string)$magentoOrder->getPayment()->getMethod() : null)
            ->setShippingMethod($magentoOrder->getShippingMethod());

        return $order;
    }

    private function mapAddress($address): Address
    {
        return (new Address())
            ->setFirstname((string)$address->getFirstname())
            ->setLastname((string)$address->getLastname())
            ->setStreet($address->getStreet())
            ->setCity((string)$address->getCity())
            ->setRegion((string)$address->getRegion())
            ->setPostcode((string)$address->getPostcode())
            ->setCountryId((string)$address->getCountryId())
            ->setTelephone((string)$address->getTelephone());
    }

    private function addressToArray($address): array
    {
        return [
            'firstname' => (string)$address->getFirstname(),
            'lastname' => (string)$address->getLastname(),
            'street' => $address->getStreet(),
            'city' => (string)$address->getCity(),
            'region' => (string)$address->getRegion(),
            'postcode' => (string)$address->getPostcode(),
            'country_id' => (string)$address->getCountryId(),
            'telephone' => (string)$address->getTelephone(),
        ];
    }
}
