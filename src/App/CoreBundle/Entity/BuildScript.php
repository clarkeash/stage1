<?php

namespace App\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BuildScript
 */
class BuildScript
{
    /**
     * @var string
     */
    private $buildScript;

    /**
     * @var string
     */
    private $runScript;

    /**
     * @var string
     */
    private $config;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\CoreBundle\Entity\Build
     */
    private $build;

    /**
     * @return array
     */
    public function getContainerEnv()
    {
        $config = $this->getConfig();

        $env = [];

        if (!array_key_exists('env', $config)) {
            return $env;
        }

        foreach ($config['env'] as $key => $value) {
            $env[] = sprintf('%s=%s', $key, $value);
        }

        return $env;
    }

    /**
     * @return array
     */
    public function getNormalizedConfig()
    {
        $config = $this->getConfig();

        if (array_key_exists('env', $config)) {
            $config['env'] = $this->getContainerEnv();
        }

        return $config;
    }

    /**
     * @param string $json
     * 
     * @return App\CoreBundle\Entity\BuildScript
     */
    public static function fromJson($json)
    {
        $data = json_decode($json, true);

        $obj = new self();
        $obj->setBuildScript($data['build']);
        $obj->setRunScript($data['run']);
        $obj->setConfig($data['config']);

        return $obj;
    }


    /**
     * Set buildScript
     *
     * @param string $buildScript
     * @return BuildScript
     */
    public function setBuildScript($buildScript)
    {
        $this->buildScript = $buildScript;
    
        return $this;
    }

    /**
     * Get buildScript
     *
     * @return string 
     */
    public function getBuildScript()
    {
        return $this->buildScript;
    }

    /**
     * Set runScript
     *
     * @param string $runScript
     * @return BuildScript
     */
    public function setRunScript($runScript)
    {
        $this->runScript = $runScript;
    
        return $this;
    }

    /**
     * Get runScript
     *
     * @return string 
     */
    public function getRunScript()
    {
        return $this->runScript;
    }

    /**
     * Set config
     *
     * @param string $config
     * @return BuildScript
     */
    public function setConfig($config)
    {
        $this->config = $config;
    
        return $this;
    }

    /**
     * Get config
     *
     * @return string 
     */
    public function getConfig()
    {
        return $this->config ?: [];
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set build
     *
     * @param \App\CoreBundle\Entity\Build $build
     * @return BuildScript
     */
    public function setBuild(\App\CoreBundle\Entity\Build $build = null)
    {
        $this->build = $build;
    
        return $this;
    }

    /**
     * Get build
     *
     * @return \App\CoreBundle\Entity\Build 
     */
    public function getBuild()
    {
        return $this->build;
    }
}