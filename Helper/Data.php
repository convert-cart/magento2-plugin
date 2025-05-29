<?php
declare(strict_types=1);

namespace Convertcart\Analytics\Helper;

use Magento\Framework\Module\ModuleListInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * Scope config interface instance.
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Module list interface instance.
     *
     * @var ModuleListInterface
     */
    protected $moduleList;

    /**
     * Constructor for the Helper Data class.
     *
     * @param \Magento\Framework\App\Helper\Context              $context     Helper context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig Scope config
     * @param ModuleListInterface                                $moduleList  Module list
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        ModuleListInterface $moduleList
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->moduleList = $moduleList;
        parent::__construct($context);
    }

    /**
     * Get the mapped event type for a given event name.
     *
     * @param string $event Event name
     *
     * @return string
     */
    public function getEventType(string $event): string
    {
        $eventMap = [
            'homepageViewed'      =>  'homepageViewed',
            'contentPageViewed'   =>  'contentPageViewed',
            'categoryViewed'      =>  'categoryViewed',
            'productViewed'       =>  'productViewed',
            'productsSearched'    =>  'productsSearched',
            'registered'          =>  'registered',
            'loggedIn'            =>  'loggedIn',
            'loggedOut'           =>  'loggedOut',
            'cartViewed'          =>  'cartViewed',
            'checkoutViewed'      =>  'checkoutViewed',
            'cartUpdated'         =>  'cartUpdated',
            'productAdded'        =>  'productAdded',
            'productRemoved'      =>  'productRemoved',
            'orderCompleted'      =>  'orderCompleted',
            'couponApplied'       =>  'couponApplied',
            'couponDenied'        =>  'couponDenied',
            'couponRemoved'       =>  'couponRemoved',
        ];
        if (!empty($eventMap[$event])) {
            return $eventMap[$event];
        } else {
            return 'default';
        }
    }

    /**
     * Check if Convertcart Analytics is enabled.
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return (bool)$this->getClientKey();
    }

    /**
     * Get the configured client key.
     *
     * @return string|null
     */
    public function getClientKey(): ?string
    {
        $clientKey = $this->scopeConfig->getValue(
            'convertcart/configuration/domainid',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if (empty($clientKey)) {
            return null;
        } else {
            return $clientKey;
        }
    }

    /**
     * Get the module version from module list.
     *
     * @return string|null
     */
    public function getModuleVersion(): ?string
    {
        $ccModule = $this->moduleList->getOne('Convertcart_Analytics');
        return !empty($ccModule['setup_version']) ? $ccModule['setup_version'] : null;
    }

    /**
     * Sanitize a parameter by stripping tags.
     *
     * @param string|null $param Parameter to sanitize
     *
     * @return string|null
     */
    public function sanitizeParam(?string $param): ?string
    {
        if ($param === null) {
            return null;
        }
        $sanitized = strip_tags($param);
        return $sanitized;
    }
}
