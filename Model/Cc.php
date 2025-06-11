<?php
/**
 * Convertcart Analytics - CC Model
 *
 * Magento 2 model for handling Convertcart Analytics event/session logic.
 *
 * @category   Convertcart
 * @package    Convertcart_Analytics
 * @copyright  Copyright (c) Convertcart
 */
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
    /**
     * @var \Magento\Framework\View\LayoutInterface|null Layout block manager (lazily loaded)
     */
    protected $layout = null;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|null Store manager (lazily loaded)
     */
    protected $storeManager = null;

    /**
     * @var \Convertcart\Analytics\Helper\Data|null Analytics helper (lazily loaded)
     */
    protected $dataHelper = null;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface|null Session manager (lazily loaded)
     */
    protected $fwSession = null;

    /**
     * Lazily load the LayoutInterface instance.
     *
     * @return \Magento\Framework\View\LayoutInterface
     */
    protected function getLayout()
    {
        if ($this->layout === null) {
            $this->layout = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Framework\View\LayoutInterface::class);
        }
        return $this->layout;
    }

    /**
     * Lazily load the StoreManagerInterface instance.
     *
     * @return \Magento\Store\Model\StoreManagerInterface
     */
    protected function getStoreManager()
    {
        if ($this->storeManager === null) {
            $this->storeManager = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Store\Model\StoreManagerInterface::class);
        }
        return $this->storeManager;
    }

    /**
     * Lazily load the Analytics DataHelper instance.
     *
     * @return \Convertcart\Analytics\Helper\Data
     */
    protected function getDataHelper()
    {
        if ($this->dataHelper === null) {
            $this->dataHelper = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Convertcart\Analytics\Helper\Data::class);
        }
        return $this->dataHelper;
    }

    /**
     * Lazily load the SessionManagerInterface instance.
     *
     * @return \Magento\Framework\Session\SessionManagerInterface
     */
    protected function getFwSession()
    {
        if ($this->fwSession === null) {
            $this->fwSession = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Framework\Session\SessionManagerInterface::class);
        }
        return $this->fwSession;
    }

    /**
     * Get the initial script block if enabled.
     *
     * @return \Magento\Framework\View\Element\Template|null
     */
    public function getInitScript(): ?\Magento\Framework\View\Element\Template
    {
        if ($this->getDataHelper()->isEnabled() == false) {
            return null;
        }

        $clientKey = $this->getDataHelper()->getClientKey();
        if (empty($clientKey)) {
            return null;
        }
        $script = $this->getLayout()
            ->createBlock(\Convertcart\Analytics\Block\Script::class)
            ->setTemplate('Convertcart_Analytics::init.phtml')
            ->assign([
                'clientKey' => $clientKey
            ]);
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
        if ($this->getDataHelper()->isEnabled() == false) {
            return null;
        }

        $clientKey = $this->getDataHelper()->getClientKey();
        $script = $this->getLayout()
            ->createBlock(\Convertcart\Analytics\Block\Script::class)
            ->setTemplate('Convertcart_Analytics::event.phtml')
            ->assign([
                'eventData' => json_encode($eventData),
                'clientKey' => $clientKey
            ]);

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
        if ($this->getDataHelper()->isEnabled() == false) {
            return;
        }
        $ccEvents = $this->getFwSession()->getCcEvents();
        $eventData['ccEvent'] = $this->getDataHelper()->getEventType($eventName);
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

        $this->getFwSession()->setCcEvents($ccEvents);
    }

    /**
     * Fetch and clear stored Convertcart events from session.
     *
     * @return array
     */
    public function fetchCcEvents(): array
    {
        if ($this->getDataHelper()->isEnabled() == false) {
            return null;
        }

        $ccEvents = $this->getFwSession()->getCcEvents();
        $this->getFwSession()->setCcEvents();
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
        $metaData['plugin_version'] = $this->getDataHelper()->getModuleVersion();
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
