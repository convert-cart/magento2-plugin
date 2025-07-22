<?php
declare(strict_types=1);

namespace Convertcart\Analytics\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Convertcart\Analytics\Logger\Logger;
use Convertcart\Analytics\Model\Cc;

abstract class AbstractObserver implements ObserverInterface
{
    protected Logger $logger;
    protected Cc $ccModel;

    public function __construct(
        Logger $logger,
        Cc $ccModel
    ) {
        $this->logger = $logger;
        $this->ccModel = $ccModel;
    }

    public function execute(Observer $observer): void
    {
        try {
            $this->executeInternal($observer);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    abstract protected function executeInternal(Observer $observer): void;
}
