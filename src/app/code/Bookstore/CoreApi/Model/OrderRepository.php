<?php

namespace Bookstore\CoreApi\Model;

use Bookstore\CoreApi\Api\OrderRepositoryInterface;
use Bookstore\CoreApi\Api\Data\OrderInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\QuoteManagement;
use Magento\Sales\Api\OrderRepositoryInterface as MagentoOrderRepository;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class OrderRepository implements OrderRepositoryInterface
{
    public function __construct(
        private readonly QuoteFactory $quoteFactory,
        private readonly QuoteManagement $quoteManagement,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly MagentoOrderRepository $magentoOrderRepository,
        private readonly StoreManagerInterface $storeManager,
        private readonly LoggerInterface $logger
    ) {
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
                $productId = $item['product_id'] ?? null;
                $qty = $item['qty'] ?? 1;

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
            if ($billingAddress && is_array($billingAddress)) {
                $quote->getBillingAddress()
                    ->addData($billingAddress);
            }

            // Set shipping address
            $shippingAddress = $order->getShippingAddress();
            if ($shippingAddress && is_array($shippingAddress)) {
                $quote->getShippingAddress()
                    ->addData($shippingAddress);
            }

            // Set shipping method
            $shippingMethod = $order->getShippingMethod() ?? 'bookstore_express_express';
            $quote->getShippingAddress()->setShippingMethod($shippingMethod);

            // Collect shipping rates
            $quote->getShippingAddress()->collectShippingRates()
                ->setCollectShippingRates(true);

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

        return $order;
    }
}
