<?php
declare(strict_types=1);

namespace Convertcart\Analytics\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;

class HomepageViewed implements ObserverInterface
{
    /**
     * @var \Convertcart\Analytics\Logger\Logger
     */
    protected $_logger;

    /**
     * @var \Convertcart\Analytics\Model\Cc
     */
    protected $_ccModel;

    public function __construct(
        \Convertcart\Analytics\Logger\Logger $_logger,
        \Convertcart\Analytics\Model\Cc $_ccModel
    ) {
        $this->_logger = $_logger;
        $this->_ccModel = $_ccModel;
    }

    /**
     * Execute observer for homepage viewed event.
     *
     * @param  \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer): void
    {
        try {
            $eventName = 'homepageViewed';
            $eventData= [];
            $this->_ccModel->storeCcEvents($eventName, $eventData);
        } catch (\Exception $e) {
            $this->_logger->error($e->getMessage());
        }
    }
}
