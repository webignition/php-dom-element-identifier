<?php

declare(strict_types=1);

namespace webignition\DomElementIdentifier;

use webignition\DomElementLocator\ElementLocator;

class ElementIdentifier extends ElementLocator implements ElementIdentifierInterface
{
    /**
     * @var ElementIdentifierInterface
     */
    private $parentIdentifier;

    public function getParentIdentifier(): ?ElementIdentifierInterface
    {
        return $this->parentIdentifier;
    }

    public function withParentIdentifier(ElementIdentifierInterface $parentIdentifier): ElementIdentifierInterface
    {
        $new = clone $this;
        $new->parentIdentifier = $parentIdentifier;

        return $new;
    }

    public function getScope(): array
    {
        $scope = [];

        $parentIdentifier = $this->getParentIdentifier();
        while ($parentIdentifier instanceof ElementIdentifierInterface) {
            $scope[] = $parentIdentifier;
            $parentIdentifier = $parentIdentifier->getParentIdentifier();
        }

        return array_reverse($scope);
    }

    /**
     * @return array<mixed>
     */
    public function jsonSerialize(): array
    {
        return Serializer::toArray($this);
    }

    /**
     * @param string $json
     *
     * @return ElementIdentifierInterface
     *
     * @throws InvalidJsonException
     */
    public static function fromJson(string $json): ElementIdentifierInterface
    {
        return Serializer::fromJson($json);
    }

    public static function fromAttributeIdentifier(ElementIdentifierInterface $identifier): ElementIdentifierInterface
    {
        $elementIdentifier = new ElementIdentifier(
            $identifier->getLocator(),
            $identifier->getOrdinalPosition()
        );

        $parentIdentifier = $identifier->getParentIdentifier();
        if ($parentIdentifier instanceof ElementIdentifierInterface) {
            $parentIdentifier = ElementIdentifier::fromAttributeIdentifier($parentIdentifier);

            $elementIdentifier = $elementIdentifier->withParentIdentifier($parentIdentifier);
        }

        return $elementIdentifier;
    }

    public function __toString(): string
    {
        $string = '$' . parent::__toString();

        if (null !== $this->parentIdentifier) {
            $prefix = '$"';
            $string = $prefix . '{{ ' . (string) $this->parentIdentifier . ' }} ' . substr($string, strlen($prefix));
        }

        return $string;
    }
}
