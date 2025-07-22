<?php
declare(strict_types=1);

namespace Convertcart\Analytics\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class Environment implements ArrayInterface
{
    public function toOptionArray(): array
    {
        return [
            ['value' => 'production', 'label' => __('Production (app.convertcart.com)')],
            ['value' => 'beta', 'label' => __('Beta (app-beta.convertcart.com)')]
        ];
    }
}
