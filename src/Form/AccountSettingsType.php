<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/** @extends AbstractType<User> */
final class AccountSettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('fullName', TextType::class, [
                'label' => 'account.field.full_name',
            ])
            ->add('email', EmailType::class, [
                'label' => 'account.field.email',
                'disabled' => true,
                'help' => 'account.field.email_help',
            ])
            ->add('locale', ChoiceType::class, [
                'label' => 'account.field.language',
                'choices' => [
                    'language.ukrainian' => 'uk',
                    'language.english' => 'en',
                ],
                'choice_translation_domain' => 'messages',
                'expanded' => true,
                'multiple' => false,
                'attr' => ['class' => 'cars-locale-radios'],
                'row_attr' => ['class' => 'cars-locale-field'],
            ])
            ->add('currentPassword', PasswordType::class, [
                'label' => 'account.field.current_password',
                'mapped' => false,
                'required' => false,
                'attr' => ['autocomplete' => 'current-password'],
            ])
            ->add('newPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'required' => false,
                'first_options' => [
                    'label' => 'account.field.new_password',
                    'attr' => ['autocomplete' => 'new-password'],
                ],
                'second_options' => [
                    'label' => 'account.field.confirm_password',
                    'attr' => ['autocomplete' => 'new-password'],
                ],
                'invalid_message' => 'account.password_mismatch',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'translation_domain' => 'messages',
        ]);
    }
}
