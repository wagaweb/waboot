<?php

require_once( "Waboot_Cache.php" );

class Waboot_Less_Compiler{
    public $compile_sets = array();
    private $compiling_flags;

    function __construct($compile_sets){
        $this->compile_sets = $compile_sets;
        $this->compiling_flags = get_option('waboot_compiling_less_flags');
        $this->update_compiling_flags();
    }

    private function update_compiling_flags()
    {
        $this->compiling_flags = get_option('waboot_compiling_less_flags');
        if (!$this->compiling_flags) {
            add_option("waboot_compiling_less_flags", array(), '', true);
            $this->compiling_flags = array();
        }
    }

    function needs_to_compile($set){
        if(!is_array($set)){
            $set = $this->compile_sets[$set];
        }

        $less_files = array(
            $set['input'] => $set['import_url'],
        );

        if(Waboot_Cache::needs_to_compile($less_files,$set['cache'])){
            if(isset($_SERVER['HTTP_X_REQUESTED_WITH'])){
                echo 1;
                die();
            }else{
                return true;
            }
        }else{
            if(isset($_SERVER['HTTP_X_REQUESTED_WITH'])){
                echo 0;
                die();
            }else{
                return false;
            }
        }
    }

    function compile(){
        try{
            foreach($this->compile_sets as $set_name => $set_args){
                $this->compile_set($set_name,$set_args);
            }
            if(isset($_SERVER['HTTP_X_REQUESTED_WITH'])){
                echo 1;
                die();
            }else{
                return true;
            }
        }catch(exception $e){
            if(isset($_SERVER['HTTP_X_REQUESTED_WITH'])){
                echo 0;
                die();
            }else{
                return false;
            }
        }
    }

    function compile_set($name,$args){
        global $wp_filesystem;

        try{
            $less_files = array(
                $args['input'] => $args['import_url'],
            );

            $parser_options = array(
                'cache_dir'         => $args['cache'],
                'compress'          => false,
                'sourceMap'         => true,
                'sourceMapWriteTo'  => $args['map'],
                'sourceMapURL'      => $args['map_url'],
            );

            if(!is_writable($args['cache'])){
                throw new Exception("Cache dir ({$args['cache']}) is not writeable");
            }

            if(Waboot_Cache::needs_to_compile($less_files,$args['cache']) && $this->can_compile($name)){
                //todo: forse sarebbe meglio un while che fa attendere il processo finché non può compilare?
                $this->set_compiling_flag($name,true);
                $css_file_name = Less_Cache::Get(
                    $less_files,
                    $parser_options
                );

                $css = file_get_contents( $args['cache'].'/'.$css_file_name );

                if(!is_writable($args['output'])){
                    throw new Exception("Output dir ({$args['output']}) is not writeable");
                }

                $wp_filesystem->put_contents( $args['output'], $css, FS_CHMOD_FILE );
                //file_put_contents($args['output'], $css);

                $this->set_compiling_flag($name,false);
            }

            return true;
        }catch(Exception $e){
            throw $e;
        }
    }

    private function can_compile($name){
        $this->update_compiling_flags();
        if(isset($this->compiling_flags[$name]) && $this->compiling_flags[$name] == true){
            return false;
        }else{
            return true;
        }
    }

    private function set_compiling_flag($name,$value){
        $this->update_compiling_flags();
        $compiling_flags = get_option("waboot_compiling_less_flags");
        if($compiling_flags != false){
            $compiling_flags[$name] = $value;
        }
        $this->compiling_flags[$name] = $value;
    }
}