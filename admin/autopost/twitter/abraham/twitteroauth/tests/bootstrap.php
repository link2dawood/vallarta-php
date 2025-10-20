<?php

declare(strict_types=1);

require __DIR__ . '/../twitter/autoload.php';
require 'vars.php';
require 'mocks.php';

\VCR\VCR::configure()->setStorage('json');
\VCR\VCR::turnOn();
