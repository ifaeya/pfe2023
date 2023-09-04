<?php

namespace App\Form;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Reservation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Document; 
use App\Entity\Devises;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ReservationType extends AbstractType
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
            ->add('montant', null, [
                'label' => 'Montant',
                'attr' => ['maxlength' => 20, 'class' => 'form-control custom-margin', 'style' => 'width: 600px;'],
                'label_attr' => [
                    'class' => 'col-sm-1 control-label no-padding-right'
                ]   
            ])
            ->add('documents', FileType::class, [
                'label' => 'Importer le fichier',
                'multiple' => true, // Permet de sélectionner plusieurs fichiers
                'attr' => [
                    'accept' => '.pdf,.jpg,.png', // Accepte les fichiers PDF, JPG et PNG
                    'style' => 'display: none;', // Masque le champ par défaut
                ],
                'mapped' => false, // Ne pas mapper ce champ à l'entité Document
            ])
            ->add('reserver', SubmitType::class, [ // Utilisation de SubmitType pour créer un bouton
                'label' => 'Réserver', // Étiquette du bouton
                'attr' => [
                    'class' => 'btn btn-info pull-right' // Classe CSS pour le style
                ]
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
        ]);
    }
}
