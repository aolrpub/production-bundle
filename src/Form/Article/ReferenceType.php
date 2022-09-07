<?php

namespace Aolr\ProductionBundle\Form\Article;

use Aolr\ProductionBundle\Entity\Author;
use Aolr\ProductionBundle\Entity\Reference;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReferenceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', HiddenType::class, [
                'label' => 'ID'
            ])
            ->add('publicationType', ChoiceType::class, [
                'label' => 'Publication Type',
                'choices' => array_combine(Reference::$publicationTypes, Reference::$publicationTypes),
                'placeholder' => 'select'
            ])
            ->add('articleTitle', TextType::class, [
                'label' => 'Article Title',
            ])
            ->add('source', TextType::class, [
                'label' => 'Source',
            ])
            ->add('year', TextType::class, [
                'label' => 'Year',
            ])
            ->add('volume', TextType::class, [
                'label' => 'Volume',
                'required' => false
            ])
            ->add('fPage', TextType::class, [
                'label' => 'First Page',
                'required' => false
            ])
            ->add('lPage', TextType::class, [
                'label' => 'Last Page',
                'required' => false
            ])
            ->add('location', TextType::class, [
                'label' => 'Publisher Location',
                'required' => false
            ])
            ->add('publisher', TextType::class, [
                'label' => 'Publisher Name',
                'required' => false
            ])
            ->add('doi', TextType::class, [
                'label' => 'doi',
                'required' => false
            ])
            ->add('persons', CollectionType::class, [
                'label' => 'Authors',
                'entry_type' => TextType::class,
                'entry_options' => [
                    'label' => 'Name'
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'attr' => ['class' => 'reference-persons row']
            ])
            ->add('editors', CollectionType::class, [
                'label' => 'Editors',
                'entry_type' => TextType::class,
                'entry_options' => [
                    'label' => 'Name'
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'attr' => ['class' => 'reference-persons row'],
                'required' => false
            ])
        ;

        $callbackTransformer = new CallbackTransformer(
            function(ArrayCollection $persons) {
                return $persons->map(function ($person) {
                    return $person->getName();
                });
            },
            function(ArrayCollection $persons) {
                return $persons->map(function ($personsName) {
                    $person = new Author();
                    if (preg_match('/^(.*?)\s+(.*?)$/', $personsName, $matches)) {
                        $person->setSurname($matches[1]);
                        $person->setGivenName($matches[2]);
                    } else {
                        $person->setSurname($personsName);
                    }
                    return $person;
                });
            }
        );
        $builder->get('persons')
            ->resetViewTransformers()
            ->addModelTransformer($callbackTransformer)
        ;

        $builder->get('editors')
            ->resetViewTransformers()
            ->addModelTransformer($callbackTransformer)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Reference::class
        ]);
    }
}
