<?php

namespace App\Repository;

use App\Entity\Deck;
use App\Entity\Flashcard;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Flashcard>
 */
class FlashcardRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Flashcard::class);
    }

    /**
     * @return Flashcard[] Returns an array of Flashcard objects
     */
    public function findByDeck(Deck $deck, bool $orderByStudyPriority = false): array
    {
        $qb = $this->createQueryBuilder('f')
            ->andWhere('f.deck = :deck')
            ->setParameter('deck', $deck);


        if ($orderByStudyPriority) {
            $qb = $qb->addSelect("
                    CASE 
                        WHEN f.result = 'incorrect' THEN 1
                        WHEN f.result = 'unanswered' THEN 2
                        WHEN f.result = 'correct' THEN 3
                        ELSE 4 
                    END AS HIDDEN sort_order
                ")->orderBy('sort_order', 'ASC');
        };

        return $qb
            ->getQuery()
            ->getResult();
    }

    //    public function findOneBySomeField($deck): ?Flashcard
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $deck)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
