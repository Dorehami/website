<?php

namespace App\Form;

use App\Entity\Post;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Url;

class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'عنوان',
                'attr' => [
                    'placeholder' => 'عنوان مقاله رو وارد کن',
                    'class' => 'w-full px-3 py-2 placeholder-gray-400 border border-gray-300 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500'
                ],
                'row_attr' => [
                    'class' => 'mb-4'
                ],
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new Length(
                        min: 5,
                        max: 140,
                        minMessage: 'عنوان باید حداقل ۵ کاراکتر داشته باشه',
                        maxMessage: 'عنوان میتونه حداکثر ۱۴۰ کاراکتر داشته باشه'
                    ),
                ]
            ])
            ->add('url', TextType::class, [
                'label' => 'آدرس',
                'attr' => [
                    'placeholder' => 'آدرس اینترنتی مقاله رو وارد کن',
                    'class' => 'w-full px-3 py-2 placeholder-gray-400 border border-gray-300 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500'
                ],
                'row_attr' => [
                    'class' => 'mb-4'
                ],
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new Length(
                        min: 10,
                        max: 255,
                        minMessage: 'آدرس باید حداقل ۱۰ کاراکتر داشته باشه',
                        maxMessage: 'آدرس میتونه نهایتا ۲۵۵ کاراکتر داشته باشه'
                    ),
                    new Url([
                        'protocols' => ['http', 'https'],
                        'message' => 'مقدار وارد شده، یه آدرس اینترنتی معتبر نیست',
                        'requireTld' => true,
                        'tldMessage' => 'دامنه باید یه tld معتبر داشته باشه',
                    ])
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'توضیحات',
                'attr' => [
                    'placeholder' => 'توضیحات بیشتری برای خواننده بنویس (اجباری نیست)',
                    'class' => 'w-full px-3 py-2 placeholder-gray-400 border border-gray-300 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500'
                ],
                'row_attr' => [
                    'class' => 'mb-4'
                ],
                'constraints' => [
                    new Length(
                        min: 0,
                        max: 255,
                    ),
                ],
                'required' => false,
            ])
            ->add('domainIsPersonal', CheckboxType::class, [
                'label' => 'دامنه به شخص تعلق داره؟',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
        ]);
    }
}
