<?php

namespace App\Repository;

use App\Entity\Deck;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Deck>
 */
class DeckRepository extends ServiceEntityRepository
{

    public const PER_PAGE = 12;

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

    public function findDecksPaginator(int $offset = 0, User $user, ?string $searchTerm = null): Paginator
    {
        $qb = $this->createQueryBuilder('d')->andWhere('d.creator = :user')
            ->setParameter('user', $user);

        if ($searchTerm != null) {
            $qb = $qb->andWhere('d.name LIKE :searchTerm')
                ->setParameter('searchTerm', "%$searchTerm%");
        }

        $query = $qb->setFirstResult($offset)
            ->setMaxResults($this::PER_PAGE);

        return new Paginator($query);
    }
}
