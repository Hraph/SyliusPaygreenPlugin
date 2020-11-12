<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class PaygreenConfigurationType extends AbstractType
{
    private bool $forceUseAuthorize = false;

    /**
     * PaygreenConfigurationType constructor.
     * @param bool $forceUseAuthorize
     */
    public function __construct(bool $forceUseAuthorize)
    {
        $this->forceUseAuthorize = $forceUseAuthorize;
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if (!$this->forceUseAuthorize) {
            $builder
                ->add('use_authorize', CheckboxType::class, [
                    'label' => 'hraph_sylius_paygreen_plugin.form.gateway.authorize',
                    'data' => false
                ]);
        }
        else {
            $builder
                ->add('use_authorize', HiddenType::class, [
                    'data' => true
                ]);
        }
    }
}
