<?php

namespace Aolr\ProductionBundle\Form\Article;

use Aolr\ProductionBundle\Entity\Permission;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArticlePermissionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('year', TextType::class, [
                'label' => 'Copyright Year'
            ])
            ->add('statement', TextType::class, [
                'label' => 'Copyright Statement'
            ])
            ->add('licenseType', ChoiceType::class, [
                'label' => 'License Type',
                'choices' => ['open-access' => 'open-access'],
                'required' => false,
                'placeholder' => 'select'
            ])
            ->add('licenseContents', CollectionType::class, [
                'label' => 'License Contents',
                'entry_type' => TextareaType::class,
                'entry_options' => ['required' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'attr' => ['class' => 'permission-license row']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Permission::class
        ]);
    }
}
