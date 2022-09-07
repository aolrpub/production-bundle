<?php

namespace Aolr\ProductionBundle\Form\Article;

use Aolr\ProductionBundle\Entity\Article;
use Aolr\ProductionBundle\Entity\Journal;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class JournalType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Journal Title'
            ])

            ->add('abbrevTitle', TextType::class, [
                'label' => 'Journal Abbr Title',
                'required' => false
            ])
            ->add('publisherName', TextType::class, [
                'label' => 'Publisher Name'
            ])
            ->add('eIssn', TextType::class, [
                'label' => 'Electronic Issn',
                'required' => false
            ])
            ->add('issn', TextType::class, [
                'label' => 'Issn',
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Journal::class,
        ]);
    }
}
