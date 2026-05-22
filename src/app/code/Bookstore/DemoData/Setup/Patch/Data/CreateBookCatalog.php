<?php

namespace Bookstore\DemoData\Setup\Patch\Data;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Store\Model\Store;

class CreateBookCatalog implements DataPatchInterface
{
    private array $categories = [
        'Van hoc Viet Nam', 'Van hoc nuoc ngoai', 'Kinh doanh', 'Ky nang song', 'Thieu nhi',
        'Giao trinh', 'Cong nghe thong tin', 'Ngoai ngu', 'Lich su', 'Khoa hoc thuong thuc',
    ];

    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly CategoryFactory $categoryFactory,
        private readonly CategoryRepositoryInterface $categoryRepository,
        private readonly CategoryCollectionFactory $categoryCollectionFactory,
        private readonly ProductFactory $productFactory,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly SearchCriteriaBuilder $searchCriteriaBuilder,
        private readonly StockRegistryInterface $stockRegistry,
        private readonly EavSetupFactory $eavSetupFactory,
        private readonly DirectoryList $directoryList,
        private readonly State $state
    ) {
    }

    public function apply()
    {
        try {
            $this->state->setAreaCode('adminhtml');
        } catch (LocalizedException) {
        }

        $this->moduleDataSetup->getConnection()->startSetup();
        $attributeSetId = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup])
            ->getAttributeSetId('catalog_product', 'Default');
        $image = $this->createDemoImage();

        foreach ($this->categories as $index => $categoryName) {
            $categoryId = $this->ensureCategory($categoryName);
            for ($i = 1; $i <= 10; $i++) {
                $sku = sprintf('BOOK-%02d-%02d', $index + 1, $i);
                if ($this->productExists($sku)) {
                    continue;
                }

                $product = $this->productFactory->create();
                $product->setSku($sku);
                $product->setName($categoryName . ' - Sach ' . $i);
                $product->setAttributeSetId($attributeSetId);
                $product->setStatus(Status::STATUS_ENABLED);
                $product->setVisibility(Visibility::VISIBILITY_BOTH);
                $product->setTypeId('simple');
                $product->setWebsiteIds([1]);
                $product->setStoreId(Store::DEFAULT_STORE_ID);
                $product->setPrice(45000 + ($index * 5000) + ($i * 1000));
                $product->setDescription('Sach demo cho danh muc ' . $categoryName . '. Du lieu dung de trinh bay he thong Magento bookstore.');
                $product->setShortDescription('Sach demo ' . $sku);
                $product->setCategoryIds([$categoryId]);
                $product->setStockData(['use_config_manage_stock' => 1, 'qty' => 50, 'is_qty_decimal' => 0, 'is_in_stock' => 1]);
                $product->addImageToMediaGallery($image, ['image', 'small_image', 'thumbnail'], false, false);
                $saved = $this->productRepository->save($product);

                $stockItem = $this->stockRegistry->getStockItem((int)$saved->getId());
                $stockItem->setQty(50);
                $stockItem->setIsInStock(true);
                $this->stockRegistry->updateStockItemBySku($sku, $stockItem);
            }
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    private function ensureCategory(string $name): int
    {
        $collection = $this->categoryCollectionFactory->create();
        $collection->addAttributeToSelect('name')
            ->addAttributeToFilter('name', $name)
            ->setPageSize(1);
        $existing = $collection->getFirstItem();
        if ($existing->getId()) {
            return (int)$existing->getId();
        }

        $category = $this->categoryFactory->create();
        $category->setName($name);
        $category->setParentId(2);
        $category->setIsActive(true);
        $category->setIncludeInMenu(true);
        return (int)$this->categoryRepository->save($category)->getId();
    }

    private function productExists(string $sku): bool
    {
        try {
            $this->productRepository->get($sku);
            return true;
        } catch (NoSuchEntityException) {
            return false;
        }
    }

    private function createDemoImage(): string
    {
        $dir = $this->directoryList->getRoot() . '/pub/media/import';
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $path = $dir . '/bookstore-demo-cover.png';
        if (!is_file($path)) {
            if (function_exists('imagecreatetruecolor')) {
                $image = imagecreatetruecolor(300, 400);
                $blue = imagecolorallocate($image, 35, 75, 110);
                $white = imagecolorallocate($image, 245, 248, 250);
                imagefilledrectangle($image, 0, 0, 299, 399, $blue);
                imagefilledrectangle($image, 28, 28, 271, 371, $white);
                imagefilledrectangle($image, 42, 42, 257, 357, imagecolorallocate($image, 51, 111, 150));
                imagestring($image, 5, 86, 175, 'BOOKSTORE', $white);
                imagestring($image, 4, 105, 205, 'DEMO', $white);
                imagepng($image, $path);
                imagedestroy($image);
            }
        }
        return $path;
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }
}
