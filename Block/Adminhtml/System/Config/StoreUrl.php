<?php
declare(strict_types=1);

namespace Convertcart\Analytics\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Convertcart\Analytics\Model\IntegrationTokenManager;

class StoreUrl extends Field
{
    private IntegrationTokenManager $tokenManager;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        IntegrationTokenManager $tokenManager,
        array $data = []
    ) {
        $this->tokenManager = $tokenManager;
        parent::__construct($context, $data);
    }

    protected function _getElementHtml(AbstractElement $element): string
    {
        try {
            $tokens = $this->tokenManager->getStoredTokens();
            if ($tokens) {
                $credentials = $this->tokenManager->getOrCreateTokens();
                $storeUrl = $credentials['store_url'] ?? '';
                return '<input type="text" readonly value="' . $this->escapeHtml($storeUrl) . '" style="width: 100%; background: #f5f5f5;" onclick="this.select();" />';
            } else {
                return '<span style="color: orange;">⚠ Integration not ready</span>';
            }
        } catch (\Exception $e) {
            return '<span style="color: red;">✗ Error: ' . $this->escapeHtml($e->getMessage()) . '</span>';
        }
    }
}
