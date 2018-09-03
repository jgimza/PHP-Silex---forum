<?php
/**
 * Topic add form.
 */

namespace Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ForumType.
 */

class ForumType extends AbstractType
{
    /**
     * {@inheritdoc}
     */

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'nameTopic',
            TextType::class,
            [
                'label' => 'Nazwa tematu',
                'required' => true,
                'attr' => [
                    'max_length' => 10000,

                ],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(
                        [
                            'max' => 10000,
                        ]
                    ),
                ],
            ]
        );

        $builder->add(
            'content',
            TextareaType::class,
            [
                'label' => 'Post',
                'required' => false,
                'attr' => [
                    'max_length' => 10000,

                ],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(
                        [
                            'max' => 10000,
                        ]
                    ),
                ],
            ]
        );
    }
    /**
     * {@inheritdoc}
     */

    public function getBlockPrefix()
    {
        return 'content_type';
    }
}