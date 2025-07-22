<?php
declare(strict_types=1);

namespace Convertcart\Analytics\Model\Api;

use Convertcart\Analytics\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface as MagentoCustomerRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;

class CustomerRepository implements CustomerRepositoryInterface
{
    private MagentoCustomerRepository $customerRepository;
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    public function __construct(
        MagentoCustomerRepository $customerRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->customerRepository = $customerRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    public function getCustomers(int $limit = 100, int $page = 1): array
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->setPageSize($limit)
            ->setCurrentPage($page)
            ->create();

        $customers = $this->customerRepository->getList($searchCriteria);
        $result = [];

        foreach ($customers->getItems() as $customer) {
            $result[] = [
                'id' => $customer->getId(),
                'email' => $customer->getEmail(),
                'first_name' => $customer->getFirstname(),
                'last_name' => $customer->getLastname(),
                'created_at' => $customer->getCreatedAt(),
                'updated_at' => $customer->getUpdatedAt()
            ];
        }

        return [
            'customers' => $result,
            'total_count' => $customers->getTotalCount(),
            'page' => $page,
            'limit' => $limit
        ];
    }
}
