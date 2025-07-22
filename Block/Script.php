<?php
declare(strict_types=1);

namespace Convertcart\Analytics\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Escaper;

class Script extends Template
{
    private Escaper $escaper;

    public function __construct(
        Context $context,
        Escaper $escaper,
        array $data = []
    ) {
        $this->escaper = $escaper;
        parent::__construct($context, $data);
    }

    public function getEscaper(): Escaper
    {
        return $this->escaper;
    }
}
