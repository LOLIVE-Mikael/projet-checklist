<?php

namespace App\Repository;

use App\Entity\Task;
use App\Entity\Checklist;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Task>
 */
class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    /**
     * Récupérer les tâches qui ne sont pas associées à cette checklist.
     *
     * @param Checklist $checklist
     * @return Task[] Retourne un tableau d'objets Task
     */
	public function findTasksNotInChecklist(Checklist $checklist)
    {
        return $this->createQueryBuilder('t')
            ->where('t NOT IN (
                SELECT Task FROM App\Entity\Checklist c
                JOIN c.tasks Task
                WHERE c = :checklist
            )')
		    ->andWhere('t.archived = false')
            ->setParameter('checklist', $checklist)
            ->getQuery()
            ->getResult();
    }

	    /**
     * Récupère toutes les tâches non archivées.
     *
     * @return Task[]
     */
    public function findAllNonArchived(): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.archived = :archived')
            ->setParameter('archived', false)
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Task[] Returns an array of Task objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('t.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Task
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
