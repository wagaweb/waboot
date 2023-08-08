<?php

namespace Waboot\inc\core;

use Waboot\inc\core\utils\Utilities;

/**
 * Class AssetsManager
 *
 * A simple assets manager.
 */
class AssetsManager
{
    /**
     * @var array
     */
    private $assets;

    /**
     * AssetsManager constructor.
     *
     * @param array $assets
     */
    public function __construct($assets = [])
    {
        if (is_array($assets) && !empty($assets)) {
            $this->addAssets($assets);
        }
    }

    /**
     * Adds a single asset
     *
     * @param $name
     * @param array $args
     */
    public function addAsset($name, $args): void
    {
        $this->assets[$name] = $args;
    }

    /**
     * Adds multiple assets
     * @param array $assets
     */
    public function addAssets($assets): void
    {
        foreach ($assets as $name => $args) {
            $this->addAsset($name, $args);
        }
    }

    /**
     * Enqueue the registered assets
     *
     * @throws \Exception
     */
    public function enqueue(): void
    {
        $to_enqueue = [];

        //Doing some checks
        foreach ($this->assets as $name => $param) {
            $isVersionNull = false;

            $param = wp_parse_args($param, [
                'uri' => '', //A valid uri
                'path' => '', //A valid path
                'version' => false, //If FALSE, the filemtime will be used (if path is set)
                'deps' => [], //Dependencies
                'i10n' => [], //the Localication array for wp_localize_script
                'type' => '', //js or css. Optional. Its autodetected if empty.
                'enqueue_callback' => false, //A valid callable that must be return true or false
                'in_footer' => false, //Used for scripts
                'enqueue' => true, //If FALSE the script\css will only be registered
                'media' => apply_filters('waboot/assets/styles/default_media', 'all') //The media for which this stylesheet has been defined. Accepts media types like 'all', 'print' and 'screen', or media queries like '(orientation: portrait)' and '(max-width: 640px)'.
            ]);
            if ($param['path'] !== "" && !file_exists($param['path'])) {
                if (is_admin()) {
                    Utilities::addAdminNotice("Asset '$name' not found in '" . $param['path'] . "'", 'error');
                } else {
                    trigger_error("Asset '$name' not found in '" . $param['path'] . "'");
                }
                continue;
            }
            if (isset($param['version']) && \is_string($param['version'])) {
                $version = $param['version'];
            } elseif (\is_null($param['version'])) {
                $version = null;
                $isVersionNull = true;
            } else {
                $version = false;
                //Get version
                if ($param['path'] !== "" && file_exists($param['path'])) {
                    $version = filemtime($param['path']);
                } elseif ($param['uri'] !== '' && strpos($param['uri'], \home_url()) === false) {
                    //If an external assets is linked, version is unnecessary
                    $version = null;
                    $isVersionNull = true;
                }
            }
            if ($param['path'] !== '' && $param['type'] === '' && file_exists($param['path'])) {
                //Autodetect types
                $ext = pathinfo($param['path'], PATHINFO_EXTENSION);
                if (in_array($ext, ['js', 'css'])) {
                    $param['type'] = $ext;
                }
            }
            if ($param['type'] === 'js') {
                wp_register_script($name, $param['uri'], $param['deps'], $version, $param['in_footer']);
            } elseif ($param['type'] === 'css') {
                wp_register_style($name, $param['uri'], $param['deps'], $version, $param['media']);
            } else {
                throw new \Exception("Unknow asset type for $name");
            }
            if ($param['type'] === "js" && isset($param['i10n']) && is_array($param['i10n']) && !empty($param['i10n'])) {
                if (is_array($param['i10n']) && array_key_exists("name", $param['i10n']) && array_key_exists('params', $param['i10n']) && is_array($param['i10n']['params'])) {
                    wp_localize_script($name, $param['i10n']['name'], $param['i10n']['params']);
                }
            }
            if ($param['enqueue']) {
                $to_enqueue[] = [
                    'name' => $name,
                    'type' => $param['type'],
                    'callback' => isset($param['enqueue_callback']) && is_callable($param['enqueue_callback']) ? $param['enqueue_callback'] : false
                ];
            }
        }

        //Actual enqueue
        if (!empty($to_enqueue)) {
            foreach ($to_enqueue as $s) {
                if ($s['callback']) {
                    $can_enqueue = call_user_func($s['callback']);
                } else {
                    $can_enqueue = true;
                }
                if ($can_enqueue) {
                    if ($s['type'] === 'js') {
                        wp_enqueue_script($s['name']);
                    } elseif ($s['type'] === 'css') {
                        wp_enqueue_style($s['name']);
                    }
                }
            }
        }
    }
}