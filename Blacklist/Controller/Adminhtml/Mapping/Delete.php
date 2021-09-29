<?php

namespace Zhixing\Blacklist\Controller\Adminhtml\Mapping;

use Magento\Backend\App\Action;

/**
 * Class Delete
 * @package Zhixing\Blacklist\Controller\Adminhtml\Mapping
 */
class Delete extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Zhixing_Blacklist::core';

    /**
     * @var \Zhixing\Blacklist\Model\MappingFactory
     */
    protected $_mappingFactory;

    /**
     * Delete constructor.
     * @param Action\Context $context
     * @param \Zhixing\Blacklist\Model\MappingFactory $mappingFactory
     */
    public function __construct(
        Action\Context $context,
        \Zhixing\Blacklist\Model\MappingFactory $mappingFactory
    ) {
        $this->_mappingFactory = $mappingFactory;
        parent::__construct($context);
    }

    /**
     * Delete Mapping action
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            try {
                $mapping = $this->_mappingFactory->create();
                $mapping->getResource()->load($mapping, $id);
                $mapping->getResource()->delete($mapping);

                $this->messageManager->addSuccessMessage(__('The mapping has been deleted.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
            }
        }
        $this->messageManager->addErrorMessage(__('We can\'t find a customer to delete.'));
        return $resultRedirect->setPath('*/*/');
    }
}
