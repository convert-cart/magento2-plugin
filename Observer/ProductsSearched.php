<?php
declare(strict_types=1);

namespace Convertcart\Analytics\Observer;

use Magento\Framework\Event\Observer;
use Convertcart\Analytics\Helper\Data;
use Convertcart\Analytics\Logger\Logger;
use Convertcart\Analytics\Model\Cc;

class ProductsSearched extends AbstractObserver
{
    private Data $dataHelper;

    public function __construct(
        Logger $logger,
        Cc $ccModel,
        Data $dataHelper
    ) {
        parent::__construct($logger, $ccModel);
        $this->dataHelper = $dataHelper;
    }

    protected function executeInternal(Observer $observer): void
    {
        $eventName = 'productsSearched';
        $eventData = [];
        $query = $observer->getDataObject();
        if (is_object($query)) {
            $eventData['query'] = $this->dataHelper->sanitizeParam($query->getQueryText());
        }
        $this->ccModel->storeCcEvents($eventName, $eventData);
    }
}
