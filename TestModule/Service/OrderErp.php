<?php
declare(strict_types=1);

namespace Sample\TestModule\Service;

use Sample\TestModule\Helper\Data as TestHelper;
use Magento\Framework\HTTP\ZendClient;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Exception\LocalizedException;
use Zend_Http_Client_Exception;

/**
 * Class OrderErp
 */
class OrderErp
{
    private TestHelper $helper;
    private ZendClientFactory $httpClientFactory;
    private Json $json;

    /**
     * OrderErp constructor.
     * @param ZendClientFactory $httpClientFactory
     * @param Json $json
     * @param TestHelper $helper
     */
    public function __construct(
        ZendClientFactory $httpClientFactory,
        Json $json,
        TestHelper $helper
    ) {
        $this->httpClientFactory = $httpClientFactory;
        $this->json = $json;
        $this->helper = $helper;
    }

    /**
     * Send Request
     *
     * @param array $params
     * @return array
     * @throws LocalizedException
     */
    public function sendRequest(array $params = []): array
    {
        $client = $this->httpClientFactory->create();
        $url = $this->helper->getErpEndpoint();
        $apiKey = $this->helper->getErpApiKey();

        try {
            $client->setUri($url);
            $client->setConfig(['timeout' => 30]);
            $client->setHeaders('Authorization', 'Bearer ' . $apiKey);
            $client->setRawData($this->json->serialize($params), 'application/json');
            $client->setMethod(ZendClient::POST);

            $responseBody = $client->request()->getBody();

            $response = [
                'response_code' => $client->getLastResponse()->getStatus() ?? 200,
                'message' => 'Integrated successfully',
                'response_body' => $responseBody
            ];

        } catch (Zend_Http_Client_Exception $e) {
            throw new LocalizedException(
                __("[ErpOrderSync] An error occurred while order integration with ERP. Error: {$e->getMessage()}")
            );
        }

        return $response;
    }
}
