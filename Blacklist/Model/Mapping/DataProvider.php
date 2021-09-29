<?php

namespace Zhixing\Blacklist\Model\Mapping;

use Zhixing\Blacklist\Model\ResourceModel\Mapping\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;

/**
 * Class DataProvider
 * @package Zhixing\Blacklist\Model\Mapping
 */
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var CollectionFactory
     */
    protected $collection;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * DataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $mappingCollectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $mappingCollectionFactory,
        DataPersistorInterface $dataPersistor,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $mappingCollectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->meta = $this->prepareMeta($this->meta);
    }

    /**
     * Prepares Meta
     * @param array $meta
     * @return array
     */
    public function prepareMeta(array $meta)
    {
        return $meta;
    }

    /**
     * Get data
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        /** @var $mapping \Zhixing\Blacklist\Model\Mapping */
        foreach ($items as $common) {
            $this->loadedData[$common->getId()] = $common->getData();
        }

        $data = $this->dataPersistor->get('mapping');
        if (!empty($data)) {
            $mapping = $this->collection->getNewEmptyItem();
            $mapping->setData($data);
            $this->loadedData[$mapping->getId()] = $mapping->getData();
            $this->dataPersistor->clear('mapping');
        }

        return $this->loadedData;
    }
}
