<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class FlashcardHiddenType extends AbstractType
{
    public function getBlockPrefix()
    {
        return 'flashcard_hidden';
    }

    public function getParent(): string
    {
        return HiddenType::class;
    }
}
