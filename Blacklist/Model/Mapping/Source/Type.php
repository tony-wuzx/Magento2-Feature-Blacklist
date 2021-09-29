<?php

namespace Zhixing\Blacklist\Model\Mapping\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Type
 * @package Zhixing\Blacklist\Model\Mapping\Source
 */
class Type implements OptionSourceInterface
{
    /**
     * @var \Zhixing\Blacklist\Model\Mapping
     */
    protected $mapping;

    /**
     * Type constructor.
     * @param \Zhixing\Blacklist\Model\Mapping $mapping
     */
    public function __construct(\Zhixing\Blacklist\Model\Mapping $mapping)
    {
        $this->mapping = $mapping;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $availableOptions = $this->mapping->getAvailableTypes();
        $options = [];
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }
}
