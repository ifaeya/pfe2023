<?php

namespace App\Form;

use App\Entity\Coursdevises;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Devises;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class CoursdevisesType extends AbstractType
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
           
        ->add('valeurachat', null, [
            'label' => 'Achat',
            'attr' => ['maxlength' => 20, 'class' => 'form-control custom-margin', 'style' => 'width: 600px;'],
            'label_attr' => [
                'class' => 'col-sm-1 control-label no-padding-right'
            ]   
        ])
        ->add('valeurvente', null, [
            'label' => 'Vente',
            'attr' => ['maxlength' => 30, 'class' => 'form-control custom-margin', 'style' => 'width: 600px;'],
            'label_attr' => [
                'class' => 'col-sm-1 control-label no-padding-right'
            ]   
        ])
      
        ->getForm();

        
       
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Coursdevises::class,
        ]);
    }
}
