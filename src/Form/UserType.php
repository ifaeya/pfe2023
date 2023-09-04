<?php

namespace App\Form;

use App\Entity\Societe;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('nom', TextType::class, [
            'label' => 'Nom',
            'attr' => ['class' => 'form-control', 'style' => 'width: 700px;'],
    
        ])
        ->add('prenom', TextType::class, [
            'label' => 'Prénom',
            'attr' => ['class' => 'form-control', 'style' => 'width: 700px;'],
    
        ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => ['class' => 'form-control', 'style' => 'width: 700px;'],
        
            ])
            ->add('roles', ChoiceType::class, [
                'label' => 'Rôles',
                'choices' => [
                    'Utilisateur' => 'ROLE_USER',
                    'Administrateur' => 'ROLE_ADMIN',
                    'Modérateur' => 'ROLE_MODERATEUR',
                   
                ],
                'multiple' => true,
                'expanded' => false,
                'attr' => [
                    'class' => 'form-control',
                    'style' => 'width: 700px;',
                    'data-toggle' => 'dropdown', // ajout de l'attribut "data-toggle"
                ],
            ])
                ->add('usersSociete', EntityType::class, [
                'label' => 'Société',
                'class' => Societe::class,
                'choice_label' => 'raisonsociale', // le champ de la société qui doit être affiché dans le formulaire de sélection
                'multiple' => true, // permet de sélectionner plusieurs sociétés
                'expanded' => false,
                'attr' => [
                    'class' => 'form-control',
                    'style' => 'width: 700px;',
                    'data-toggle' => 'dropdown', // ajout de l'attribut "data-toggle"
                ],
                'placeholder' => 'Sélectionner une société',
                // affiche les options en mode bouton radio
                'by_reference' => false, // important pour que la modification de la relation manyToMany soit effectuée
            ])
           // ->add('password')
           ->add('plainPassword', RepeatedType::class, [
            'type' => PasswordType::class,
            'mapped' => false, // Le champ plainPassword n'est pas directement mappé à l'entité
            'invalid_message' => 'Les mots de passe doivent correspondre.',
            'options' => ['attr' => ['class' => 'form-control', 'style' => 'width: 700px;']],
            'required' => true,
            'first_options' => ['label' => 'Mot de passe'],
            'second_options' => ['label' => 'Répéter le mot de passe'],
        ])
            ->add('isVerified', null, [
                'attr' => ['class' => 'form-check-input'],
                'label_attr' => [
                    'class' => 'col-sm-1 control-label no-padding-left'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
