<?php

namespace App\Form;

use App\Entity\Flashcard;
use App\Enum\Image;
use App\Form\Type\FlashcardHiddenType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\AtLeastOneOf;
use Symfony\Component\Validator\Constraints\Blank;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\IsNull;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Url;

class FlashcardFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('front', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Length(max: 255)
                ],
                'attr' => [
                    'data-ajax-images-target' => 'flashcardFront'
                ]
            ])
            ->add('back', FlashcardHiddenType::class, [
                'constraints' => [
                    new NotBlank(message: "You have to choose an image or a gif"),
                    new Length(max: 255),
                    new Url()
                ],
                'attr' => [
                    'data-ajax-images-target' => "flashcardBack",
                ]
            ])
            ->add('imageType', HiddenType::class, [
                'constraints' => [
                    new Choice(callback: [Image::class, 'values']),
                    new NotBlank()
                ],
                'attr' => [
                    'data-ajax-images-target' => 'flashcardImageType',
                ]
            ])
            ->add('searchGifs', ButtonType::class, [
                'attr' => [
                    'data-action' => 'ajax-images#searchGifs',
                    "class" => "button "
                ]
            ])
            ->add('searchImages', ButtonType::class, [
                'attr' => [
                    'data-action' => 'ajax-images#searchImages',
                    "class" => "button"
                ]
            ])
            ->add('deleteFlashcard', ButtonType::class, [
                'attr' => [
                    'data-action' => 'ajax-images#deleteFlashcard',
                    "class" => "button"
                ]
            ])
            ->add("authorName", HiddenType::class, [
                "label" => false,
                'attr' => [
                    'data-ajax-images-target' => 'authorName',

                ],
            ])
            ->add("authorProfileUrl", HiddenType::class, [
                "label" => false,
                "required" => false,
                'attr' => [
                    'data-ajax-images-target' => 'authorProfileUrl',
                ],
                "constraints" => [
                    new AtLeastOneOf([
                        new Url(),
                        new IsNull(),
                        new Blank()
                    ])
                ]
            ])


        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Flashcard::class,
        ]);
    }
}
