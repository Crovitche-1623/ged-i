<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
            /*
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
            */
            ->add('collaborator', ChoiceType::class, [
                'label' => 'Collaborateur',
                'choices' => $options['collaborators'],
                'expanded' => false,
                'multiple' => false
            ])
            ->add('fileType', ChoiceType::class, [
                'choices' => $options['document_types'],
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

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['collaborators', 'document_types']);
        $resolver->setAllowedTypes('collaborators', 'array');
        $resolver->setAllowedTypes('document_types', 'array');
    }
}
