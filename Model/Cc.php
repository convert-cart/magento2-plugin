<?php
declare(strict_types=1);

namespace Convertcart\Analytics\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Store\Model\StoreManagerInterface;

class Cc extends AbstractModel
{
    private LayoutInterface $layout;
    private StoreManagerInterface $storeManager;
    private \Convertcart\Analytics\Helper\Data $dataHelper;
    private SessionManagerInterface $fwSession;

    public function __construct(
        Context $context,
        Registry $registry,
        LayoutInterface $layout,
        StoreManagerInterface $storeManager,
        \Convertcart\Analytics\Helper\Data $dataHelper,
        SessionManagerInterface $fwSession,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->layout = $layout;
        $this->storeManager = $storeManager;
        $this->dataHelper = $dataHelper;
        $this->fwSession = $fwSession;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    public function getInitScript(): ?\Magento\Framework\View\Element\Template
    {
        if ($this->dataHelper->isEnabled() == false) {
            return null;
        }

        $clientKey = $this->dataHelper->getClientKey();
        if (empty($clientKey)) {
            return null;
        }
        $script = $this->layout
            ->createBlock(\Convertcart\Analytics\Block\Script::class)
            ->setTemplate('Convertcart_Analytics::init.phtml')
            ->assign([
                'clientKey' => $clientKey
            ]);
        return $script;
    }

    public function getEventScript(array $eventData = []): ?\Magento\Framework\View\Element\Template
    {
        if ($this->dataHelper->isEnabled() == false) {
            return null;
        }

        $clientKey = $this->dataHelper->getClientKey();
        $script = $this->layout
            ->createBlock(\Convertcart\Analytics\Block\Script::class)
            ->setTemplate('Convertcart_Analytics::event.phtml')
            ->assign([
                'eventData' => json_encode($eventData),
                'clientKey' => $clientKey
            ]);

        return $script;
    }

    public function storeCcEvents(string $eventName, array $eventData = []): void
    {
        if ($this->dataHelper->isEnabled() == false) {
            return;
        }
        $ccEvents = $this->fwSession->getCcEvents();
        $eventData['ccEvent'] = $this->dataHelper->getEventType($eventName);
        $eventLimit = 3;
        if (empty($ccEvents)) {
            $ccEvents = [];
            $ccEvents[] = $this->addMetaData($eventData);
        } elseif (count($ccEvents) >= $eventLimit) {
            $eventIndex = $eventLimit - 1;
            $ccEvents[$eventIndex] = $this->addMetaData($eventData);
        } else {
            $ccEvents[] = $this->addMetaData($eventData);
        }

        $this->fwSession->setCcEvents($ccEvents);
    }

    public function fetchCcEvents(): array
    {
        if ($this->dataHelper->isEnabled() == false) {
            return [];
        }

        $ccEvents = $this->fwSession->getCcEvents();
        $this->fwSession->setCcEvents();
        if (empty($ccEvents)) {
            return [];
        } else {
            return $ccEvents;
        }
    }

    public function addMetaData(array $eventData = []): array
    {
        $metaData = [];
        $metaData['plugin_version'] = $this->dataHelper->getModuleVersion();
        $eventData['meta_data'] = $metaData;
        return $eventData;
    }

    public function getCartEventData($quote, string $currency): ?array
    {
        if (!is_object($quote)) {
            return null;
        }
        $cartItems = $quote->getAllVisibleItems();
        $eventData = [];
        $eventData['currency'] = $currency;
        $eventData['items'] = [];
        foreach ($cartItems as $item) {
            $cartItem = [];
            $cartItem['name'] = str_replace("'", "", $item->getName());
            $cartItem['price'] = $item->getPrice();
            $cartItem['currency'] = $currency;
            $cartItem['quantity'] = $item->getQty();
            $cartItem['id'] = $item->getProductId();
            $cartItem['sku'] = $item->getSku();
            $cartItem['customOptions'] = $this->getCartItemOptions($item);

            $product = $item->getProduct();
            if (is_object($product)) {
                $cartItem['url'] = $product->getProductUrl();
                if (!empty($product->getSmallImage())) {
                    $store = $this->storeManager->getStore();
                    $cartItem['image'] = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
                    $cartItem['image'].= 'catalog/product'.$product->getSmallImage();
                }
            }
            $eventData['items'][] = $cartItem;
        }
        $eventData['currency'] = $currency;
        $eventData['coupon_code'] = $quote->getCouponCode();
        $eventData['subtotal'] = $quote->getSubtotal();
        $eventData['total'] = $quote->getGrandTotal();
        $eventData['base_total'] = $quote->getBaseGrandTotal();

        return $eventData;
    }

    public function getCartItemOptions($item): ?array
    {
        if (!is_object($item)) {
            return null;
        }

        $product = $item->getProduct();
        if (!is_object($product)) {
            return null;
        }

        $productInstance = $product->getTypeInstance(true);
        if (!is_object($productInstance)) {
            return null;
        }

        $productOptions = $productInstance->getOrderOptions($product);
        $options = !empty($productOptions['options']) ? $productOptions['options'] : null;
        if (empty($options)) {
            return null;
        }

        $customOptions = [];
        foreach ($options as $option) {
            $customOption = [];
            $customOption['label'] = !empty($option['label']) ? $option['label'] : null;
            $customOption['value'] = !empty($option['value']) ? $option['value'] : null;
            $customOption['option_id'] = !empty($option['option_id']) ? $option['option_id'] : null;
            $customOption['option_type'] = !empty($option['option_type']) ? $option['option_type'] : null;
            $customOptions[] = $customOption;
        }

        return $customOptions;
    }

    public function getCustomerData($customer): ?array
    {
        if (!is_object($customer)) {
            return null;
        }
        $customerData = [];
        $customerData['email'] = $customer->getEmail();
        $customerData['first_name'] = $customer->getFirstname();
        $customerData['last_name'] = $customer->getLastname();
        $customerData['id'] = $customer->getId();

        return $customerData;
    }
}
