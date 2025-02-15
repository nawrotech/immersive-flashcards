<?php

namespace App\Repository;

use App\Entity\Deck;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Deck>
 */
class DeckRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Deck::class);
    }



    /**
     * @return Deck[] Returns an array of Deck objects
     */
    public function findDecks($user): array
    {
        return $this->createQueryBuilder('d')
            // ->andWhere('d.creator = :user')
            // ->setParameter('user', $user)
            ->orderBy('d.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    //    public function findOneBySomeField($user): ?Deck
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $user)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
