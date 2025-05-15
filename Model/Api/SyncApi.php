<?php
declare(strict_types=1);

namespace Convertcart\Analytics\Model\Api;

use Convertcart\Analytics\Model\SyncFactory;
use Convertcart\Analytics\Model\ResourceModel\Sync\Collection;

class SyncApi implements \Convertcart\Analytics\Api\SyncRepositoryInterface
{

    /**
     * @var \Convertcart\Analytics\Logger\Logger
     */
    protected $_logger;

    /**
     * @var \Convertcart\Analytics\Model\SyncFactory
     */
    protected $_deletedProduct;

    public function __construct(
        \Convertcart\Analytics\Logger\Logger $_logger,
        \Convertcart\Analytics\Model\SyncFactory $deletedProduct
    ) {
        $this->_logger = $_logger;
        $this->_deletedProduct = $deletedProduct;
    }

    /**
     * Deleted product
     *
     * @inheriDoc
     *
     * @param int $limit
     * @param int $id
     * @param string $type
     */
    /**
     * Get deleted product data.
     * @param int $limit
     * @param int|null $id
     * @param string $type
     * @return array|null
     */
    public function getDeletedProduct(int $limit, ?int $id, string $type): ?array
    {
        try {
            // to delete the previous synced data ($id is last sync id);
            $model = $this->_deletedProduct->create()->getCollection();

            // Filter by type
            $model->addFieldToFilter("type", ["eq" => $type]);
            if ($id) {
                // If an ID is provided, fetch only records greater than this ID
                $model->addFieldToFilter("id", ['gt' => $id]);
            }
            // Limit the number of records
            $model->setPageSize($limit);

            return $model->getData();
        } catch (\Exception $e) {
            $this->_logger->error($e->getMessage());
        }
    }
}
