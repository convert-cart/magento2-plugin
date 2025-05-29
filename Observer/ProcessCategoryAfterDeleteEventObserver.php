<?php
declare(strict_types=1);

namespace Convertcart\Analytics\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\ResourceConnection;
use Convertcart\Analytics\Model\SyncFactory;

class ProcessCategoryAfterDeleteEventObserver implements ObserverInterface
{

    /**
     * @var \Convertcart\Analytics\Model\SyncFactory
     */
    protected $_deletedCategory;

    /**
     * @var \Convertcart\Analytics\Logger\Logger
     */
    protected $_logger;

    public function __construct(
        \Convertcart\Analytics\Model\SyncFactory $deletedCategory,
        \Convertcart\Analytics\Logger\Logger $_logger
    ) {
        $this->_logger = $_logger;
        $this->_deletedCategory = $deletedCategory;
    }

    /**
     * Execute observer for category after delete event.
     *
     * @param  \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer): void
    {
        try {
            $eventCategory = $observer->getEvent()->getCategory();
            $model = $this->_deletedCategory->create();
            $model->addData(["item_id" => $eventCategory->getId()]);
            $model->addData(["type" => "category"]);
            $saveData = $model->save();
        } catch (\Exception $e) {
            $this->_logger->error($e->getMessage());
        }
    }
}
