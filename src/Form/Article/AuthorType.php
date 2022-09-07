<?php

namespace Aolr\ProductionBundle\Form\Article;

use Aolr\ProductionBundle\Entity\Author;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AuthorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('givenName', TextType::class, [
                'label' => 'Given Name'
            ])
            ->add('surname', TextType::class, [
                'label' => 'Surname'
            ])
            ->add('orcid', TextType::class, [
                'label' => 'Orcid',
                'required' => false
            ])
            ->add('affs', TextType::class, [
                'label' => 'Affiliations',
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Author::class
        ]);
    }
}
