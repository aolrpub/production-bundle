<?php

namespace Aolr\ProductionBundle\Form\Article;

use Aolr\ProductionBundle\Entity\Article;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BaseInfoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('journal', JournalType::class, [
                'label' => 'Journal Detail',
            ])
            ->add('id', TextType::class, [
                'label' => 'Publication ID'
            ])
            ->add('title', TextType::class, [
                'label' => 'Article Title',
            ])
            ->add('type', TextType::class, [
                'label' => 'Article Type',
            ])
            ->add('doi', TextType::class, [
                'label' => 'Doi'
            ])
            ->add('abstract', TextAreaType::class, [
                'label' => 'Abstract',
                'required' => false,
                'attr' => ['rows' => 5]
            ])
            ->add('keywords', TextType::class, [
                'label' => 'Keywords',
                'required' => false
            ])
            ->add('publishedDate', DateType::class, [
                'label' => 'Published Date(epub)',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'html5' => false,
                'attr' => ['class' => 'js-datepicker']
            ])
            ->add('printDate', DateType::class, [
                'label' => 'Published Date(print)',
                'required' => false,
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'html5' => false,
                'attr' => ['class' => 'js-datepicker']
            ])
            ->add('volume', TextType::class, [
                'label' => 'Volume'
            ])
            ->add('issue', TextType::class, [
                'label' => 'Issue'
            ])
            ->add('number', TextType::class, [
                'label' => 'E-location Id',
                'required' => false
            ])
            ->add('receivedDate', DateType::class, [
                'label' => 'Received Date',
                'required' => false,
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'html5' => false,
                'attr' => ['class' => 'js-datepicker']
            ])
            ->add('acceptedDate', DateType::class, [
                'label' => 'Accepted Date',
                'required' => false,
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'html5' => false,
                'attr' => ['class' => 'js-datepicker']
            ])
            ->add('permission', ArticlePermissionType::class, [
                'label' => 'Permission',
            ])

            ->add('footnotes', CollectionType::class, [
                'label' => 'Footnotes',
                'entry_type' => TextareaType::class,
                'entry_options' => [],
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'attr' => ['class' => 'row']
            ])
        ;

        $builder->get('keywords')
            ->resetViewTransformers()
            ->addModelTransformer(new CallbackTransformer(
                function (array $keywords = []) {
                    return implode('; ', $keywords);
                },
                function (string $keywordsString = '') {
                    return explode('; ', $keywordsString);
                }
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Article::class
        ]);
    }
}
