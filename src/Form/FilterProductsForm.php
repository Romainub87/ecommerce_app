<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterProductsForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'name',
                TextType::class,
                [
                    'required' => false,
                    'label' => 'Nom du produit',
                    'attr' => ['class' => 'block mb-2 focus:outline-none border-b mt-1', 'placeholder' => 'Rechercher un produit...'],
                ]
            )
            ->add(
                'category',
                ChoiceType::class,
                [
                    'required' => false,
                    'choices' => $options['categories'],
                    'placeholder' => 'Toutes les catégories',
                    'label' => 'Catégorie',
                    'attr' => ['class' => 'block mb-2 focus:outline-none border-b mt-1'],
                ]
            )
            ->add(
                'minPrice',
                NumberType::class,
                [
                    'required' => false,
                    'label' => 'Prix minimum',
                    'attr' => ['class' => 'block mb-2 focus:outline-none border-b mt-1', 'placeholder' => '0'],
                ]
            )
            ->add(
                'maxPrice',
                NumberType::class,
                [
                    'required' => false,
                    'label' => 'Prix maximum',
                    'attr' => ['class' => 'block mb-2 focus:outline-none border-b mt-1', 'placeholder' => '1000'],
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'categories' => [],
            ]
        );
    }
}
