<?php

namespace App\Entity;

use App\Enum\FlashcardResult;
use App\Repository\FlashcardRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FlashcardRepository::class)]
class Flashcard
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $front = null;

    #[ORM\Column(length: 255)]
    private ?string $back = null;

    #[ORM\ManyToOne(inversedBy: 'flashcards')]
    private ?Deck $deck = null;

    #[ORM\Column(length: 10)]
    private ?string $imageType = null;

    #[ORM\Column(enumType: FlashcardResult::class)]
    private ?FlashcardResult $result = FlashcardResult::UNANSWERED;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFront(): ?string
    {
        return $this->front;
    }

    public function setFront(string $front): static
    {
        $this->front = $front;

        return $this;
    }

    public function getBack(): ?string
    {
        return $this->back;
    }

    public function setBack(string $back): static
    {
        $this->back = $back;

        return $this;
    }

    public function getDeck(): ?Deck
    {
        return $this->deck;
    }

    public function setDeck(?Deck $deck): static
    {
        $this->deck = $deck;

        return $this;
    }

    public function getImageType(): ?string
    {
        return $this->imageType;
    }

    public function setImageType(string $imageType): static
    {
        $this->imageType = $imageType;

        return $this;
    }

    public function getResult(): ?FlashcardResult
    {
        return $this->result;
    }

    public function setResult(FlashcardResult $result): static
    {
        $this->result = $result;

        return $this;
    }
}
