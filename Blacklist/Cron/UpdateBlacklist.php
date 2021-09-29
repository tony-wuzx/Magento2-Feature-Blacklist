<?php

declare(strict_types=1);

namespace Zhixing\Blacklist\Cron;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Psr\Log\LoggerInterface;
use Zhixing\Blacklist\Model\Api\Mapping;

/**
 * Class UpdateBlacklist
 *
 * @package Zhixing\Blacklist\Cron
 */
class UpdateBlacklist
{
    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var SearchCriteriaInterface
     */
    protected $searchCriteria;

    /**
     * @var Mapping
     */
    protected $mapping;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * UpdateBlacklist constructor.
     *
     * @param CustomerRepositoryInterface $customerRepository
     * @param SearchCriteriaInterface $searchCriteria
     * @param Mapping $mapping
     * @param LoggerInterface $logger
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        SearchCriteriaInterface $searchCriteria,
        Mapping $mapping,
        LoggerInterface $logger
    ) {
        $this->customerRepository = $customerRepository;
        $this->searchCriteria = $searchCriteria;
        $this->mapping = $mapping;
        $this->logger = $logger;
    }

    public function execute()
    {
        $values = [];
        $result = $this->customerRepository->getList($this->searchCriteria);
        foreach($result->getItems() as $customer) {

            $code = $customer->getCustomAttribute('register_device') ? $customer->getCustomAttribute('register_device')->getValue() : null;
            $name = $customer->getCustomAttribute('register_id') ? $customer->getCustomAttribute('register_id')->getValue() : null;

            if (empty($code) || empty ($name)) continue;

            $values[] = [
                'type' => 'device',
                'code' => $code,
                'name' => $name,
                'map' => $customer->getId(),
                'remarks' => $customer->getFirstname() . $customer->getLastname(),
            ];
        }

        if (count($values)) {
            $this->mapping->set($values);
        }
    }
}
