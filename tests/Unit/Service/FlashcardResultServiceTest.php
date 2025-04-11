<?php

namespace App\Tests\Unit\Service;

use App\Entity\Flashcard;
use App\Enum\FlashcardResult;
use App\Service\FlashcardResultService;
use PHPUnit\Framework\TestCase;

class FlashcardResultServiceTest extends TestCase
{
    private FlashcardResultService $flashcardResultService;

    protected function setUp(): void
    {
        $this->flashcardResultService = new FlashcardResultService();
    }

    public function testGetDeckResultsSummaryGroupsFlashcardsByResult(): void
    {
        $correctFlashcard1 = new Flashcard();
        $correctFlashcard1->setResult(FlashcardResult::CORRECT);

        $correctFlashcard2 = new Flashcard();
        $correctFlashcard2->setResult(FlashcardResult::CORRECT);

        $incorrectFlashcard = new Flashcard();
        $incorrectFlashcard->setResult(FlashcardResult::INCORRECT);

        $unansweredFlashcard  = new Flashcard();
        $unansweredFlashcard->setResult(FlashcardResult::UNANSWERED);

        $flashcards = [
            $correctFlashcard1,
            $incorrectFlashcard,
            $unansweredFlashcard,
            $correctFlashcard2,
        ];

        $result = $this->flashcardResultService->getDeckResultsSummary($flashcards);

        $this->assertCount(2, $result['hasCorrect']);
        $this->assertCount(1, $result['hasIncorrect']);
        $this->assertCount(1, $result['hasUnanswered']);

        $this->assertSame($correctFlashcard1, $result['hasCorrect'][0]);
        $this->assertSame($correctFlashcard2, $result['hasCorrect'][1]);
        $this->assertSame($incorrectFlashcard, $result['hasIncorrect'][0]);
        $this->assertSame($unansweredFlashcard, $result['hasUnanswered'][0]);
    }

    public function testGetDeckResultsSummaryWithEmptyFlashcards(): void
    {

        $result = $this->flashcardResultService->getDeckResultsSummary([]);

        $this->assertEmpty($result['hasCorrect']);
        $this->assertEmpty($result['hasIncorrect']);
        $this->assertEmpty($result['hasUnanswered']);
    }

    public function testGetDeckResultsSummaryWithOnlyOneResultType(): void
    {
        $correctFlashcard1 = new Flashcard();
        $correctFlashcard1->setResult(FlashcardResult::CORRECT);

        $correctFlashcard2 = new Flashcard();
        $correctFlashcard2->setResult(FlashcardResult::CORRECT);

        $flashcards = [$correctFlashcard1, $correctFlashcard2];

        $result = $this->flashcardResultService->getDeckResultsSummary($flashcards);

        $this->assertCount(2, $result['hasCorrect']);
        $this->assertEmpty($result['hasIncorrect']);
        $this->assertEmpty($result['hasUnanswered']);
    }
}
