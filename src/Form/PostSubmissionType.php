<?php

namespace App\Form;

use App\Entity\Post;
use App\Enum\PostType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Url;

class PostSubmissionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'عنوان',
                'attr' => [
                    'placeholder' => 'عنوان پست رو وارد کن',
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
                    'placeholder' => 'آدرس اینترنتی پست رو وارد کن',
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
            ->add('originalAuthorName', TextType::class, [
                'label' => 'نویسنده',
                'required' => false,
                'attr' => [
                    'placeholder' => 'نام نویسنده‌ی اصلی پست چیه؟ (اجباری نیست)',
                    'class' => 'w-full px-3 py-2 placeholder-gray-400 border border-gray-300 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500'
                ],
                'row_attr' => [
                    'class' => 'mb-4'
                ],
            ])
            ->add('type', EnumType::class, [
                'label' => 'نوع پست',
                'required' => true,
                'class' => PostType::class,
                'choice_label' => fn(PostType $p) => $p->getLabel(),
                'expanded' => true, // This makes it render as radio buttons
                'attr' => [
                    'class' => 'post-type-selector'
                ],
                'row_attr' => [
                    'class' => 'mb-8'
                ],
                'choice_attr' => function(PostType $choice) {
                    return [
                        'data-description' => $choice->getDescription(),
                        'data-icon' => $choice->getIcon(),
                    ];
                },
                'constraints' => [
                    new NotBlank([
                        'message' => 'لطفا نوع پست را انتخاب کنید'
                    ]),
                ]
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
