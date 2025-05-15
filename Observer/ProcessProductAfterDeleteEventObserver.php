<?php
declare(strict_types=1);

namespace Convertcart\Analytics\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\ResourceConnection;
use Convertcart\Analytics\Model\SyncFactory;

class ProcessProductAfterDeleteEventObserver implements ObserverInterface
{

    /**
     * @var \Convertcart\Analytics\Model\SyncFactory
     */
    protected $_deletedProduct;

    /**
     * @var \Convertcart\Analytics\Logger\Logger
     */
    protected $_logger;

    public function __construct(
        \Convertcart\Analytics\Model\SyncFactory $deletedProduct,
        \Convertcart\Analytics\Logger\Logger $_logger
    ) {
        $this->_logger = $_logger;
        $this->_deletedProduct = $deletedProduct;
    }

    /**
     * Execute observer for product after delete event.
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer): void
    {
        try {
            $eventProduct = $observer->getEvent()->getProduct();
            $model = $this->_deletedProduct->create();
            $model->addData(["item_id" => $eventProduct->getId()]);
            $model->addData(["type" => "product"]);
            $saveData = $model->save();
        } catch (\Exception $e) {
            $this->_logger->error($e->getMessage());
        }
    }
}
