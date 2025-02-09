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
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\NotBlank;

class FlashcardFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('front', TextType::class, [
                'constraints' => [
                    new NotBlank()
                ],
                'attr' => [
                    'data-ajax-images-target' => 'flashcardFront'
                ]
            ])
            ->add('back', FlashcardHiddenType::class, [
                'constraints' => [
                    new NotBlank(),
                ],
                'attr' => [
                    'data-ajax-images-target' => "flashcardBack",
                ]
            ])
            ->add('imageType', HiddenType::class, [
                'constraints' => [
                    new Choice(callback: [Image::class, 'values']),
                ],
                'attr' => [
                    'data-ajax-images-target' => 'flashcardImageType',
                ]
            ])
            ->add('searchGifs', ButtonType::class, [
                'attr' => [
                    'data-action' => 'ajax-images#searchGifs',
                ]
            ])
            ->add('searchImages', ButtonType::class, [
                'attr' => [
                    'data-action' => 'ajax-images#searchImages',
                ]
            ])
            ->add('deleteFlashcard', ButtonType::class, [
                'attr' => [
                    'data-action' => 'ajax-images#deleteFlashcard',
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
