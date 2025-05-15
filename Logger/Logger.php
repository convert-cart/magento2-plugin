<?php
declare(strict_types=1);

namespace Convertcart\Analytics\Logger;

use Monolog\Logger as MonologLogger;

class Logger extends MonologLogger
{
    /**
     * WARNING: This class should only be instantiated via Magento's Dependency Injection (DI) system.
     * Instantiating directly or via ObjectManager may result in missing required arguments and errors.
     */
    public function __construct(
        string $name = 'convertcart',
        array $handlers = [],
        array $processors = []
    ) {
        parent::__construct($name, $handlers, $processors);
    }
}
