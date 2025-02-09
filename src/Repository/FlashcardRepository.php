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
    public function findByDeck(Deck $deck): array
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.deck = :deck')
            ->setParameter('deck', $deck)
            ->getQuery()
            ->getResult()
        ;
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
