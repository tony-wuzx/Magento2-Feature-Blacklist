<?php

namespace Zhixing\Blacklist\Model\Mapping\Source;

/**
 * Is active filter source
 */
class Device extends Type
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 'ios',
                'label' => __('Ios')
            ],
            [
                'value' => 'android',
                'label' => __('Android')
            ]
        ];
    }
}
