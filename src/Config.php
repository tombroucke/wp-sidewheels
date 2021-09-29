<?php

namespace Otomaties\Sidewheels;

class Config
{

    private $rootPath;
    private $configPath;

    public function __construct(string $rootPath)
    {
        $this->rootPath = $rootPath;
        $this->configPath = $rootPath . '/config.php';

        $this->fetchConfig();
    }

    public function fetchConfig() : void
    {
        if (!file_exists($this->configPath)) {
            throw new \Exception('Config file not found', 1);
        }

        $defaults = [
            'templatePath'  => $this->rootPath . '/views',
            'textDomain'    => '',
            'routes'        => [],
            'postTypes'     => [],
            'taxonomies'    => [],
        ];
        $this->config = wp_parse_args(include($this->configPath), $defaults);
    }

    public function templatePath() : string
    {
        return $this->config['templatePath'];
    }

    public function textDomain() : string
    {
        return $this->config['textDomain'];
    }

    public function routes() : array
    {
        return $this->config['routes'];
    }

    public function postTypes() : array
    {
        return $this->config['postTypes'];
    }

    public function taxonomies() : array
    {
        return $this->config['taxonomies'];
    }
}
