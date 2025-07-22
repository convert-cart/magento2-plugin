<?php
declare(strict_types=1);
namespace Convertcart\Analytics\Model\Api;

use Convertcart\Analytics\Model\SyncFactory;
use Convertcart\Analytics\Logger\Logger;

class SyncApi implements \Convertcart\Analytics\Api\SyncRepositoryInterface
{
    private Logger $logger;
    private SyncFactory $deletedProduct;

    public function __construct(
        Logger $logger,
        SyncFactory $deletedProduct
    ) {
        $this->logger = $logger;
        $this->deletedProduct = $deletedProduct;
    }

    public function getDeletedProduct(int $limit, ?int $id, string $type): ?array
    {
        try {
            $model = $this->deletedProduct->create()->getCollection();

            $model->addFieldToFilter("type", ["eq" => $type]);
            if ($id) {
                $model->addFieldToFilter("id", ['gt' => $id]);
            }
            $model->setPageSize($limit);
            return $model->getData();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            return null;
        }
    }
}
