<?php
declare(strict_types=1);

namespace Convertcart\Analytics\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\View\LayoutInterface;
use Convertcart\Analytics\Logger\Logger;
use Convertcart\Analytics\Helper\Data;
use Convertcart\Analytics\Model\Cc;

class AddScript implements ObserverInterface
{
    private LayoutInterface $layout;
    private Logger $logger;
    private Data $dataHelper;
    private Cc $ccModel;

    public function __construct(
        LayoutInterface $layout,
        Logger $logger,
        Data $dataHelper,
        Cc $ccModel
    ) {
        $this->dataHelper = $dataHelper;
        $this->logger = $logger;
        $this->layout = $layout;
        $this->ccModel = $ccModel;
    }

    public function execute(Observer $observer): void
    {
        try {
            $initScript = $this->ccModel->getInitScript();
            if (empty($initScript)) {
                return;
            }

            $layout = $this->layout;
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
            $this->logger->error($e->getMessage());
        }
    }

    private function attachEvents($head): void
    {
        if (!is_object($head)) {
            return;
        }
        $ccEvents = $this->ccModel->fetchCcEvents();
        if (empty($ccEvents) || !is_array($ccEvents)) {
            return;
        }
        foreach ($ccEvents as $ccEvent) {
            $eventBlock = $this->ccModel->getEventScript($ccEvent);
            $head->append($eventBlock);
        }
    }
}
