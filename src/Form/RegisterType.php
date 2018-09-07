<?php
/**
 * Register form.
 */

namespace Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Validator\Constraints as CustomAssert;

/**
 * Class RegisterType.
 */

class RegisterType extends AbstractType
{

    /**
     * Build register form.
     *
     * @param FormBuilderInterface $builder
     * @param array $options
     */

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'username',
            TextType::class,
            [
                'label' => 'Login',
                'required' => true,
                'attr' => [
                    'max_length' => 32,

                ],
                'constraints' => [
                    new CustomAssert\UniqueLogin(
                        [
                            'repository' => $options['user_repository'],
                        ]
                    ),
                    new Assert\NotBlank(),
                    new Assert\Length(
                        [
                            'min' => 3,
                            'max' => 32,
                        ]
                    ),
                ],
            ]
        );

        $builder->add(
            'password',
            RepeatedType::class,
            [
                'type' => PasswordType::class,
                'first_options'  => array('label' => 'Hasło'),
                'second_options' => array('label' => 'Powtórz hasło'),
                'required' => true,
                'attr' => [
                    'max_length' => 32,

                ],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(
                        [
                            'min' => 3,
                            'max' => 32,
                        ]
                    ),
                ],
            ]
        );

        $builder->add(
            'name',
            TextType::class,
            [
                'label' => 'Imię',
                'required' => true,
                'attr' => [
                    'max_length' => 64,

                ],
                'constraints' => [
                    new CustomAssert\UniqueLogin(
                        [
                            'repository' => $options['user_repository'],
                        ]
                    ),
                    new Assert\NotBlank(),
                    new Assert\Length(
                        [
                            'min' => 3,
                            'max' => 64,
                        ]
                    ),
                ],
            ]
        );

        $builder->add(
            'surname',
            TextType::class,
            [
                'label' => 'Nazwisko',
                'required' => true,
                'attr' => [
                    'max_length' => 64,

                ],
                'constraints' => [
                    new CustomAssert\UniqueLogin(
                        [
                            'repository' => $options['user_repository'],
                        ]
                    ),
                    new Assert\NotBlank(),
                    new Assert\Length(
                        [
                            'min' => 3,
                            'max' => 64,
                        ]
                    ),
                ],
            ]
        );

        $builder->add(
            'email',
            EmailType::class,
            [
                'label' => 'Email',
                'required' => true,
                'attr' => [
                    'max_length' => 64,

                ],
                'constraints' => [
                    new CustomAssert\UniqueLogin(
                        [
                            'repository' => $options['user_repository'],
                        ]
                    ),
                    new Assert\NotBlank(),
                    new Assert\Length(
                        [
                            'min' => 3,
                            'max' => 64,
                        ]
                    ),
                ],
            ]
        );

        $builder->add(
            'birthdate',
            DateType::class,
            [
                'label' => 'Data urodzenia',
                'required' => true,
                'days' => range(1,31),
                'months' => range(1,12),
                'years' => range(1940,2018),
                'attr' => [
                    'max_length' => 32,
                ],
            ]
        );

    }

    /**
     * Configure options.
     *
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'user_repository' => null,
            ]
        );
    }

    /**
     * Get block prefix.
     *
     * @return string
     */

    public function getBlockPrefix()
    {
        return 'register_type';
    }
}