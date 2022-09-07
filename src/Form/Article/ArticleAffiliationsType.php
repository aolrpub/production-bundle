<?php

namespace Aolr\ProductionBundle\Form\Article;

use Aolr\ProductionBundle\Entity\Article;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArticleAffiliationsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('affiliations', CollectionType::class, [
                'label' => 'Affiliations',
                'entry_type' => AffiliationType::class,
                'entry_options' => [],
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'attr' => ['class' => 'article-affiliations']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        return $resolver->setDefaults([
            'data_class' => Article::class
        ]);
    }
}
