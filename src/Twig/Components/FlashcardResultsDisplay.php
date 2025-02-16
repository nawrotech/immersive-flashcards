<?php

namespace App\Twig\Components;

use App\Repository\FlashcardRepository;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('FlashcardResultsDisplay')]
class FlashcardResultsDisplay
{
    use ComponentToolsTrait;
    use DefaultActionTrait;

    #[LiveProp]
    public int $page = 0;

    #[LiveProp]
    public int $deckId;

    private int $deckCount = 0;

    public function __construct(private FlashcardRepository $flashcardRepository) {}

    #[LiveAction]
    public function more(): void
    {
        ++$this->page;
    }

    public function hasMore(): bool
    {
        if ($this->deckCount === 0) {
            $this->deckCount = $this->flashcardRepository
                ->findFlashcardsByDeckPaginator($this->deckId)->count();
        }

        return $this->deckCount - FlashcardRepository::PER_PAGE > ($this->page * FlashcardRepository::PER_PAGE);
    }

    public function getItems()
    {
        $offset = $this->page * FlashcardRepository::PER_PAGE;
        $items = $this->flashcardRepository->findFlashcardsByDeckPaginator($this->deckId, $offset);
        return $items;
    }
}
