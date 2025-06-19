<?php

declare(strict_types = 1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BankTransferForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'IBAN', TextType::class, [
                'label' => 'IBAN',
                'attr' => [
                    'inputmode' => 'text',
                    'pattern' => '[A-Z]{2}[0-9]{2}[A-Z0-9]{1,30}',
                    'maxlength' => 34,
                    'placeholder' => 'Entrez votre IBAN',
                    'class' => 'block w-full border-b focus:outline-none'
                ]
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
