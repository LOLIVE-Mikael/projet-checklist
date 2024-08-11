<?php

namespace App\Repository;

use App\Entity\Visite;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use DateTime;

/**
 * @extends ServiceEntityRepository<Visite>
 */
class VisiteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Visite::class);
    }

	/**
     * Trouve les visites postérieures à la date actuelle
     *
     * @return Visite[] Retourne un tableau de visites
     */
    public function findFutureVisits(): array
    {
        // Récupérer la date actuelle
        $currentDate = new DateTime();

        // Créer une requête pour récupérer les visites postérieures à la date actuelle
        return $this->createQueryBuilder('v')
            ->andWhere('v.date >= :currentDate')
            ->setParameter('currentDate', $currentDate)
			->orderBy('v.date', 'ASC')
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return Visite[] Returns an array of Visite objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('v')
//            ->andWhere('v.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('v.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Visite
//    {
//        return $this->createQueryBuilder('v')
//            ->andWhere('v.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
