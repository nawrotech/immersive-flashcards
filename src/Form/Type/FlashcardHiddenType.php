<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class FlashcardHiddenType extends AbstractType
{
    public function getBlockPrefix(): string
    {
        return 'flashcard_hidden';
    }

    
    public function getParent(): string
    {
        return TextType::class;
    }
}
