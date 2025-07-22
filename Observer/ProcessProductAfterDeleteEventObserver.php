<?php
declare(strict_types=1);

namespace Convertcart\Analytics\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Convertcart\Analytics\Model\SyncFactory;
use Convertcart\Analytics\Logger\Logger;

class ProcessProductAfterDeleteEventObserver implements ObserverInterface
{
    private SyncFactory $deletedProduct;
    private Logger $logger;

    public function __construct(
        SyncFactory $deletedProduct,
        Logger $logger
    ) {
        $this->logger = $logger;
        $this->deletedProduct = $deletedProduct;
    }

    public function execute(Observer $observer): void
    {
        try {
            $eventProduct = $observer->getEvent()->getProduct();
            $model = $this->deletedProduct->create();
            $model->addData(["item_id" => $eventProduct->getId()]);
            $model->addData(["type" => "product"]);
            $model->save();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
