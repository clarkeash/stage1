<?php

namespace App\CoreBundle\EventListener;

use Symfony\Bridge\Doctrine\RegistryInterface;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\Common\EventSubscriber;

use App\CoreBundle\Entity\Build;
use App\CoreBundle\Entity\Branch;

use Psr\Log\LoggerInterface;

/**
 * Whenever a build is created, checks if there exists a corresponding
 * branch record. If not create it. in any case, create a relation between
 * the build and the branch records.
 */
class BuildBranchRelationSubscriber implements EventSubscriber
{
    /**
     * @var Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var Symfony\Bridge\Doctrine\RegistryInterface
     */
    private $doctrine;

    /**
     * @param Psr\Log\LoggerInterface                   $logger
     * @param Symfony\Bridge\Doctrine\RegistryInterface $doctrine
     */
    public function __construct(LoggerInterface $logger, RegistryInterface $doctrine)
    {
        $this->logger = $logger;
        $this->doctrine = $doctrine;
    }

    /**
     * @return array
     */
    public function getSubscribedEvents()
    {
        return ['prePersist'];
    }

    /**
     * @param LifecycleEventArgs
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $build = $args->getEntity();

        if (!$build instanceof Build) {
            return;
        }

        $em = $this->doctrine->getManager();
        
        $branch = $this->doctrine
            ->getRepository('AppCoreBundle:Branch')
            ->findOneByProjectAndName($build->getProject(), $build->getRef());

        if (!$branch) {
            $this->logger->info('creating non-existing branch', ['project' => $project->getId(), 'branch' => $ref]);

            $branch = new Branch();
            $branch->setName($build->getRef());
            $branch->setProject($build->getProject());

            $em->persist($branch);
        }

        $build->setBranch($branch);
    }
}