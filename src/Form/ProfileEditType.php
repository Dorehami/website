<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProfileEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('displayName', TextType::class, [
                'label' => 'نام نمایشی',
                'attr' => [
                    'placeholder' => 'نام نمایشی خود را وارد کنید',
                    'class' => 'form-input text-sm w-full'
                ],
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'لطفا یک نام نمایشی وارد کنید',
                    ]),
                    new Length([
                        'min' => 3,
                        'max' => 50,
                        'minMessage' => 'نام نمایشی باید حداقل {{ limit }} کاراکتر داشته باشد',
                        'maxMessage' => 'نام نمایشی نمی‌تواند بیشتر از {{ limit }} کاراکتر داشته باشد',
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}