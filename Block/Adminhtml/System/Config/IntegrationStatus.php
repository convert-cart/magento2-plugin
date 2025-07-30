<?php
declare(strict_types=1);

namespace Convertcart\Analytics\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Convertcart\Analytics\Model\IntegrationTokenManager;

class IntegrationStatus extends Field
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
                return '<span style="color: green;">✓ Integration Active</span>';
            } else {
                $credentials = $this->tokenManager->getOrCreateTokens();
                if ($credentials) {
                    return '<span style="color: green;">✓ Integration Active (Just Activated)</span>';
                } else {
                    return '<span style="color: orange;">⚠ Integration Pending</span>';
                }
            }
        } catch (\Exception $e) {
            return '<span style="color: red;">✗ Integration Error: ' . $this->escapeHtml($e->getMessage()) . '</span>';
        }
    }
}
