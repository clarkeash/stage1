<?php

namespace App\CoreBundle\Provider;

use App\Model\Project;
use App\Model\User;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\Extension\Csrf\CsrfProvider\CsrfProviderInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * App\CoreBundle\Provider\AbstractProvider
 */
abstract class AbstractProvider implements ProviderInterface
{
    /**
     * @var string
     */
    protected $baseUrl;

    /**
     * @var string
     */
    protected $baseApiUrl;

    /**
     * @var string
     */
    protected $accessTokenUrl;

    /**
     * @var array
     */
    protected $apiCache = [];

    /**
     * @var UrlGeneratorInterface
     */
    protected $router;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var array
     */
    protected $scopeMap = [];

    /**
     * @param LoggerInterface       $logger
     * @param UrlGeneratorInterface $router
     * @param CsrfProviderInterface $csrfProvider
     */
    public function __construct(LoggerInterface $logger, UrlGeneratorInterface $router)
    {
        $this->logger = $logger;
        $this->router = $router;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getDisplayName();
    }

    /**
     * @return string
     */
    public function getAccessTokenUrl()
    {
        return $this->baseUrl.$this->accessTokenUrl;
    }

    /**
     * @param User $user
     * 
     * @return string|array $accessToken
     */
    public function getAccessTokenFromUser(User $user)
    {
        return $user->getProviderAccessToken($this->getName());
    }

    /**
     * @return string|array
     */
    public function getAccessToken(Project $project)
    {
        return $project->getUsers()->first()->getProviderAccessToken($this->getName());
    }

    /**
     * @param Project $project
     * 
     * @return Client
     */
    public function configureClientForProject(Project $project)
    {
        if (count($project->getUsers()) === 0) {
            throw new InvalidArgumentException('Project "'.$project->getFullName().'" has no users');
        }

        return $this->configureClientForUser($project->getUsers()->first());
    }

    /**
     * @param User $user
     * 
     * @return Client
     */
    public function configureClientForUser(User $user)
    {
        $accessToken = $user->getProviderAccessToken($this->getName());

        return $this->configureClientForAccessToken($accessToken);
    }

    /**
     * Translates a Stage1 scope to the provider scope.
     * 
     * @param string $scope
     * 
     * @return string|null
     */
    public function translateScope($scope)
    {
        if (array_key_exists($scope, $this->scopeMap)) {
            return $this->scopeMap[$scope];
        }

        throw new InvalidArgumentException('Unknown internal scope "'.$scope.'"');
    }

    /**
     * Translates a provider scope to a Stage1 scope
     * @param string $scope
     * 
     * @return string
     */
    public function reverseTranslateScope($scope)
    {
        if (in_array($scope, $this->scopeMap)) {
            return $this->scopeMap[array_search($scope, $this->scopeMap)];
        }

        throw new InvalidArgumentException('Unknown provider scope "'.$scope.'"');
    }

    /**
     * @param User  $user
     * @param array $scope
     * 
     * @return boolean
     */
    public function hasScope(User $user, $scope)
    {
        $translatedScope = $this->translateScope($scope);

        return (null === $translatedScope)
            ? $user->hasProviderAccessToken($this->getName())
            : $user->hasProviderScope($this->getName(), $translatedScope);
    }

    /**
     * @return string
     */
    public function requireLogin()
    {
        return $this->requireScope();
    }

    /**
     * @param Project $project
     * 
     * @return boolean
     */
    public function hasDeployKey(Project $project)
    {
        return $this->countDeployKeys($project) > 0;
    }

    /**
     * @param User $user
     * 
     * @return array
     */
    public function getIndexedRepositories(User $user)
    {
        $indexedProjects = [];

        foreach ($this->getRepositories($user) as $project) {
            if (!array_key_exists($project['owner_login'], $indexedProjects)) {
                $indexedProjects[$project['owner_login']] = [];
            }

            $indexedProjects[$project['owner_login']][] = $project;
        }

        return $indexedProjects;
    }
}