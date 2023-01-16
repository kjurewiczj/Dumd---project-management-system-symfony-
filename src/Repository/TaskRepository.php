<?php

namespace App\Repository;

use App\Entity\Project;
use App\Entity\Task;
use App\Repository\UserRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Task>
 *
 * @method Task|null find($id, $lockMode = null, $lockVersion = null)
 * @method Task|null findOneBy(array $criteria, array $orderBy = null)
 * @method Task[]    findAll()
 * @method Task[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TaskRepository extends ServiceEntityRepository
{
    public function __construct(
        private UserRepository $userRepository,
        ManagerRegistry $registry,
    )
    {
        parent::__construct($registry, Task::class);
    }

    public function save(Task $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Task $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getList(Project $project, $request): array
    {
        $queryBuilder = $this->createQueryBuilder('t')
            ->orderBy('t.priority', 'ASC');

        for ($i=0; $i<count($request); $i++) {
            if (!$request->get('user'.$i)) continue;
            $tmpUser = $this->userRepository->find($request->get('user'.$i));
            $queryBuilder->orWhere('t.userAssigned = :userAssigned'.$i)->setParameter('userAssigned'.$i, $tmpUser);
        }

        $queryBuilder->andWhere('t.project = :project')->setParameter('project', $project);

        return $queryBuilder->getQuery()->getResult();
    }

    public function getMinStartDate(Project $project): string
    {
        $queryBuilder = $this->createQueryBuilder('t')
            ->select('MIN(t.startDate)')
            ->andWhere('t.project = :project')->setParameter('project', $project)
            ->getQuery()->getSingleScalarResult();

        return $queryBuilder ?? '';
    }

    public function getLastEndDate(Project $project): string
    {
        $queryBuilder = $this->createQueryBuilder('t')
            ->select('MAX(t.endDate)')
            ->andWhere('t.project = :project')->setParameter('project', $project)
            ->getQuery()->getSingleScalarResult();

        return $queryBuilder ?? '';
    }

    public function getAssignedUsersForProject(Project $project): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.project = :project')->setParameter('project', $project)
            ->orderBy('t.id', 'DESC')
            ->groupBy('t.userAssigned')
            ->getQuery()->getResult();
    }
}
