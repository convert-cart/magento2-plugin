<?php
declare(strict_types=1);

namespace Convertcart\Analytics\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Cc model for Convertcart Analytics
 */
class Cc extends AbstractModel
{
    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layout;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Convertcart\Analytics\Helper\Data
     */
    protected $dataHelper;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $fwSession;

    /**
     * Cc constructor.
     *
     * @param \Magento\Framework\Model\Context::class                   $context      Model context
     * @param \Magento\Framework\Registry                        $registry     Registry
     * @param \Magento\Framework\View\LayoutInterface            $layout       Layout interface
     * @param \Magento\Store\Model\StoreManagerInterface         $storeManager Store manager
     * @param \Convertcart\Analytics\Helper\Data                 $dataHelper   Data helper
     * @param \Magento\Framework\Session\SessionManagerInterface $fwSession    Session manager
     * @param array                                              $data         Additional data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        LayoutInterface $layout,
        StoreManagerInterface $storeManager,
        \Convertcart\Analytics\Helper\Data $dataHelper,
        SessionManagerInterface $fwSession,
        array $data = []
    ) {
        $this->layout = $layout;
        $this->storeManager = $storeManager;
        $this->dataHelper = $dataHelper;
        $this->fwSession = $fwSession;
        parent::__construct($context, $registry, $data);
    }

    /**
     * Get the initial script block if enabled.
     *
     * @return \Magento\Framework\View\Element\Template|null
     */
    public function getInitScript(): ?\Magento\Framework\View\Element\Template
    {
        if ($this->_dataHelper->isEnabled() == false) {
            return null;
        }

        $clientKey = $this->_dataHelper->getClientKey();
        if (empty($clientKey)) {
            return null;
        }
        $script = $this->_layout->createBlock(\Convertcart\Analytics\Block\Script::class)
            ->setTemplate('Convertcart_Analytics::init.phtml')
            ->assign(
                [
                    'clientKey' => $clientKey
                ]
            );
        return $script;
    }

    /**
     * Get the event script block if enabled.
     *
     * @param  array $eventData
     * @return \Magento\Framework\View\Element\Template|null
     */
    public function getEventScript(array $eventData = []): ?\Magento\Framework\View\Element\Template
    {
        if ($this->_dataHelper->isEnabled() == false) {
            return null;
        }

        $clientKey = $this->_dataHelper->getClientKey();
        $script = $this->_layout->createBlock(\Convertcart\Analytics\Block\Script::class)
            ->setTemplate('Convertcart_Analytics::event.phtml')
            ->assign(
                [
                    'eventData' => json_encode($eventData),
                    'clientKey' => $clientKey
                ]
            );
        return $script;
    }

    /**
     * Store Convertcart events in session.
     *
     * @param  string $eventName
     * @param  array  $eventData
     * @return void
     */
    public function storeCcEvents(string $eventName, array $eventData = []): void
    {
        if ($this->_dataHelper->isEnabled() == false) {
            return;
        }
        $ccEvents = $this->_fwSession->getCcEvents();
        $eventData['ccEvent'] = $this->_dataHelper->getEventType($eventName);
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

        $this->_fwSession->setCcEvents($ccEvents);
    }

    /**
     * Fetch and clear stored Convertcart events from session.
     *
     * @return array
     */
    public function fetchCcEvents(): array
    {
        if ($this->_dataHelper->isEnabled() == false) {
            return null;
        }

        $ccEvents = $this->_fwSession->getCcEvents();
        $this->_fwSession->setCcEvents();
        if (empty($ccEvents)) {
            return [];
        } else {
            return $ccEvents;
        }
    }

    /**
     * Add plugin metadata to event data.
     *
     * @param  array $eventData
     * @return array
     */
    public function addMetaData(array $eventData = []): array
    {
        $metaData = [];
        $metaData['plugin_version'] = $this->_dataHelper->getModuleVersion();
        $eventData['meta_data'] = $metaData;
        return $eventData;
    }

    /**
     * Get cart event data from quote object.
     *
     * @param  \Magento\Quote\Model\Quote $quote
     * @param  string                     $currency
     * @return array|null
     */
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
                    $store = $this->_storeManager->getStore();
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

    /**
     * Get custom options for a cart item.
     *
     * @param  \Magento\Quote\Model\Quote\Item $item
     * @return array|null
     */
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

    /**
     * Get customer data array from customer object.
     *
     * @param  \Magento\Customer\Model\Customer $customer
     * @return array|null
     */
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
