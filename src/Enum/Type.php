<?php

declare(strict_types=1);

namespace webignition\DomElementIdentifier\Enum;

enum Type: string
{
    case CSS = 'css';
    case XPATH = 'xpath';
}
