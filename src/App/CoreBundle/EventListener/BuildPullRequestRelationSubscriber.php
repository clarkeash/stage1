<?php

namespace App\CoreBundle\EventListener;

use App\CoreBundle\Provider\ProviderFactory;
use App\Model\Build;
use App\Model\PullRequest;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;

class BuildPullRequestRelationSubscriber implements EventSubscriber
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var RegistryInterface
     */
    private $doctrine;

    /**
     * @var ProviderFactory
     */
    private $providerFactory;

    /**
     * @param LoggerInterface   $logger
     * @param RegistryInterface $doctrine
     * @param ProviderFactory   $providerFactory
     */
    public function __construct(LoggerInterface $logger, RegistryInterface $doctrine, ProviderFactory $providerFactory)
    {
        $this->logger = $logger;
        $this->doctrine = $doctrine;
        $this->providerFactory = $providerFactory;
    }

    /**
     * @return string[]
     */
    public function getSubscribedEvents()
    {
        return ['prePersist'];
    }

    /**
     * @return boolean
     */
    public function supports($entity)
    {
        return ($entity instanceof Build) && $entity->isPullRequest();
    }

    /**
     * @param LifecycleEventArgs
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $build = $args->getEntity();

        if (!$this->supports($build)) {
            return;
        }

        $em = $this->doctrine->getManager();
        
        $pr = $this->doctrine
            ->getRepository('Model:PullRequest')
            ->findOneBy([
                'project' => $build->getProject()->getId(),
                'ref' => $build->getRef(),
            ]);

        if (!$pr) {
            $this->logger->info('creating non-existing pr', [
                'project' => $build->getProject()->getId(),
                'ref' => $build->getRef()
            ]);

            $project = $build->getProject();

            if (null === $project) {
                $this->logger->info('could not find a project for build', ['build_id' => $build->getId()]);
            }

            $provider = $this->providerFactory->getProvider($project);
            $pr = $provider->createPullRequestFromPayload($project, $build->getRawPayload());
            
            $em->persist($pr);
        }

        $build->setPullRequest($pr);
    }
}