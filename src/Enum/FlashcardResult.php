<?php

namespace App\Enum;

enum FlashcardResult: string
{
    case INCORRECT = 'incorrect';
    case UNANSWERED = 'unanswered';
    case CORRECT = 'correct';
}
