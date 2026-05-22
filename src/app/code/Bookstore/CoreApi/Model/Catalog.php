<?php

namespace Bookstore\CoreApi\Model;

use Bookstore\CoreApi\Api\CatalogInterface;
use Magento\Catalog\Api\CategoryManagementInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Store\Model\StoreManagerInterface;

class Catalog implements CatalogInterface
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly CategoryManagementInterface $categoryManagement,
        private readonly SearchCriteriaBuilder $searchCriteriaBuilder,
        private readonly StoreManagerInterface $storeManager
    ) {
    }

    public function getProducts()
    {
        $criteria = $this->searchCriteriaBuilder
            ->addFilter('status', 1)
            ->setPageSize(100)
            ->create();
        $items = [];
        $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

        foreach ($this->productRepository->getList($criteria)->getItems() as $product) {
            $image = (string)$product->getData('image');
            $items[] = [
                'id' => (int)$product->getId(),
                'sku' => (string)$product->getSku(),
                'name' => (string)$product->getName(),
                'price' => (float)$product->getPrice(),
                'type' => (string)$product->getTypeId(),
                'url_key' => (string)$product->getUrlKey(),
                'image' => $image && $image !== 'no_selection' ? $mediaUrl . 'catalog/product' . $image : null,
            ];
        }

        return ['items' => $items, 'total' => count($items)];
    }

    public function getCategories()
    {
        return ['items' => $this->mapCategory($this->categoryManagement->getTree())];
    }

    private function mapCategory($category): array
    {
        $children = [];
        foreach ($category->getChildrenData() as $child) {
            $children[] = $this->mapCategory($child);
        }

        return [
            'id' => (int)$category->getId(),
            'name' => (string)$category->getName(),
            'is_active' => (bool)$category->getIsActive(),
            'children' => $children,
        ];
    }
}
