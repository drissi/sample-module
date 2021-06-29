<?php
declare(strict_types=1);

namespace Sample\TestModule\Observer;

use Sample\TestModule\Helper\Data as TestHelper;
use Sample\TestModule\Service\OrderErp;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;

/**
 * OrderIntegrationObserver class
 */
class OrderIntegrationObserver implements ObserverInterface
{
    private TestHelper $helper;
    private OrderErp $erpOrderService;
    private CustomerRepositoryInterface $customerRepositoryInterface;
    private LoggerInterface $logger;
    private Json $json;

    /**
     * OrderIntegrationObserver constructor.
     * @param TestHelper $helper
     * @param OrderErp $erpOrderService
     * @param CustomerRepositoryInterface $customerRepositoryInterface
     * @param Json $json
     * @param LoggerInterface $logger
     */
    public function __construct(
        TestHelper $helper,
        OrderErp $erpOrderService,
        CustomerRepositoryInterface $customerRepositoryInterface,
        Json $json,
        LoggerInterface $logger
    ) {
        $this->helper = $helper;
        $this->erpOrderService = $erpOrderService;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->json = $json;
        $this->logger = $logger;
    }

    /**
     * @param EventObserver $observer
     * @return $this|void
     */
    public function execute(EventObserver $observer): self
    {
        if (!$this->helper->getIsSalesIntegrationActive()) {
            return $this;
        }

        $order = $observer->getOrder();
        $erpOrderData = $this->parseOrderInfo($order);

        try {
            $this->erpOrderService->sendRequest($erpOrderData);

            $this->logger->notice("[ErpOrderSync] Order #{$order->getIncrementId()} integrated successfully to ERP.");
        } catch (LocalizedException $e) {
            $this->logger->error(
                "[ErpOrderSync] Error whie order #{$order->getIncrementId()} integration to ERP." .
                "Error:: " . $e->getMessage()
            );
        }

        return $this;
    }

    /**
     * Format order info based on ERP contract
     *
     * @param Order $order
     * @return array
     */
    protected function parseOrderInfo(Order $order): array
    {
        $shippingAddress = $order->getShippingAddress() ?? [];
        $payment = $order->getPayment() ?? [];
        $paymentInformation = $payment->getAdditionalInformation() ?? [];

        $data = [
            'customer' => [
                'name' => $order->getCustomerFirstname() . $order->getCustomerLastname(),
                'cpf_cnpj' => $shippingAddress->getVatId() ?? '',
                'telephone' => $shippingAddress->getTelephone() ?? '',
                'cnpj' => '',
                'razao_social' => '',
                'nome_fantasia' => '',
                'ie' => '',
                'dob' => ''
            ],
            'shipping_address' => [
                'street' => $shippingAddress->getStreetLine(1) ?? '',
                'number' => $shippingAddress->getStreetLine(2) ?? '',
                'additional' => $shippingAddress->getStreetLine(3) ?? '',
                'neighborhood' => $shippingAddress->getStreetLine(4) ?? '',
                'city' => $shippingAddress->getCity() ?? '',
                'city_ibge_code' => '',
                'uf' => $shippingAddress->getRegion() ?? '',
                'country' => $shippingAddress->getCountryId() ?? ''
            ],
            "shipping_method" => $order->getShippingMethod() ?? '',
            "payment_method" => $payment->getMethod() ?? '',
            "payment_installments" => $paymentInformation['cc_installments'] ?? 1,
            "subtotal" => $order->getSubtotal() ?? 0,
            "shipping_amount" => $order->getShippingAmount() ?? 0,
            "discount" => $order->getDiscountAmount() ?? 0,
            "total" => $order->getGrandTotal() ?? 0,
        ];

        $data['items'] = [];
        foreach ($order->getAllVisibleItems() as $item) {
            $data['items'][] = [
                'sku' => $item->getSku(),
                'name' => $item->getName(),
                'price' => $item->getPrice(),
                'qty' => $item->getQtyOrdered()
            ];
        }

        return $data;
    }
}
