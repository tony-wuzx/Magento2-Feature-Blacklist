<?php

namespace Zhixing\Blacklist\Controller\Adminhtml\Mapping;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Registry;
use Zhixing\Blacklist\Model\MappingFactory;

/**
 * Class Edit
 * @package Zhixing\Blacklist\Controller\Adminhtml\Mapping
 */
class Edit extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Zhixing_Blacklist::core';

    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var MappingFactory
     */
    protected $_mappingFactory;

    /**
     * Edit constructor.
     * @param Action\Context $context
     * @param PageFactory $resultPageFactory
     * @param Registry $registry
     * @param MappingFactory $mappingFactory
     */
    public function __construct(
        Action\Context $context,
        PageFactory $resultPageFactory,
        Registry $registry,
        MappingFactory $mappingFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        $this->_mappingFactory = $mappingFactory;
        parent::__construct($context);
    }

    /**
     * Init actions
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Blacklist::Core');
        $resultPage->addBreadcrumb(__('Blacklist'), __('Blacklist Edit'));
        $resultPage->getConfig()->getTitle()->prepend(__('Blacklist Edit'));
        return $resultPage;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $mapping = $this->_mappingFactory->create();

        if ($id) {
            $mapping->getResource()->load($mapping, $id);
            if (!$mapping->getId()) {
                $this->messageManager->addErrorMessage(__('This Blacklist no longer exists.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }

        $this->_coreRegistry->register('mapping', $mapping);

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_initAction();
        $resultPage->addBreadcrumb(
            $id ? __('Edit Blacklist') : __('New Blacklist'),
            $id ? __('Edit Blacklist') : __('New Blacklist')
        );
        $resultPage->getConfig()->getTitle()->prepend($mapping->getId() ? __('Edit Blacklist') : __('New Blacklist'));

        return $resultPage;
    }
}
