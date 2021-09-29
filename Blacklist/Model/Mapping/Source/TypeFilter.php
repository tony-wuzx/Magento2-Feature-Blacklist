<?php

namespace Zhixing\Blacklist\Model\Mapping\Source;

/**
 * Is active filter source
 */
class TypeFilter extends Type
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return array_merge([['label' => '', 'value' => '']], parent::toOptionArray());
    }
}
