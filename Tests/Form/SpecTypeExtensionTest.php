<?php

namespace Tests\Form;

use Symfony\Bridge\RulerZ\Form\SpecificationToBooleanTransformer;
use Symfony\Bridge\RulerZ\Form\SpecificationToStringTransformer;
use Symfony\Bridge\RulerZ\Form\SpecTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Forms;
use Symfony\Component\Form\Test\FormIntegrationTestCase;

// BC layer for SF 3.*
if (!class_exists(\PHPUnit_Framework_TestCase::class)) {
    class_alias(\PHPUnit\Framework\TestCase::class, \PHPUnit_Framework_TestCase::class);
}

class SpecTypeExtensionTest extends FormIntegrationTestCase
{
    protected function getTypeExtensions()
    {
        return [
            new SpecTypeExtension(),
        ];
    }

    protected function setUp(): void
    {
        $this->factory = Forms::createFormFactoryBuilder()
            ->addExtensions($this->getExtensions())
            ->addTypeExtensions($this->getTypeExtensions())
            ->getFormFactory();
    }

    public function testItDoesNotAddAnyTransformerIfNoSpecificationClassIsGiven()
    {
        $form = $this->factory->create(TextType::class, null, [
            'spec_transformer' => 'string',
            'spec_class' => null,
        ]);

        $this->assertEmpty($form->getConfig()->getModelTransformers());
    }

    public function testItAddsStringToSpecTransformer()
    {
        $form = $this->factory->create(TextType::class, null, [
            'spec_transformer' => 'string',
            'spec_class' => '\Some\Specification\Class',
        ]);

        $modelTransformers = $form->getConfig()->getModelTransformers();

        $this->assertCount(1, $modelTransformers);
        $this->assertInstanceOf(SpecificationToStringTransformer::class, $modelTransformers[0]);
    }

    public function testItAddsBooleanToSpecTransformer()
    {
        $form = $this->factory->create(TextType::class, null, [
            'spec_transformer' => 'boolean',
            'spec_class' => '\Some\Specification\Class',
        ]);

        $modelTransformers = $form->getConfig()->getModelTransformers();

        $this->assertCount(1, $modelTransformers);
        $this->assertInstanceOf(SpecificationToBooleanTransformer::class, $modelTransformers[0]);
    }

    /**
     * @expectedException \Symfony\Bridge\RulerZ\Form\Exception\InvalidTransformer
     */
    public function testItFailsIfTheTransformerIsUnknown()
    {
        $this->factory->create(TextType::class, null, [
            'spec_transformer' => 'unknown',
            'spec_class' => '\Some\Specification\Class',
        ]);
    }
}
