<?php
declare(strict_types=1);

namespace Sample\TestModule\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Helper Data class
 */
class Data extends AbstractHelper
{
    public const XML_PATH_ERP_INTEGRATION_ACTIVE = 'sampletest_erp/sales/is_active';
    public const XML_PATH_ERP_INTEGRATION_API_KEY = 'sampletest_erp/sales/api_key';
    public const XML_PATH_ERP_INTEGRATION_ENDPOINT = 'sampletest_erp/sales/endpoint';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Data constructor.
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * Retrieve erp sales integration status
     *
     * @param null $store
     * @return int
     */
    public function getIsSalesIntegrationActive($store = null): int
    {
        return (int)$this->scopeConfig->getValue(
            self::XML_PATH_ERP_INTEGRATION_ACTIVE,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Retrieve erp api key
     *
     * @param null $store
     * @return string
     */
    public function getErpApiKey($store = null): string
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_ERP_INTEGRATION_API_KEY,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Retrieve erp endpoint
     *
     * @param null $store
     * @return string
     */
    public function getErpEndpoint($store = null): string
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_ERP_INTEGRATION_ENDPOINT,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }
}
