<?php

namespace App\Service;

use App\Enum\FlashcardResult;
use Doctrine\Common\Collections\ArrayCollection;

class FlashcardService
{

    public function hasFlashcardsWithResult(array $flashcards, FlashcardResult $result): bool
    {

        return (new ArrayCollection($flashcards))
            ->exists(
                fn($key, $flashcard) =>
                $flashcard->getResult() === $result
            );
    }

    public function getDeckResultsSummary(array $flashcards)
    {
        return [
            'hasCorrect' => $this->hasFlashcardsWithResult($flashcards, FlashcardResult::CORRECT),
            'hasIncorrect' =>  $this->hasFlashcardsWithResult($flashcards, FlashcardResult::INCORRECT),
            'hasUnanswered' => $this->hasFlashcardsWithResult($flashcards, FlashcardResult::UNANSWERED),
        ];
    }
}
