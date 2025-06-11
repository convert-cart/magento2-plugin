<?php
declare(strict_types=1);

namespace Convertcart\Analytics\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\View\LayoutInterface;

class AddScript implements ObserverInterface
{
    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $_layout;

    /**
     * @var \Convertcart\Analytics\Logger\Logger
     */
    protected $_logger;

    /**
     * @var \Convertcart\Analytics\Helper\Data
     */
    protected $_dataHelper;

    public function __construct(
        LayoutInterface $_layout,
        \Convertcart\Analytics\Logger\Logger $_logger,
        \Convertcart\Analytics\Helper\Data $_dataHelper
    ) {
        $this->_dataHelper = $_dataHelper;
        $this->_logger = $_logger;
        $this->_layout = $_layout;
    }

    /**
     * Lazy-load the Cc model instance.
     *
     * @return \Convertcart\Analytics\Model\Cc
     */
    /**
     * @var \Convertcart\Analytics\Model\Cc|null
     */
    protected $_ccModel = null;

    /**
     * Lazy-load the Cc model instance.
     *
     * @return \Convertcart\Analytics\Model\Cc
     */
    protected function getCcModel()
    {
        if ($this->_ccModel === null) {
            $this->_ccModel = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Convertcart\Analytics\Model\Cc::class);
        }
        return $this->_ccModel;
    }

    /**
     * Execute observer to add init script and events.
     *
     * @param  \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer): void
    {
        try {
            $initScript = $this->getCcModel()->getInitScript();
            if (empty($initScript)) {
                return;
            }

            $layout = $this->_layout;
            if (!is_object($layout)) {
                return;
            }

            $head = $layout->getBlock('head.additional');
            if (!is_object($head)) {
                return;
            }
            $head->append($initScript);
            $this->attachEvents($head);
        } catch (\Exception $e) {
            $this->_logger->error($e->getMessage());
        }
    }

    /**
     * Attach event scripts to the head block.
     *
     * @param  \Magento\Framework\View\Element\AbstractBlock|null $head
     * @return void
     */
    private function attachEvents($head): void
    {
        if (!is_object($head)) {
            return;
        }
        $ccEvents = $this->getCcModel()->fetchCcEvents();
        if (empty($ccEvents) || !is_array($ccEvents)) {
            return;
        }
        foreach ($ccEvents as $ccEvent) {
            $eventBlock = $this->getCcModel()->getEventScript($ccEvent);
            $head->append($eventBlock);
        }
    }
}
