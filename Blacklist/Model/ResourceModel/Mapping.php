<?php

namespace Zhixing\Blacklist\Model\ResourceModel;

use \Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Mapping
 * @package Zhixing\Blacklist\Model\ResourceModel
 */
class Mapping extends AbstractDb
{
    /**
     * @var string
     */
    const TABLE_NAME = 'zhixing_core_mapping';

    /**
     * Initialize resource model
     * @return void
     */
    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, 'id');
    }

    /**
     * @return array
     */
    public function getMapping()
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from($this->getTable(self::TABLE_NAME));

        return $connection->fetchAll($select);
    }

    /**
     * @param $name
     * @param $type
     * @return bool
     */
    public function existName($name, $type, $map)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from($this->getTable(self::TABLE_NAME));
        $select->where('type = :type', $type);
        $select->where('name = :name', $name);
        $select->where('map = :map', $map);
        $bind = [':type'=> $type, ':name' => $name, ':map' => $map];

        return $connection->fetchOne($select, $bind);
    }
}
