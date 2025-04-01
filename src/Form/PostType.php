<?php

namespace App\Form;

use App\Entity\Post;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'عنوان',
                'attr' => [
                    'placeholder' => 'عنوان مقاله را وارد کنید',
                    'class' => 'w-full px-3 py-2 placeholder-gray-400 border border-gray-300 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500'
                ],
                'row_attr' => [
                    'class' => 'mb-4'
                ]
            ])
            ->add('url', UrlType::class, [
                'label' => 'آدرس',
                'attr' => [
                    'placeholder' => 'آدرس اینترنتی مقاله را وارد کنید',
                    'class' => 'w-full px-3 py-2 placeholder-gray-400 border border-gray-300 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500'
                ],
                'row_attr' => [
                    'class' => 'mb-4'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
        ]);
    }
}
