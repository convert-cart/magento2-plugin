<?php
declare(strict_types=1);

namespace Convertcart\Analytics\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Convertcart\Analytics\Model\SyncFactory;
use Convertcart\Analytics\Logger\Logger;

class ProcessCategoryAfterDeleteEventObserver implements ObserverInterface
{
    private SyncFactory $deletedCategory;
    private Logger $logger;

    public function __construct(
        SyncFactory $deletedCategory,
        Logger $logger
    ) {
        $this->logger = $logger;
        $this->deletedCategory = $deletedCategory;
    }

    public function execute(Observer $observer): void
    {
        try {
            $eventCategory = $observer->getEvent()->getCategory();
            $model = $this->deletedCategory->create();
            $model->addData(["item_id" => $eventCategory->getId()]);
            $model->addData(["type" => "category"]);
            $model->save();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
