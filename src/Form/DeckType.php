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
                ]
            ])
            ->add('lang', ChoiceType::class, [
                'choices' => $languageChoices,
                'choice_label' => function ($choice, string $key) {
                    return ucfirst($key);
                }
            ])
            ->add('flashcards', CollectionType::class, [
                'entry_type' => FlashcardFormType::class,
                'entry_options' => ['label' => false],
                'prototype' => true,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'constraints' => [
                    new Count([
                        'min' => 1,
                        'max' => DeckController::MAX_FLASHCARDS_IN_DECK,
                        'minMessage' => 'At least {{ limit }} flashcard(s) has(have) to be added.',
                        'maxMessage' => 'There can\'t be more than {{ limit }} flashcards',
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
