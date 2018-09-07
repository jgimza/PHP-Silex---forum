<?php
/**
 * Topic add form.
 */

namespace Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class TopicType.
 */

class TopicType extends AbstractType
{

    /**
     * Build add topic form.
     *
     * @param FormBuilderInterface $builder
     * @param array $options
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
    }

    /**
     * Get block prefix.
     *
     * @return string
     */

    public function getBlockPrefix()
    {
        return 'content_type';
    }
}