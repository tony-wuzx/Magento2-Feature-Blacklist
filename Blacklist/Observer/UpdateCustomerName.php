<?php
namespace Zhixing\Blacklist\Observer;

use Zhixing\Blacklist\Model\Mapping as MappingModel;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class UpdateCustomerName
 *
 * @package Zhixing\Blacklist\Observer
 */
class UpdateCustomerName implements ObserverInterface
{
    /**
     * @var MappingModel
     */
    protected $mapping;

    /**
     * UpdateCustomerName constructor.
     *
     * @param MappingModel $mapping
     */
    public function __construct(
        MappingModel $mapping
    ) {
        $this->mapping = $mapping;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Customer\Api\Data\CustomerInterface $customer */
        $customer = $observer->getEvent()->getCustomerDataObject();
        try {
            $customerId = $customer->getId();
            $items = $this->mapping->getCollection()->addFieldToFilter('map', $customerId)->getItems();
            if ($items) {
                $customerName = $customer->getFirstname() . $customer->getLastname();
                foreach ($items as $item) {
                    if ($item->getData('remarks') != $customerName) {
                        $item->setData('remarks', $customerName);
                        $item->save();
                    }
                }
            }
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
        }
    }
}
