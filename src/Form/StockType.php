<?php

namespace App\Form;

use App\Entity\Caisse;
use App\Entity\Devises;
use App\Entity\Stock;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StockType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('devise', EntityType::class, [
            'class' => Devises::class,
            'choice_label' => 'code',
            'label' => 'Code devise',
            'placeholder' => 'Sélectionner une devise',

            'label_attr' => [
                'class' => 'col-sm-1 control-label no-padding-right'
            ]

            ])
            ->add('caisse', EntityType::class, [
                'class' => Caisse::class,
                'choice_label' => 'libelle',
                'label' => 'Caisse',
                'placeholder' => 'Sélectionner une caisse',

                'label_attr' => [
                    'class' => 'col-sm-1 control-label no-padding-right'
                ]

                ])
            ->add('name', null, [
                'label' => 'Nom',
                'attr' => ['maxlength' => 30, 'class' => 'form-control', 'style' => 'width: 600px;'],
                'label_attr' => [
                    'class' => 'col-sm-1 control-label no-padding-right'
                ]
            ])
            ->add('quantity', null, [
                'label' => 'Quantité',
                'attr' => ['maxlength' => 30, 'class' => 'form-control', 'style' => 'width: 600px;'],
                'label_attr' => [
                    'class' => 'col-sm-1 control-label no-padding-right'
                ]
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Stock::class,
        ]);
    }
}
