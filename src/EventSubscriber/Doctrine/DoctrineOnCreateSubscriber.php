<?php

namespace App\EventSubscriber\Doctrine;

use App\Interfaces\Entity\CreatedAtInterface;
use App\Interfaces\Entity\UserCreatedInterface;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\UnitOfWork;
use Symfony\Bundle\SecurityBundle\Security;

class DoctrineOnCreateSubscriber implements EventSubscriber
{
    private EntityManager $em;
    private UnitOfWork $uow;

    public function __construct(
        private readonly Security $security,
    )
    {
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::onFlush,
        ];
    }

    public function onFlush(OnFlushEventArgs $args)
    {
        $this->em = $args->getObjectManager();
        $this->uow = $this->em->getUnitOfWork();
        foreach ($this->uow->getScheduledEntityInsertions() as $entity) {
            $this->onCreated($entity);
        }
    }

    public function onCreated($entity)
    {
        if ($entity instanceof CreatedAtInterface) {
            $entity->setCreatedAt(new \DateTimeImmutable());
        }
        if ($entity instanceof UserCreatedInterface) {
            $entity->setUserCreated($this->security->getUser());
        }
    }
}