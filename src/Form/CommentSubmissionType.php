<?php

namespace App\Form;

use App\Entity\Comment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommentSubmissionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('content', TextAreaType::class, [
                'label' => 'دیدگاه شما',
                'attr' => [
                    'placeholder' => 'نظرتون رو راجع به این پست بنویسید',
                    'class' => 'form-textarea w-full placeholder:italic',
                ],
                'row_attr' => [
                    'class' => 'w-full mb-5',
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Comment::class,
        ]);
    }
}
