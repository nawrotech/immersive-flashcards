<?php

namespace App\Tests\Unit\Service;

use App\Entity\Flashcard;
use App\Enum\FlashcardResult;
use App\Service\FlashcardService;
use PHPUnit\Framework\TestCase;

class FlashcardServiceTest extends TestCase
{
    private FlashcardService $flashcardService;

    protected function setUp(): void
    {
        $this->flashcardService = new FlashcardService();
    }

    public function testHasFlashcardsWithResult(): void
    {

        $correctFlashcard = new Flashcard();
        $correctFlashcard->setResult(FlashcardResult::CORRECT);

        $incorrectFlashcard = new Flashcard();
        $incorrectFlashcard->setResult(FlashcardResult::INCORRECT);

        $flashcards = [$correctFlashcard, $incorrectFlashcard];


        $this->assertTrue(
            $this->flashcardService->hasFlashcardsWithResult($flashcards, FlashcardResult::CORRECT)
        );


        $this->assertFalse(
            $this->flashcardService->hasFlashcardsWithResult($flashcards, FlashcardResult::UNANSWERED)
        );


        $this->assertFalse(
            $this->flashcardService->hasFlashcardsWithResult([], FlashcardResult::CORRECT)
        );
    }

    public function testGetDeckResultsSummary(): void
    {

        $correctFlashcard = new Flashcard();
        $correctFlashcard->setResult(FlashcardResult::CORRECT);

        $incorrectFlashcard = new Flashcard();
        $incorrectFlashcard->setResult(FlashcardResult::INCORRECT);

        $unansweredFlashcard = new Flashcard();
        $unansweredFlashcard->setResult(FlashcardResult::UNANSWERED);

        $flashcards = [$correctFlashcard, $incorrectFlashcard, $unansweredFlashcard];

        $summary = $this->flashcardService->getDeckResultsSummary($flashcards);

        $this->assertEquals([
            'hasCorrect' => true,
            'hasIncorrect' => true,
            'hasUnanswered' => true,
        ], $summary);
    }

    public function testGetDeckResultsSummaryWithEmptyDeck(): void
    {
        $summary = $this->flashcardService->getDeckResultsSummary([]);

        $this->assertEquals([
            'hasCorrect' => false,
            'hasIncorrect' => false,
            'hasUnanswered' => false,
        ], $summary);
    }
}
