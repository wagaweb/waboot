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

    public function loadDependecies()
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
     * @return array
     */
    public function getThemeOptions(): array {
        static $themeOptions = false;
        if(\is_array($themeOptions) && count($themeOptions) !== 0){
            return $themeOptions;
        }
        if(\is_child_theme()){
            $childYamlFilePath = get_stylesheet_directory().'/theme-options.yml';
            $parentYamlFilePath = get_template_directory().'/theme-options.yml';
            $childThemeOptions = [];
            $parentThemeOptions = [];
            if(\is_file($childYamlFilePath)){
                try{
                    $childThemeOptions = Yaml::parse(file_get_contents($childYamlFilePath));
                }catch (\Exception $e){
                    $childThemeOptions = [];
                }
            }
            if(\is_file($parentYamlFilePath)){
                try{
                    $parentThemeOptions = Yaml::parse(file_get_contents($parentYamlFilePath));
                }catch (\Exception $e){
                    $parentThemeOptions = [];
                }
            }
            $themeOptions = wp_parse_args($childThemeOptions,$parentThemeOptions);
            return $themeOptions;
        }
        $yamlFilePath = get_template_directory().'/theme-options.yml';
        if(\is_file($yamlFilePath)){
            try{
                $themeOptions = Yaml::parse(file_get_contents($yamlFilePath));
                return $themeOptions;
            }catch (\Exception $e){
                return [];
            }
        }
        return [];
    }

    /**
     * Get a theme option from the yaml file
     *
     * @param string $optionName
     * @param string $default
     * @return string
     */
    public function getThemeOption(string $optionName, string $default = ''): string
    {
        $themeOptions = $this->getThemeOptions();
        if(\is_array($themeOptions) && count($themeOptions) !== 0){
            if(\array_key_exists($optionName,$themeOptions)){
                $value = $themeOptions[$optionName];
                //Replace special strings
                $value = str_replace('{{ WP_CONTENT_DIR }}', WP_CONTENT_DIR, $value);
                $value = str_replace('{{ WP_CONTENT_URI }}', Utilities::pathToUrl(WP_CONTENT_DIR), $value);
                $value = str_replace('{{ PARENT_DIR }}', get_template_directory(), $value);
                $value = str_replace('{{ PARENT_URI }}', get_template_directory_uri(), $value);
                $value = str_replace('{{ THEME_DIR }}', get_stylesheet_directory(), $value);
                $value = str_replace('{{ THEME_URI }}', get_stylesheet_directory_uri(), $value);
                return $value;
            }
        }
        return $default;
    }

    /**
     * Handles the different theme options values that can be set for archives pages. If the $taxonomy is 'category', then
     * the Blog options are used, otherwise the function looks for the option specific to that $taxonomy.
     *
     * @param string $provided_option_name (without suffix, so for: 'blog_display_title', 'display_title' is enough )
     * @param string|false $taxonomy
     *
     * @return string
     */
    function getArchiveOption($provided_option_name,$taxonomy = null){
        if(!isset($taxonomy)){
            $taxonomy = Utilities::getCurrentTaxonomy();
        }

        if(!$taxonomy && is_archive()){
            global $wp_query;
            $taxonomy = $wp_query->query['post_type'] ?? false;
        }

        $taxonomy = apply_filters('waboot/archive_option/taxonomy',$taxonomy);

        $default_value = $this->getThemeOption('blog_'.$provided_option_name); //Default to blog values

        if($taxonomy === 'category' || !$taxonomy){
            $option_name = 'blog_' .$provided_option_name;
        }else{
            $option_name = 'archive_' .$taxonomy. '_' .$provided_option_name;
        }

        $value = $this->getThemeOption($option_name,$default_value);

        return $value;
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
}