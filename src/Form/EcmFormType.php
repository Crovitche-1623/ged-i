<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class EcmFormType extends AbstractType
{
    /**
     * {@inheritDoc}
     */
    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    ): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'attr' => [
                    'autofocus' => true
                ],
                'label' => 'Prénom',
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom'
            ])
            ->add('isMale', ChoiceType::class, [
                'choices' => [
                    'Monsieur' => true,
                    'Madame' => false
                ],
                'expanded' => true,
                'label' => 'Sexe',
                'multiple' => false,
            ])
            ->add('birthDate', DateType::class, [
                'label' => 'Date de naissance',
            ])
            ->add('fileType', ChoiceType::class, [
                'choices' => [
                    'Dossier de candidature' => 1,
                    'Contrat de travail' => 2,
                    'Fiche de salaire' => 3,
                    'Document personnel' => 4,
                    'Evaluation' => 5,
                    'Cahier de charge' => 6,
                    "Formule personnel d'entrée" => 7,
                    "Formule de modification personnelle" => 8,
                    'Certificat médical' => 9,
                    'Lettre de démission' => 10,
                    'Remarque interne' => 11,
                    'Certificat de travail' => 12
                ],
                'expanded' => false,
                'label' => 'Type de document',
                'multiple' => false,
            ])
            ->add('file', FileType::class, [
                'label' => 'Fichier'
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Envoyer un fichier à Doc.ECM'
            ])
        ;
    }
}
