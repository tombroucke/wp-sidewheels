<?php

namespace Otomaties\Sidewheels;

class Config
{

    /**
     * Rootpath of the WordPress plugin
     *
     * @var string
     */
    private $rootPath = '';

    /**
     * The full path to the config file
     *
     * @var string
     */
    private $configPath = '';

    /**
     * Initialize config
     *
     * @param string $rootPath
     */
    public function __construct(string $rootPath)
    {
        $this->rootPath = $rootPath;
        $this->configPath = $rootPath . '/config.php';

        $this->config = $this->fetchConfig();
    }

    /**
     * Get config file & set defaults
     *
     * @return array
     */
    public function fetchConfig() : array
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
        return wp_parse_args(include($this->configPath), $defaults);
    }

    /**
     * Template folder path. Defaults to plugin-dir/views
     *
     * @return string
     */
    public function templatePath() : string
    {
        return $this->config['templatePath'];
    }

    /**
     * The plugin textdomain
     *
     * @return string
     */
    public function textDomain() : string
    {
        return $this->config['textDomain'];
    }

    /**
     * The necessary routes
     *
     * @return array
     */
    public function routes() : array
    {
        return $this->config['routes'];
    }

    /**
     * Custom post types to be registered
     *
     * @return array
     */
    public function postTypes() : array
    {
        return $this->config['postTypes'];
    }

    /**
     * Custom taxonomies to be registered
     *
     * @return array
     */
    public function taxonomies() : array
    {
        return $this->config['taxonomies'];
    }
}
