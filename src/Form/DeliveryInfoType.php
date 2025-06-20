<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DeliveryInfoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'firstName',
                TextType::class,
                [
                    'label' => 'Prénom',
                    'required' => true,
                    'attr' => ['class' => 'w-full p-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500'],
                ]
            )
            ->add(
                'lastName',
                TextType::class,
                [
                    'label' => 'Nom',
                    'required' => true,
                    'attr' => ['class' => 'w-full p-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500'],
                ]
            )
            ->add(
                'email',
                EmailType::class,
                [
                    'label' => 'Email',
                    'required' => true,
                    'attr' => [
                        'class' => 'w-full p-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500',
                    ],
                ]
            )
            ->add(
                'address',
                TextType::class,
                [
                    'label' => 'Adresse',
                    'required' => true,
                    'attr' => ['class' => 'w-full p-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500'],
                ]
            )
            ->add(
                'postalCode',
                TextType::class,
                [
                    'label' => 'Code Postal',
                    'required' => true,
                    'attr' => [
                        'class' => 'w-full p-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500',
                        'pattern' => '\d{5}',
                    ],
                ]
            )
            ->add(
                'city',
                TextType::class,
                [
                    'label' => 'Ville',
                    'required' => true,
                    'attr' => ['class' => 'w-full p-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500'],
                ]
            )
            ->add(
                'country',
                CountryType::class,
                [
                    'label' => 'Pays',
                    'required' => true,
                    'attr' => ['class' => 'w-full p-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500'],
                ]
            )
            ->add(
                'phone',
                TelType::class,
                [
                    'label' => 'Téléphone',
                    'required' => true,
                    'attr' => [
                        'class' => 'w-full p-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500',
                        'placeholder' => '06 12 34 56 78',
                        'pattern' => '^(?:\d{2}\s?){5}$',
                        'inputmode' => 'tel',
                    ],
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'delivery_info',
            'data_class' => null,
            'validation_groups' => ['Default'],
        ]);
    }
}
