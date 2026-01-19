<?php

declare(strict_types=1);

namespace webignition\DomElementIdentifier\Tests\Unit;

use PHPUnit\Framework\TestCase;
use webignition\DomElementIdentifier\AttributeIdentifier;
use webignition\DomElementIdentifier\ElementIdentifier;
use webignition\DomElementIdentifier\ElementIdentifierInterface;
use webignition\DomElementIdentifier\Serializer;

class ElementIdentifierTest extends TestCase
{
    public function testParentIdentifier(): void
    {
        $identifier = new ElementIdentifier('.selector');
        $this->assertNull($identifier->getParentIdentifier());

        $parentIdentifier = new ElementIdentifier('.parent');
        $identifier = $identifier->withParentIdentifier($parentIdentifier);

        $this->assertSame($parentIdentifier, $identifier->getParentIdentifier());
    }

    /**
     * @dataProvider getScopeDataProvider
     *
     * @param array<int, ElementIdentifierInterface> $expectedScope
     */
    public function testGetScope(ElementIdentifierInterface $elementIdentifier, array $expectedScope): void
    {
        $this->assertEquals($expectedScope, $elementIdentifier->getScope());
    }

    /**
     * @return array<mixed>
     */
    public function getScopeDataProvider(): array
    {
        return [
            'no scope' => [
                'elementIdentifier' => new ElementIdentifier('.selector'),
                'expectedScope' => [],
            ],
            'parent > child' => [
                'elementIdentifier' => (new ElementIdentifier('.child'))
                    ->withParentIdentifier(
                        new ElementIdentifier('.parent')
                    ),
                'expectedScope' => [
                    new ElementIdentifier('.parent'),
                ],
            ],
            'grandparent > parent > child' => [
                'elementIdentifier' => (new ElementIdentifier('.child'))
                    ->withParentIdentifier(
                        (new ElementIdentifier('.parent'))
                            ->withParentIdentifier(
                                new ElementIdentifier('.grandparent')
                            )
                    ),
                'expectedScope' => [
                    new ElementIdentifier('.grandparent'),
                    (new ElementIdentifier('.parent'))
                        ->withParentIdentifier(
                            new ElementIdentifier('.grandparent')
                        ),
                ],
            ],
        ];
    }

    /**
     * @dataProvider toStringDataProvider
     */
    public function testToString(ElementIdentifierInterface $domIdentifier, string $expectedString): void
    {
        $this->assertSame($expectedString, (string) $domIdentifier);
    }

    /**
     * @return array<mixed>
     */
    public function toStringDataProvider(): array
    {
        return [
            'empty' => [
                'domIdentifier' => new ElementIdentifier(''),
                'expectedString' => '$""',
            ],
            'css selector' => [
                'locator' => new ElementIdentifier('.selector'),
                'expectedString' => '$".selector"',
            ],
            'css selector containing double quotes' => [
                'locator' => new ElementIdentifier('a[href="https://example.org"]'),
                'expectedString' => '$"a[href=\"https://example.org\"]"',
            ],
            'xpath expression' => [
                'locator' => new ElementIdentifier('//body'),
                'expectedString' => '$"//body"',
            ],
            'xpath expression containing double quotes' => [
                'locator' => new ElementIdentifier('//*[@id="id"]'),
                'expectedString' => '$"//*[@id=\"id\"]"',
            ],
            'css selector with ordinal position' => [
                'locator' => new ElementIdentifier('.selector', 3),
                'expectedString' => '$".selector":3',
            ],
            'css selector with parent' => [
                'locator' => (new ElementIdentifier('.child'))
                    ->withParentIdentifier(
                        new ElementIdentifier('.parent')
                    ),
                'expectedString' => '$".parent" >> $".child"',
            ],
            'css selector with parent and grandparent' => [
                'locator' => (new ElementIdentifier('.child'))
                    ->withParentIdentifier(
                        (new ElementIdentifier('.parent'))
                            ->withParentIdentifier(
                                new ElementIdentifier('.grandparent')
                            )
                    ),
                'expectedString' => '$".grandparent" >> $".parent" >> $".child"',
            ],
        ];
    }

    public function testJsonSerialize(): void
    {
        $this->assertSame(
            [
                Serializer::KEY_LOCATOR => '.selector',
            ],
            (new ElementIdentifier('.selector'))->jsonSerialize()
        );
    }

    public function testFromJson(): void
    {
        $this->assertEquals(
            new ElementIdentifier('.selector'),
            ElementIdentifier::fromJson((string) json_encode([
                Serializer::KEY_LOCATOR => '.selector',
            ]))
        );
    }

    /**
     * @dataProvider fromAttributeIdentifierRemainsUnchangedDataProvider
     */
    public function testFromAttributeIdentifierRemainsUnchanged(ElementIdentifierInterface $identifier): void
    {
        $this->assertEquals($identifier, ElementIdentifier::fromAttributeIdentifier($identifier));
    }

    /**
     * @return array<mixed>
     */
    public function fromAttributeIdentifierRemainsUnchangedDataProvider(): array
    {
        return [
            'element identifier, no ordinal position, no parent' => [
                'identifier' => new ElementIdentifier('.selector'),
            ],
            'element identifier, has ordinal position, no parent' => [
                'identifier' => new ElementIdentifier('.selector', 3),
            ],
            'element identifier, no ordinal position, has parent with no ordinal position and no parents' => [
                'identifier' => (new ElementIdentifier('.child'))
                    ->withParentIdentifier(
                        new ElementIdentifier('.parent')
                    ),
            ],
            'element identifier, has ordinal position, has parent with ordinal position and no parents' => [
                'identifier' => (new ElementIdentifier('.child', 3))
                    ->withParentIdentifier(
                        new ElementIdentifier('.parent', 4)
                    ),
            ],
            'element identifier, has ordinal position, has parent and grandparent with oridinal position' => [
                'identifier' => (new ElementIdentifier('.child', 3))
                    ->withParentIdentifier(
                        (new ElementIdentifier('.parent', 4))
                            ->withParentIdentifier(
                                new ElementIdentifier('.grandparent', 5)
                            )
                    ),
            ],
        ];
    }

    /**
     * @dataProvider fromAttributeIdentifierIsChangedDataProvider
     */
    public function testFromAttributeIdentifierIsChanged(
        ElementIdentifierInterface $identifier,
        ElementIdentifier $expectedIdentifier
    ): void {
        $this->assertEquals($expectedIdentifier, ElementIdentifier::fromAttributeIdentifier($identifier));
    }

    /**
     * @return array<mixed>
     */
    public function fromAttributeIdentifierIsChangedDataProvider(): array
    {
        return [
            'attribute identifier with no ordinal position, no parent' => [
                'identifier' => new AttributeIdentifier('.selector', 'attribute_name'),
                'expectedIdentifier' => new ElementIdentifier('.selector'),
            ],
            'attribute identifier has ordinal position, no parent' => [
                'identifier' => new AttributeIdentifier('.selector', 'attribute_name', 2),
                'expectedIdentifier' => new ElementIdentifier('.selector', 2),
            ],
            'attribute identifier has ordinal position, has element identifier parent with no ordinal position' => [
                'identifier' => (new AttributeIdentifier('.child', 'child_attr', 3))
                    ->withParentIdentifier(
                        new ElementIdentifier('.parent')
                    ),
                'expectedIdentifier' => (new ElementIdentifier('.child', 3))
                    ->withParentIdentifier(
                        new ElementIdentifier('.parent')
                    ),
            ],
            'attribute identifier has ordinal position, has element identifier parent no ordinal position' => [
                'identifier' => (new AttributeIdentifier('.child', 'child_attr', 4))
                    ->withParentIdentifier(
                        new ElementIdentifier('.parent', 5)
                    ),
                'expectedIdentifier' => (new ElementIdentifier('.child', 4))
                    ->withParentIdentifier(
                        new ElementIdentifier('.parent', 5)
                    ),
            ],
            'attribute identifier, has element identifier parent with attribute identifier grandparent' => [
                'identifier' => (new AttributeIdentifier('.child', 'child_attr'))
                    ->withParentIdentifier(
                        (new ElementIdentifier('.parent'))
                            ->withParentIdentifier(
                                new AttributeIdentifier('.grandparent', 'grandparent_attr')
                            )
                    ),
                'expectedIdentifier' => (new ElementIdentifier('.child'))
                    ->withParentIdentifier(
                        (new ElementIdentifier('.parent'))
                            ->withParentIdentifier(
                                new ElementIdentifier('.grandparent')
                            )
                    ),
            ],
            'attribute identifier, has attribute identifier parent with attribute identifier grandparent' => [
                'identifier' => (new AttributeIdentifier('.child', 'child_attr'))
                    ->withParentIdentifier(
                        (new AttributeIdentifier('.parent', 'parent_attr'))
                            ->withParentIdentifier(
                                new AttributeIdentifier('.grandparent', 'grandparent_attr')
                            )
                    ),
                'expectedIdentifier' => (new ElementIdentifier('.child'))
                    ->withParentIdentifier(
                        (new ElementIdentifier('.parent'))
                            ->withParentIdentifier(
                                new ElementIdentifier('.grandparent')
                            )
                    ),
            ],
        ];
    }
}
