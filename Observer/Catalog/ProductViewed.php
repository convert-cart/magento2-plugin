<?php
declare(strict_types=1);

namespace Convertcart\Analytics\Observer\Catalog;

use Magento\Framework\Event\Observer;
use Magento\Framework\Registry;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\ConfigurableProduct\Model\Product\Type\ConfigurableFactory;
use Convertcart\Analytics\Observer\AbstractObserver;
use Convertcart\Analytics\Logger\Logger;
use Convertcart\Analytics\Model\Cc;

class ProductViewed extends AbstractObserver
{
    private Registry $registry;
    private StoreManagerInterface $storeManager;
    private StockRegistryInterface $stockItemRepository;
    private ConfigurableFactory $configurableProductProductTypeConfigurableFactory;

    public function __construct(
        Logger $logger,
        Cc $ccModel,
        Registry $registry,
        StockRegistryInterface $stockItemRepository,
        StoreManagerInterface $storeManager,
        ConfigurableFactory $configurableProductProductTypeConfigurableFactory
    ) {
        parent::__construct($logger, $ccModel);
        $this->registry = $registry;
        $this->stockItemRepository = $stockItemRepository;
        $this->storeManager = $storeManager;
        $this->configurableProductProductTypeConfigurableFactory = $configurableProductProductTypeConfigurableFactory;
    }

    protected function executeInternal(Observer $observer): void
    {
        $eventName = 'productViewed';
        $eventData = [];
        $product = $this->registry->registry('current_product');
        if (is_object($product)) {
            $eventData = [
                'id' => $product->getId(),
                'url' => $product->getProductUrl(),
                'name' => $product->getName(),
                'price' => $product->getPrice(),
                'final_price' => $product->getFinalPrice(),
                'sku' => $product->getSku(),
                'type' => $product->getTypeId()
            ];

            $store = $this->storeManager->getStore();
            $eventData['currency'] = is_object($store) ? $store->getCurrentCurrencyCode() : null;
            if (!empty($product->getImage())) {
                $eventData['image'] = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
                $eventData['image'].= 'catalog/product'.$product->getImage();
            } else {
                $eventData['image'] = null;
            }
            $stock = $this->stockItemRepository->getStockItem($product->getId());
            $eventData['is_in_stock'] = is_object($stock) ? $stock->getIsInStock() : null;
            if ($eventData['type'] == "configurable") {
                $eventData['product_type'] = "parent";
                $childProducts = $this->configurableProductProductTypeConfigurableFactory->create()
                    ->getChildrenIds($product->getId());
                $eventData['child_ids'] = $childProducts[0];
            } else {
                $eventData['product_type'] = "simple";
            }
        }
        $this->ccModel->storeCcEvents($eventName, $eventData);
    }
}
