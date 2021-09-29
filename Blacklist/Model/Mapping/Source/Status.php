<?php

namespace Zhixing\Blacklist\Model\Mapping\Source;

/**
 * Is active filter source
 */
class Status extends Type
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 0,
                'label' => __('No')
            ],
            [
                'value' => 1,
                'label' => __('Yes')
            ]
        ];
    }
}
