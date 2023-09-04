<?php

namespace App\Form;

use App\Entity\Devises;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class DevisesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder

            ->add('image', FileType::class, [
                'required' => false,
                'mapped'=> false,

                'label' => 'Image',
                'attr' => [ 'class' => 'form-control', 'style' => 'width: 600px;'],
                'label_attr' => [
                    'class' => 'col-sm-1 control-label no-padding-right'
                ]
            ])
            ->add('code', null, [
                'label' => 'Code',
                'attr' => ['maxlength' => 30, 'class' => 'form-control', 'style' => 'width: 600px;'],
                'label_attr' => [
                    'class' => 'col-sm-1 control-label no-padding-right'
                ]
            ])
            ->add('libelle', null, [
                'label' => 'Libellé',
                'attr' => ['maxlength' => 30, 'class' => 'form-control', 'style' => 'width: 600px;'],
                'label_attr' => [
                    'class' => 'col-sm-1 control-label no-padding-right'
                ]
            ])
            ->add('abreviation', null, [
                'label' => 'Abréviation',
                'attr' => ['maxlength' => 30, 'class' => 'form-control', 'style' => 'width: 600px;'],
                'label_attr' => [
                    'class' => 'col-sm-1 control-label no-padding-right'
                ]
            ])
            ->add('unite', null, [
                'label' => 'Unité',
                'attr' => ['maxlength' => 30, 'class' => 'form-control', 'style' => 'width: 600px;'],
                'label_attr' => [
                    'class' => 'col-sm-1 control-label no-padding-right'
                ]
            ])

            ->getForm();
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Devises::class,

        ]);
    }
}
