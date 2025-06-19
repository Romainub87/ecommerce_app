<?php

declare(strict_types = 1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BankCardForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'card_number', TextType::class, [
                'label' => 'Numéro de carte',
                'attr' => ['inputmode' => 'numeric', 'pattern' => '[0-9\s]{13,19}', 'maxlength' => 19, 'class' => 'block w-full rounded border-gray-300 focus:border-blue-500 focus:outline-none']
                ]
            )
            ->add(
                'expiry_date', TextType::class, [
                'label' => 'Date d\'expiration (MM/AA)',
                'attr' => ['inputmode' => 'numeric', 'pattern' => '(0[1-9]|1[0-2])\/?([0-9]{2})', 'maxlength' => 5, 'placeholder' => 'MM/AA', 'class' => 'block w-full rounded border-gray-300 focus:border-blue-500 focus:ring-blue-500']
                ]
            )
            ->add(
                'cardholder', TextType::class, [
                'label' => 'Titulaire',
                'attr' => ['autocomplete' => 'cc-name', 'class' => 'block w-full rounded border-gray-300 focus:border-blue-500 focus:ring-blue-500']
                ]
            )
            ->add(
                'cvv', PasswordType::class, [
                'label' => 'Cryptogramme visuel',
                'attr' => ['inputmode' => 'numeric', 'pattern' => '[0-9]{3,4}', 'maxlength' => 4, 'autocomplete' => 'cc-csc', 'class' => 'block w-full rounded border-gray-300 focus:border-blue-500 focus:ring-blue-500']
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
            // Configure your form options here
            ]
        );
    }
}
