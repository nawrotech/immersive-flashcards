<?php

namespace App\Service;

use App\Enum\FlashcardResult;

class FlashcardService
{

    public function getDeckResultsSummary(array $flashcards)
    {
        $correct = [];
        $incorrect = [];
        $unanswered = [];

        foreach ($flashcards as $flashcard) {
            $result = $flashcard->getResult();

            if ($result === FlashcardResult::CORRECT) {
                $correct[] = $flashcard;
            } elseif ($result === FlashcardResult::INCORRECT) {
                $incorrect[] = $flashcard;
            } elseif ($result === FlashcardResult::UNANSWERED) {
                $unanswered[] = $flashcard;
            }
        }

        return [
            'hasCorrect' => array_values($correct),
            'hasIncorrect' => array_values($incorrect),
            'hasUnanswered' => array_values($unanswered),
        ];
    }
}
