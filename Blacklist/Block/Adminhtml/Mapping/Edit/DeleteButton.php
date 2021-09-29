<?php

namespace Zhixing\Blacklist\Block\Adminhtml\Mapping\Edit;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class DeleteButton
 * @package Zhixing\Blacklist\Block\Adminhtml\Mapping\Edit
 */
class DeleteButton implements ButtonProviderInterface
{

    /**
     * @var \Magento\Backend\Block\Widget\Context
     */
    protected $context;

    /**
     * @var \Zhixing\Blacklist\Model\MappingFactory
     */
    protected $_mappingFactory;

    /**
     * DeleteButton constructor.
     * @param Context $context
     * @param \Zhixing\Blacklist\Model\MappingFactory $mappingFactory
     */
    public function __construct(
        Context $context,
        \Zhixing\Blacklist\Model\MappingFactory $mappingFactory
    ) {
        $this->context = $context;
        $this->_mappingFactory = $mappingFactory;
    }

    /**
     * @return array
     */
    public function getButtonData()
    {
        $data = [];
        if ($this->getMappingId()) {
            $data = [
                'label' => __('Delete Customer in Blacklist'),
                'class' => 'delete',
                'on_click' => 'deleteConfirm(\'' . __(
                        'Are you sure you want to do this?'
                    ) . '\', \'' . $this->getDeleteUrl() . '\')',
                'sort_order' => 20,
            ];
        }
        return $data;
    }

    /**
     * @return string
     */
    public function getDeleteUrl()
    {
        return $this->getUrl('*/*/delete', ['id' => $this->getMappingId()]);
    }

    /**
     * @return int|null
     */
    public function getMappingId()
    {
        try {
            $mapping = $this->_mappingFactory->create();
            $mapping->getResource()->load($mapping, $this->context->getRequest()->getParam('id'));
            return $mapping->getId();
        } catch (NoSuchEntityException $e) {
            //do nothing here
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            //do nothing here
        }
        return null;
    }

    /**
     * Generate url by route and parameters
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}
