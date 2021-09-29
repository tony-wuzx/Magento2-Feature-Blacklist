<?php

namespace Zhixing\Blacklist\Model;

use \Magento\Framework\Model\AbstractModel;

/**
 * Class Mapping
 * @package Zhixing\Blacklist\Model
 */
class Mapping extends AbstractModel
{
    /**
     * @var string
     */
    const COMMON_ID = 'id';

    /**
     * @var string
     */
    const MAPPING_TYPE_DEVICE = 'device';

    /**
     * Name of object id field
     * @var string
     */
    protected $_idFieldName = self::COMMON_ID;

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'zhixing_core_mapping';

    /**
     * @var array
     */
    protected $_types = [
        self::MAPPING_TYPE_DEVICE => 'Device'
    ];

    /**
     * Custom constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Model\Visitor $customerVisitor
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_init(\Zhixing\Blacklist\Model\ResourceModel\Mapping::class);
    }

    /**
     * @return array
     */
    public function getAvailableTypes()
    {
        return $this->_types;
    }
}
