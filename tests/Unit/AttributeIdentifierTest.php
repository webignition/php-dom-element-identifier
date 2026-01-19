<?php

declare(strict_types=1);

namespace webignition\DomElementIdentifier\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use webignition\DomElementIdentifier\AttributeIdentifier;
use webignition\DomElementIdentifier\AttributeIdentifierInterface;
use webignition\DomElementIdentifier\ElementIdentifier;
use webignition\DomElementIdentifier\Serializer;

class AttributeIdentifierTest extends TestCase
{
    public function testGetAttributeName(): void
    {
        $attributeName = 'attribute_name';
        $identifier = new AttributeIdentifier('.selector', $attributeName);

        $this->assertSame($attributeName, $identifier->getAttributeName());
    }

    public function testJsonSerialize(): void
    {
        $this->assertSame(
            [
                Serializer::KEY_LOCATOR => '.selector',
                Serializer::KEY_ATTRIBUTE => 'attribute_name',
            ],
            (new AttributeIdentifier('.selector', 'attribute_name'))->jsonSerialize()
        );
    }

    public function testFromJson(): void
    {
        $this->assertEquals(
            new AttributeIdentifier('.selector', 'attribute_name'),
            AttributeIdentifier::fromJson((string) json_encode([
                Serializer::KEY_LOCATOR => '.selector',
                Serializer::KEY_ATTRIBUTE => 'attribute_name',
            ]))
        );
    }

    #[DataProvider('toStringDataProvider')]
    public function testToString(AttributeIdentifierInterface $identifier, string $expectedString): void
    {
        $this->assertSame($expectedString, (string) $identifier);
    }

    /**
     * @return array<mixed>
     */
    public static function toStringDataProvider(): array
    {
        return [
            'css selector with attribute' => [
                'identifier' => new AttributeIdentifier('.selector', 'attribute_name'),
                'expectedString' => '$".selector".attribute_name',
            ],
            'css selector with parent, ordinal position and attribute name' => [
                'identifier' => (new AttributeIdentifier('.selector', 'attribute_name', 7))
                    ->withParentIdentifier(
                        new ElementIdentifier('.parent')
                    ),
                'expectedString' => '$".parent" >> $".selector":7.attribute_name',
            ],
        ];
    }
}
