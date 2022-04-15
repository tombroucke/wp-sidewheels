<?php

namespace Otomaties\Sidewheels;

class Admin
{
    
    /**
     * Configuration object
     *
     * @var Config
     */
    private $config;

    /**
     * Set config file
     *
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

	public function addMetaBoxes() {
		add_meta_box('sidewheels_post_meta', __('Meta', $this->config->textDomain()), [$this, 'showPostMeta']);
	}

	public function showPostMeta() {
		$meta = [];
		$allMeta = get_post_meta(get_the_ID());
		$allowedKeys = array_filter(array_keys($allMeta), function($key) {
			return $key[0] !== "_";
		});
		$meta = array_intersect_key($allMeta, array_flip($allowedKeys));
		include dirname(__FILE__, 2) . '/templates/admin/post-meta.php';
	}
}
