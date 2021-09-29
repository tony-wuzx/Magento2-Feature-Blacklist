<?php

namespace Zhixing\Blacklist\Controller\Adminhtml\Mapping;

use Magento\Backend\App\Action;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Zhixing\Blacklist\Model\ResourceModel\Mapping;
use Zhixing\Blacklist\Model\MappingFactory;

/**
 * Class Save
 * @package Zhixing\Blacklist\Controller\Adminhtml\Mapping
 */
class Save extends Action
{
    const SPECIAL_CHARACTERS = '/\\<>\'":*$#@()!,.?`=%&^';

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Zhixing_Blacklist::core';

    /**
     * @var string[]
     */
    protected $safeFields = ['name', 'map', 'code', 'status'];

    /**
     * @var string[]
     */
    private $replaceSymbols;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var MappingFactory
     */
    protected $_mappingFactory;

    /**
     * @var Mapping
     */
    protected $_mappingResource;

    /**
     * Save constructor.
     * @param Action\Context $context
     * @param DataPersistorInterface $dataPersistor
     * @param MappingFactory $mappingFactory
     * @param Mapping $mappingResource
     */
    public function __construct(
        Action\Context $context,
        DataPersistorInterface $dataPersistor,
        MappingFactory $mappingFactory,
        Mapping $mappingResource
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->_mappingFactory = $mappingFactory;
        $this->_mappingResource = $mappingResource;
        $this->replaceSymbols = str_split(self::SPECIAL_CHARACTERS, 1);
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            if (empty($data['id'])) {
                $data['id'] = null;
            }

            /** @var \Zhixing\Blacklist\Model\Mapping $model */
            $model = $this->_mappingFactory->create();
            $id = $this->getRequest()->getParam('id');
            if ($id) {
                $model->getResource()->load($model, $id);
            }

            foreach ($data as $k => $v) {
                if (in_array($k, $this->safeFields)) {
                    $data[$k] = str_replace($this->replaceSymbols, '', $v);
                }
            }

            $model->setData($data);
            try {
                $mappingId = $this->_mappingResource->existName($data['name'], $data['type'], $data['map']);
                if ($mappingId && $mappingId != $data['id']) {
                    throw new \Exception('name is exist');
                }

                $model->getResource()->save($model);
                $this->messageManager->addSuccessMessage(__('You saved the customer in blacklist.'));
                $this->dataPersistor->clear('mapping');
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, $e->getMessage());
            }

            $this->dataPersistor->set('mapping', $data);
            return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}
