<?php
namespace Waboot\inc\core\mvc;

abstract class View{
    /**
     * @var array
     */
    protected array $template;

    /**
     * @var array
     */
    protected array $args;

    /**
     * Initialize a new view. If the $plugin argument is not provided, the template file will be searched into stylesheet and template directories.
     *
     * @param string $filePath a path to the view file. Must be absolute unless $is_relative_path is TRUE.
     * @param bool $isRelativePath if true, the $file_path is intended to relative to theme directory
     *
     * @throws ViewException
     */
    public function __construct(string $filePath, bool $isRelativePath = true)
    {
        if(empty($filePath)){
            throw new ViewException('Cannot create View, invalid file path');
        }

        //Tries to force file extension
        //todo: decide if do this or not
        $pinfo = pathinfo($filePath);
        if(!isset($pinfo['extension'])){
            $filePath .= '.php';
        }

        if($isRelativePath){
            $search_paths = $this->getSearchPaths($filePath);
            //Searching for template
            foreach($search_paths as $path){
                if(file_exists($path)){
                    $abs_path = $path;
                    break;
                }
            }
        }else{
            $abs_path = $filePath;
        }

        if(!isset($abs_path) || !file_exists($abs_path)){
            if(!isset($search_paths)){
                $search_paths = [$filePath];
            }
            $message = 'File ' .$filePath. ' does not exists in any of these locations: ' .implode(",\n",$search_paths);
            throw new ViewException( $message );
            //throw new ViewNotFoundException($file_path,$search_paths);
        }

        $this->template = pathinfo($abs_path);
        $this->args = [
            'page_title' => '',
            'wrapper_class' => '',
            'wrapper_el' => '',
            'title_wrapper' => '%s'
        ];
    }

    /**
     * @param array $args
     * @return View
     */
    public function setArgs(array $args): View
    {
        $this->args = $args;
        return $this;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return View
     */
    public function setVar(string $name, $value): View
    {
        $this->args[$name] = $value;
        return $this;
    }

    /**
     * Clean the predefined args, providing a clean template.
     * @return $this
     */
    public function clean(): View
    {
        $this->setVar('page_title','');
        $this->setVar('wrapper_class','');
        $this->setVar('wrapper_el','');
        $this->setVar('title_wrapper','%s');
        return $this;
    }

    /**
     * Populate the predefined args, providing a template ready for being displayed in WP dashboard
     *
     * @param array $specificClasses an array of classes to append to the canonical 'wrap'.
     *
     * @return $this
     */
    public function forDashboard(array $specificClasses = []): View
    {
        $this->setVar('page_title','Page Title');
        if(!empty($specificClasses)){
            $this->setVar('wrapper_class','wrap ' .implode(' ',$specificClasses));
        }else{
            $this->setVar('wrapper_class','wrap');
        }
        $this->setVar('wrapper_el','div');
        $this->setVar('title_wrapper','<h1>%s</h1>');
        return $this;
    }

    /**
     * Get the search paths given the $relative_file_path.
     *
     * @param $relativeFilePath
     *
     * @return array
     */
    private function getSearchPaths($relativeFilePath): array
    {
        $search_paths = [];
        $searchIn = [
            rtrim(get_stylesheet_directory()),
            rtrim(get_template_directory())
        ];
        foreach($searchIn as $template_dir){
            $search_paths[] = $template_dir. '/' .$relativeFilePath;
        }
        $search_paths = array_unique($search_paths); //Clean up
        return $search_paths;
    }
}