<?php

namespace App\Twig\Components;

use App\Entity\User;
use App\Repository\DeckRepository;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('DecksColumn')]
class DecksColumn
{
    use ComponentToolsTrait;
    use DefaultActionTrait;

    #[LiveProp]
    public int $page = 0;

    #[LiveProp]
    public string $searchTerm = '';

    #[LiveProp]
    public User $user;

    public function __construct(private DeckRepository $deckRepository) {}

    #[LiveAction]
    public function more(): void
    {
        ++$this->page;
    }

    public function hasMore(): bool
    {
        $deckCount = $this->deckRepository
            ->findDecksPaginator($this->user, 0, $this->searchTerm)->count();
        return $deckCount - DeckRepository::PER_PAGE > ($this->page * DeckRepository::PER_PAGE);
    }

    public function getDecks()
    {
        $offset = $this->page * DeckRepository::PER_PAGE;
        $decks = $this->deckRepository->findDecksPaginator($this->user, $offset, $this->searchTerm);
        return $decks;
    }
}
