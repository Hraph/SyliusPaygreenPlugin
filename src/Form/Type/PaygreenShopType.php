<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\Form\Type;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Url;

class PaygreenShopType extends AbstractType
{

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('internalId', TextType::class, [
                'label' => 'sylius.ui.id',
                'disabled' => true,
                'constraints' => [
                    new NotBlank(),
                    new NotNull()
                ]
            ])
            ->add('url', TextType::class, [
                'label' => 'hraph_sylius_paygreen_plugin.form.url',
                'constraints' => [
                    new NotBlank(),
                    new Url()
                ]
            ])
            ->add('businessIdentifier', TextType::class, [
                'label' => 'hraph_sylius_paygreen_plugin.form.business_identifier',
                'constraints' => [
                    new NotBlank(),
                    new NotNull(),
                    new Length([
                        'min' => 14,
                        'max' => 17,
                        'allowEmptyString' => false,
                    ]),
                ]
            ])
            ->add('name', TextType::class, [
                'label' => 'hraph_sylius_paygreen_plugin.form.name',
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('description', TextType::class, [
                'label' => 'hraph_sylius_paygreen_plugin.form.description',
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('companyType', ChoiceType::class, [
                'label' => 'hraph_sylius_paygreen_plugin.form.company_type',
                'choices' => [
                    'eieirl' => 'EIEIRL',
                    'crafts' => 'CRAFTS',
                    'liberal' => 'LIBERAL',
                    'auto_enter' => 'AUTO_ENTER',
                    'company' => 'COMPANY',
                    'marketing' => 'MARKETING',
                    'accountant' => 'ACCOUNTANT',
                    'guesthouse' => 'GUESTHOUSE',
                    'charitable_org' => 'CHARITABLE_ORG',
                    'public_org' => 'PUBLIC_ORG',
                ],
                'choice_label' => function ($choice, $key, $value) {
                    return 'hraph_sylius_paygreen_plugin.form.company_types.' . $key;
                },
                'constraints' => [
                    new NotBlank(),
                    new NotNull(),
                ]
            ]);
    }
}
