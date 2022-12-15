<?php

namespace Waboot\inc\core;

use Symfony\Component\Yaml\Yaml;
use Waboot\inc\core\mvc\HTMLView;
use Waboot\inc\core\utils\Utilities;

class Theme{
    /**
     * @var Layout
     */
    private $layoutHandler;
    /**
     * @var AssetsManager
     */
    private $assetsManager;

    public function __construct(AssetsManager $assetsManager, Layout $layout)
    {
        $this->assetsManager = $assetsManager;
        $this->layoutHandler = $layout;
    }

    public function getAssetsManager(): AssetsManager
    {
        return $this->assetsManager;
    }

    public function getLayoutHandler(): Layout
    {
        return $this->layoutHandler;
    }

    public function loadDependencies(): void
    {
        $deps = [
            'inc/core/hooks.php',
            'inc/template-functions.php',
            'inc/template-rendering.php',
            'inc/template-tags.php',
            'inc/hooks/hooks.php',
            'inc/hooks/init.php',
            'inc/hooks/layout.php',
            'inc/hooks/posts-and-pages.php',
            'inc/hooks/widget-areas.php',
            'inc/hooks/assets.php'
        ];
        safeRequireFiles($deps);
    }

    /**
     * @param string $templateFile
     * @param array $vars
     * @param bool $clean
     */
    public function renderView(string $templateFile, array $vars = [], bool $clean = false): void
    {
        try{
            $v = new HTMLView($templateFile);
            if($clean){
                $v->clean()->display($vars);
            }else{
                $v->display($vars);
            }
        }catch (\Exception $e){
            echo $e->getMessage();
        }
    }

    /**
     * @return DB
     */
    public function DB(): DB
    {
        return DB::getInstance();
    }

    /**
     * @param string $command
     * @param string|callable $callable
     * @param string $prefix
     * @param array $description
     * @return void
     * @throws ThemeException
     */
    public function registerCommand(string $command, $callable, string $prefix = '', array $description = []): void
    {
        if(!class_exists('\WP_CLI')){
            return;
        }
        try{
            if($prefix !== ''){
                $command = $prefix.':'.$command;
            }
            if(empty($description)){
                if(class_exists($callable) && method_exists($callable,'getCommandDescription')){
                    $description = $callable::getCommandDescription();
                }
            }
            if(\is_array($description) && !empty($description)){
                \WP_CLI::add_command($command,$callable,$description);
            }else{
                \WP_CLI::add_command($command,$callable);
            }
        }catch (\Exception | \Throwable $e){
            throw new ThemeException($e->getMessage());
        }
    }
}