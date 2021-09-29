<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Zhixing\Blacklist\Ui\Component\Listing\Mapping;

use Magento\Framework\AuthorizationInterface;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\AbstractComponent;

/**
 * Class MassAction
 * @package Zhixing\Blacklist\Ui\Component\Listing\Mapping
 */
class MassAction extends AbstractComponent
{
    /**
     * @var string
     */
    const NAME = 'massaction';

    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
     * Constructor
     *
     * @param AuthorizationInterface $authorization
     * @param ContextInterface $context
     * @param UiComponentInterface[] $components
     * @param array $data
     */
    public function __construct(
        AuthorizationInterface $authorization,
        ContextInterface $context,
        array $components = [],
        array $data = []
    ) {
        $this->authorization = $authorization;
        parent::__construct($context, $components, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function prepare() : void
    {
        $config = $this->getConfiguration();

        foreach ($this->getChildComponents() as $actionComponent) {
            $actionType = $actionComponent->getConfiguration()['type'];
            if ($this->isActionAllowed($actionType)) {
                $config['actions'][] = $actionComponent->getConfiguration();
            }
        }
        $origConfig = $this->getConfiguration();
        if ($origConfig !== $config) {
            $config = array_replace_recursive($config, $origConfig);
        }

        $this->setData('config', $config);
        $this->components = [];

        parent::prepare();
    }

    /**
     * {@inheritdoc}
     */
    public function getComponentName() : string
    {
        return static::NAME;
    }

    /**
     * Check if the given type of action is allowed
     *
     * @param string $actionType
     * @return bool
     */
    public function isActionAllowed($actionType) : bool
    {
        $isAllowed = true;
        switch ($actionType) {
            case 'delete':
                $isAllowed = $this->authorization->isAllowed('Zhixing_Blacklist::core');
                break;
            default:
                break;
        }
        return $isAllowed;
    }
}
