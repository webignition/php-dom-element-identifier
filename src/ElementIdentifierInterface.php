<?php

declare(strict_types=1);

namespace webignition\DomElementIdentifier;

interface ElementIdentifierInterface extends ElementLocatorInterface, \JsonSerializable
{
    public function __toString(): string;

    public function getParentIdentifier(): ?ElementIdentifierInterface;

    public function withParentIdentifier(ElementIdentifierInterface $parentIdentifier): ElementIdentifierInterface;

    /**
     * @return array<int, ElementIdentifierInterface>
     */
    public function getScope(): array;

    /**
     * @throws InvalidJsonException
     */
    public static function fromJson(string $json): ElementIdentifierInterface;

    public static function fromAttributeIdentifier(ElementIdentifierInterface $identifier): ElementIdentifierInterface;
}
