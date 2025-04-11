<?php

namespace App\Service;

use App\Enum\FlashcardResult;

class FlashcardResultService
{
    public function getDeckResultsSummary(array $flashcards)
    {
        return [
            'hasCorrect' => array_values(array_filter(
                $flashcards,
                fn($card) => $card->getResult() === FlashcardResult::CORRECT
            )),
            'hasIncorrect' => array_values(array_filter(
                $flashcards,
                fn($card) => $card->getResult() === FlashcardResult::INCORRECT
            )),
            'hasUnanswered' => array_values(array_filter(
                $flashcards,
                fn($card) => $card->getResult() === FlashcardResult::UNANSWERED
            )),
        ];
    }
}
