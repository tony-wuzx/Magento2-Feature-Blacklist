<?php

namespace Zhixing\Blacklist\Model\Api;

use Zhixing\Blacklist\Api\MappingInterface;
use Zhixing\Blacklist\Model\Mapping as MappingModel;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Cache\StateInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Webapi\Rest\Response as ResponseHttp;
use Magento\PageCache\Model\Cache\Type;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Response\Http;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Integration\Model\ResourceModel\Oauth\Token\CollectionFactory as TokenCollectionFactory;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;

/**
 * Class Mapping
 * @package Zhixing\Blacklist\Model\Api
 */
class Mapping implements MappingInterface
{
    /**
     * cache tag
     */
    const MAPPING_CACHE_TAG = 'MAPPING_CACHE_TAG';

    /**
     * config path
     */
    const CONFIG_PATH = 'zhixing_blacklist/configuration/is_enabled';

    /**
     * @var null
     */
    protected $_blacklist = [];

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var CacheInterface
     */
    protected $cacheManager;

    /**
     * @var Json|mixed|null
     */
    protected $serializer;

    /**
     * @var StateInterface
     */
    protected $cacheState;

    /**
     * @var MappingModel
     */
    protected $mapping;

    /**
     * @var ResponseHttp
     */
    protected $responseHttp;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var TokenCollectionFactory
     */
    private $tokenCollectionFactory;

    /**
     * @var CustomerCollectionFactory
     */
    protected $customerCollectionFactory;

    /**
     * Mapping constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param CacheInterface $cache
     * @param StateInterface $cacheState
     * @param ResponseHttp $responseHttp
     * @param DateTime $dateTime
     * @param MappingModel $mapping
     * @param CustomerRepositoryInterface $customerRepository
     * @param TokenCollectionFactory $tokenCollectionFactory
     * @param CustomerCollectionFactory $customerCollectionFactory
     * @param Json|null $serializer
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        CacheInterface $cache,
        StateInterface $cacheState,
        ResponseHttp $responseHttp,
        DateTime $dateTime,
        MappingModel $mapping,
        CustomerRepositoryInterface $customerRepository,
        TokenCollectionFactory $tokenCollectionFactory,
        CustomerCollectionFactory $customerCollectionFactory,
        Json $serializer = null
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->mapping = $mapping;
        $this->cacheManager = $cache;
        $this->responseHttp = $responseHttp;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
        $this->cacheState = $cacheState;
        $this->dateTime = $dateTime;
        $this->customerRepository = $customerRepository;
        $this->tokenCollectionFactory = $tokenCollectionFactory;
        $this->customerCollectionFactory = $customerCollectionFactory;
    }

    /**
     * @return bool
     */
    public function isEnable()
    {
        return $this->scopeConfig->getValue(self:: CONFIG_PATH, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Set headers for public cache
     * Accepts the time-to-live (max-age) parameter
     *
     * @param $ttl
     * @return bool
     */
    public function setPublicHeaders($ttl)
    {
        if ($ttl < 0 || !preg_match('/^[0-9]+$/', $ttl)) {
            return false;
        }
        $this->responseHttp->setHeader('pragma', 'cache', true);
        $this->responseHttp->setHeader('X-Varnish-MaxAge', $ttl, true);
        //$this->responseHttp->setHeader('cache-control', 'public, max-age=' . $ttl . ', s-maxage=' . $ttl, true);
        $this->responseHttp->setHeader('cache-control', 'public, max-age=' . $ttl . ', s-maxage=' . $ttl, true);
        $this->responseHttp->setHeader('expires', $this->getExpirationHeader('+' . $ttl . ' seconds'), true);
    }

    /**
     * Given a time input, returns the formatted header
     *
     * @param string $time
     * @return string
     * @codeCoverageIgnore
     */
    protected function getExpirationHeader($time)
    {
        return $this->dateTime->gmDate(Http::EXPIRATION_TIMESTAMP_FORMAT, $this->dateTime->strToTime($time));
    }

    /**
     * {@inheritdoc}
     */
    public function get($type, $clear = false)
    {
        if (!$type) {
            return ['code' => 0, 'message' => __('Invalid param')->__toString()];
        }

        $cacheKey = $this->getCacheKey(['mapping' => 'mapping', 'type' => $type]);
        $useCache = $clear ? false : true;
        $canUseCacheFromBackend = $this->cacheState->isEnabled(Type::TYPE_IDENTIFIER);
        $useCache = $canUseCacheFromBackend ? $useCache : false;
        $cdnCacheLifetime = 86400;
        if ($useCache) {
            $cacheDataJson = $this->cacheManager->load($cacheKey);
            $cachedData = $cacheDataJson ? @json_decode($cacheDataJson, true) : null;
            if ($cachedData) {
                $this->setPublicHeaders($cdnCacheLifetime);
                $cachedData['cached'] = 1;
                return $cachedData;
            }
        }

        try {
            $data = [];
            $items = $this->mapping->getCollection()
                ->addFieldToFilter('type', $type)
                ->getItems();
            if ($items) {
                foreach ($items as $item) {
                    $itemData = $item->getData();
                    unset($itemData['id']);
                    unset($itemData['type']);
                    $data[] = $itemData;
                }
            }

            $result =  [
                'code' => 1,
                'cached' => 0,
                'message' => 'Succeed',
                'data' => $data
            ];



            if ($canUseCacheFromBackend) {
                $this->cacheManager->save(json_encode($result), $cacheKey, [Type::CACHE_TAG, self::MAPPING_CACHE_TAG], $cdnCacheLifetime);
            }

            $this->setPublicHeaders($cdnCacheLifetime);
        } catch (\Exception $e) {
            $result =  ['code' => 0, 'message' => 'Failed'];
        }

        return [$result];
    }

    /**
     * {@inheritdoc}
     */
    public function set(array $values = null)
    {
        if ($values == null) {
            $values[] = json_decode(file_get_contents('php://input'), true);
        }

        foreach ($values as $value) {
            $type = !empty($value['type']) ? trim($value['type']) : 'device';   //device
            $code = !empty($value['code']) ? trim($value['code']) : null;       //device code : ios/android
            $name = !empty($value['name']) ? trim($value['name']) : null;       //Device No.
            $map = !empty($value['map']) ? trim($value['map']) : null;          //Customer ID
            $remarks = !empty($value['remarks']) ? trim($value['remarks']) : null;  //Customer name
            try {
                $availableTypesKeys = array_keys($this->mapping->getAvailableTypes());
                if ($type && $code && $name && $map && in_array($type, $availableTypesKeys)) {
                    $item = $this->mapping->getCollection()
                        ->addFieldToFilter('type', $type)
                        ->addFieldToFilter('code', $code)
                        ->addFieldToFilter('name', $name)
                        ->addFieldToFilter('map', $map)
                        ->getFirstItem();

                    if ($item && $item->getId()) {
                        $compare = [
                            'id' => $item->getId(),
                            'type' => $type,
                            'code' => $code,
                            'name' => $name,
                            'map' => $map,
                            'remarks' => $remarks,
                        ];

                        if ($item->getData() != $compare) {
                            $item->setData($compare);
                            $item->save();
                        }
                    } else {
                        $this->mapping->setData($value);
                        $this->mapping->save();
                    }
                }
            } catch (\Exception $e) {
                return;
            }
        }
    }

    /**
     * @return array
     */
    public function getBlacklist()
    {
        if (empty($this->_blacklist)) {
            $items = $this->mapping->getCollection()->addFieldToFilter('status', 1)->getItems();
            if ($items) {
                $devices = [];
                $customers = [];
                foreach ($items as $item) {
                    $devices[] = $item->getData('name');
                    $customers[] = $item->getData('map');
                }
                $this->_blacklist[1] = array_unique($devices);
                $this->_blacklist[0] = array_unique($customers);
            }
        }

        return $this->_blacklist;
    }

    /**
     * {@inheritdoc}
     */
    public function isDisable($needle = '', $type = 0, $forceLogout = true)
    {
        if ($this->isEnable()) {
            if (isset($this->getBlacklist()[$type])) {
                $isDisable =  in_array($needle, $this->getBlacklist()[$type]);
                if ($isDisable && $forceLogout) {
                    return $this->revokeToken($needle, $type);
                } else {
                    return $isDisable;
                }
            }
        }

        return  false;
    }

    /**
     * @return mixed
     */
    public function revokeToken($needle, $type)
    {
        $customerIds = [];
        if ($type) {
            //load customer by device, maybe be lots of
            $customerCollection = $this->customerCollectionFactory->create()
                ->addAttributeToFilter('register_id', $needle);
            if ($customerCollection->getSize()) {
                foreach ($customerCollection->getItems() as $customer) {
                    /**@var \Magento\Customer\Api\Data\CustomerInterface $customer*/
                    $customerIds[] = $customer->getId();
                }
            }
        } else {
            $customerIds[] = $needle;
        }

        foreach ($customerIds as $customerId) {
            $tokenCollection = $this->tokenCollectionFactory->create()->addFilterByCustomerId($customerId);
            if ($tokenCollection->getSize()) {
                try {
                    foreach ($tokenCollection as $token) {
                        $token->setRevoked(1)->save();
                    }
                } catch (\Exception $e) {
                    throw new LocalizedException(__('The tokens could not be revoked.'));
                }
            }
        }

        return true;
    }

    /**
     * @param int $customerId
     * @return bool
     */
    public function isInBlacklist($customerId)
    {
        if (!$this->isEnable()) {
            return false;
        }

        $customerInBlacklist = $this->isDisable($customerId);
        $deviceInBlacklist = false;

        $customer = $this->customerRepository->getById($customerId);
        if ($customer->getCustomAttribute('register_id')) {
            $device = $customer->getCustomAttribute('register_id')->getValue();
            $deviceInBlacklist = $this->isDisable($device, 1);
        }

        return ($customerInBlacklist || $deviceInBlacklist) ? true : false;
    }

    /**
     * Get key for cache
     *
     * @param array $data
     * @return string
     */
    protected function getCacheKey($data)
    {
        $serializeData = [];
        foreach ($data as $key => $value) {
            if (is_object($value)) {
                $serializeData[$key] = $value->getId();
            } else {
                $serializeData[$key] = $value;
            }
        }
        $serializeData = $this->serializer->serialize($serializeData);
        return sha1($serializeData);
    }
}
