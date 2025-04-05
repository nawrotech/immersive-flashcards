<?php

namespace App\Form;

use App\Controller\DeckController;
use App\Entity\Deck;
use App\Service\LocaleMappingService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class DeckType extends AbstractType
{

    public function __construct(private LocaleMappingService $localeMappingService) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $languageChoices = array_flip($this->localeMappingService->getLanguagesMapping());

        $builder
            ->add('name', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Length(max: 100, maxMessage: "Name cannot exceed 100 characters")
                ]
            ])
            ->add('lang', ChoiceType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Length(exactly: 2),
                ],
                'choices' => $languageChoices,
                'choice_label' => function ($choice, string $key) {
                    return ucfirst($key);
                }
            ])
            ->add('flashcards', CollectionType::class, [
                'entry_type' => FlashcardFormType::class,
                "label" => false,
                'prototype' => true,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                "error_bubbling" => false,
                "entry_options" => [
                    "error_bubbling" => false,

                ],
                'constraints' => [
                    new Count([
                        'min' => 1,
                        'max' => DeckController::MAX_FLASHCARDS_IN_DECK,
                        'minMessage' => 'At least {{ limit }} flashcard has to be added.',
                        'maxMessage' => 'There can\'t be more than {{ limit }} flashcards in deck',
                    ])
                ],
            ])
            ->add('submit', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Deck::class,
        ]);
    }
}
