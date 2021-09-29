<?php

namespace Zhixing\Blacklist\Api;

/**
 * Interface MappingInterface
 * @package Zhixing\Blacklist\Api
 */
interface MappingInterface
{
    /**
     * @param string $type
     * @param bool $clear
     * @return mixed
     */
    public function get($type, $clear = false);

    /**
     * @param array|null $values
     * @return mixed
     */
    public function set(array $values = null);

    /**
     * @param string $needle
     * @param int $type
     * @param bool $forceLogout
     * @return mixed
     */
    public function isDisable($needle = '', $type = 0, $forceLogout = true);
}
