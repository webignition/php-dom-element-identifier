<?php

declare(strict_types=1);

namespace webignition\DomElementIdentifier;

class AttributeIdentifier extends ElementIdentifier implements AttributeIdentifierInterface
{
    private $attributeName = null;

    public function __construct(string $locator, string $attributeName, ?int $ordinalPosition = null)
    {
        parent::__construct($locator, $ordinalPosition);

        $this->attributeName = $attributeName;
    }

    public function getAttributeName(): string
    {
        return $this->attributeName;
    }

    public function __toString(): string
    {
        return parent::__toString() . '.' . $this->attributeName;
    }
}