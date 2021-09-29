<?php

namespace Zhixing\Blacklist\Controller\Adminhtml\Mapping;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Zhixing\Blacklist\Model\ResourceModel\Mapping\CollectionFactory;
use Zhixing\Blacklist\Model\ResourceModel\Mapping;

/**
 * Class MassDelete
 * @package Zhixing\Blacklist\Controller\Adminhtml\Mapping
 */
class MassDelete extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Zhixing_Blacklist::core';
    /**
     * Massactions filter
     *
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var Mapping
     */
    private $mappingResource;

    /**
     * MassDelete constructor.
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param Mapping $mappingResource
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        Mapping $mappingResource
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->mappingResource = $mappingResource;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $mappingDeleted = 0;
        /** @var \Zhixing\Blacklist\Model\Mapping $mapping */
        foreach ($collection->getItems() as $mapping) {
            $this->mappingResource->delete($mapping);
            $mappingDeleted++;
        }

        if ($mappingDeleted) {
            $this->messageManager->addSuccessMessage(
                __('A total of %1 record(s) have been deleted.', $mappingDeleted)
            );
        }

        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('*/*/index');
    }
}
