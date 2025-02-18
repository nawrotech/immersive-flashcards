<?php

namespace App\Repository;

use App\Entity\Deck;
use App\Entity\Flashcard;
use App\Enum\FlashcardResult;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Flashcard>
 */
class FlashcardRepository extends ServiceEntityRepository
{

    public const PER_PAGE = 10;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Flashcard::class);
    }

    /**
     * @return Flashcard[] Returns an array of Flashcard objects
     */
    public function findByDeck(Deck $deck, bool $orderByResult = false, ?FlashcardResult $result = null): array
    {
        $qb = $this->createQueryBuilder('f')
            ->andWhere('f.deck = :deck')
            ->setParameter('deck', $deck);


        if ($orderByResult) {
            $qb = $qb->addSelect("
                    CASE 
                        WHEN f.result = 'incorrect' THEN 1
                        WHEN f.result = 'unanswered' THEN 2
                        WHEN f.result = 'correct' THEN 3
                        ELSE 4 
                    END AS HIDDEN sort_order
                ")->orderBy('sort_order', 'ASC');
        };

        if ($result) {
            $qb = $qb->andWhere('f.result = :result')
                ->setParameter('result', $result);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }


    public function findFlashcardsByDeckPaginator(int $deckId, int $offset = 0): Paginator
    {
        $qb = $this->createQueryBuilder('f')
            ->addSelect("
                    CASE 
                        WHEN f.result = 'incorrect' THEN 1
                        WHEN f.result = 'unanswered' THEN 2
                        WHEN f.result = 'correct' THEN 3
                        ELSE 4 
                    END AS HIDDEN sort_order
                ")->orderBy('sort_order', 'ASC')
            ->andWhere('IDENTITY(f.deck) = :deckId')

            ->setParameter('deckId', $deckId);

        $query = $qb->setFirstResult($offset)
            ->setMaxResults($this::PER_PAGE);


        $paginator = new Paginator($query);
        $paginator->setUseOutputWalkers(false);

        return $paginator;
    }
}
