<?php

namespace Bookstore\CoreApi\Model;

use Bookstore\CoreApi\Api\CatalogInterface;
use Magento\Catalog\Api\CategoryManagementInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Store\Model\StoreManagerInterface;
use Bookstore\CoreApi\Model\Data\Category as CategoryData;
use Bookstore\CoreApi\Model\Data\ProductList;
use Bookstore\CoreApi\Model\Data\ProductSummary;

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
            $items[] = (new ProductSummary())
                ->setId((int)$product->getId())
                ->setSku((string)$product->getSku())
                ->setName((string)$product->getName())
                ->setPrice((float)$product->getPrice())
                ->setType((string)$product->getTypeId())
                ->setUrlKey((string)$product->getUrlKey())
                ->setImage($image && $image !== 'no_selection' ? $mediaUrl . 'catalog/product' . $image : null);
        }

        return (new ProductList())->setItems($items)->setTotal(count($items));
    }

    public function getCategories()
    {
        return $this->mapCategory($this->categoryManagement->getTree());
    }

    private function mapCategory($category): CategoryData
    {
        $children = [];
        foreach ($category->getChildrenData() as $child) {
            $children[] = $this->mapCategory($child);
        }

        return (new CategoryData())
            ->setId((int)$category->getId())
            ->setName((string)$category->getName())
            ->setIsActive((bool)$category->getIsActive())
            ->setChildren($children);
    }
}
