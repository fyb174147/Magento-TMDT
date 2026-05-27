<?php

namespace Bookstore\Sales\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;

class OrderPlacedAfter implements ObserverInterface
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly StockRegistryInterface $stockRegistry,
        private readonly LoggerInterface $logger
    ) {
    }

    public function execute(Observer $observer)
    {
        try {
            /** @var Order $order */
            $order = $observer->getEvent()->getOrder();

            if (!$order->getId()) {
                return;
            }

            $this->logger->info('Order placed: #' . $order->getIncrementId());

            // Trừ kho cho từng sản phẩm trong đơn hàng
            foreach ($order->getItems() as $item) {
                $this->deductInventory($item);
            }

            // Cập nhật trạng thái đơn hàng sang "Pending"
            $order->setState(Order::STATE_PENDING_PAYMENT)
                  ->setStatus(Order::STATE_PENDING_PAYMENT);

            $this->orderRepository->save($order);

            $this->logger->info('Order #' . $order->getIncrementId() . ' inventory updated and status set to Pending');

        } catch (\Exception $e) {
            $this->logger->error('Error processing order: ' . $e->getMessage());
        }
    }

    /**
     * Deduct inventory for order item
     */
    private function deductInventory($item): void
    {
        try {
            $productId = $item->getProductId();
            $qty = $item->getQtyOrdered();

            // Get current stock
            $stock = $this->stockRegistry->getStockItem($productId);

            if ($stock && $stock->getQty() >= $qty) {
                // Deduct quantity
                $newQty = $stock->getQty() - $qty;
                $stock->setQty($newQty);
                $stock->setIsInStock($newQty > 0);

                // Update back to registry
                $this->stockRegistry->updateStockItemBySku($item->getSku(), $stock);

                $this->logger->info('Inventory deducted for SKU: ' . $item->getSku() . ', Qty: ' . $qty);
            } else {
                $this->logger->warning('Insufficient stock for SKU: ' . $item->getSku());
            }
        } catch (\Exception $e) {
            $this->logger->error('Error deducting inventory: ' . $e->getMessage());
        }
    }
}
