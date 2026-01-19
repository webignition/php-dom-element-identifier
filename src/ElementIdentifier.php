<?php

declare(strict_types=1);

namespace webignition\DomElementIdentifier;

class ElementIdentifier extends ElementLocator implements ElementIdentifierInterface
{
    private ?ElementIdentifierInterface $parentIdentifier = null;

    public function __toString(): string
    {
        $string = '$' . parent::__toString();

        if (null !== $this->parentIdentifier) {
            $string = (string) $this->parentIdentifier . ' >> ' . $string;
        }

        return $string;
    }

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
}
