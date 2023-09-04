<?php

namespace App\Form;

use App\Entity\Caisse;
use App\Entity\Devises;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CaisseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('devises', EntityType::class, [
            'class' => Devises::class,
            'choice_label' => 'code',
            'label' => 'Code devise',
            'placeholder' => 'Sélectionner une devise',

            'label_attr' => [
                'class' => 'col-sm-1 control-label no-padding-right'
            ]
            
            ])
           
                ->add('libelle')
                ->add('solde')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Caisse::class,
        ]);
    }
}
