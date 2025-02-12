<?php

namespace App\Enum;

enum FlashcardResult: string
{
    case CORRECT = 'correct';
    case INCORRECT = 'incorrect';
    case UNANSWERED = 'unanswered';
}
