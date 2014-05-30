<?php

require_once("Waboot_Cache.php");

function checkCompile(){
    $compile_sets = apply_filters('waboot_compile_sets',array());
    $waboot_less_compiler = new Waboot_Less_Compiler($compile_sets);
    echo $waboot_less_compiler->needs_to_compile("theme_frontend");
    die();
}

function compileLess(){
    $compile_sets = apply_filters('waboot_compile_sets',array());
    $waboot_less_compiler = new Waboot_Less_Compiler($compile_sets);
    echo $waboot_less_compiler->compile();
    die();
}

class Waboot_Less_Compiler{
    public $compile_sets = array();

    function __construct($compile_sets){
        $this->compile_sets = $compile_sets;
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
            foreach($this->compile_sets as $set){
                $this->compile_set($set);
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

    function compile_set($set){
        try{
            $less_files = array(
                $set['input'] => $set['import_url'],
            );

            $parser_options = array(
                'cache_dir'         => $set['cache'],
                'compress'          => false,
                'sourceMap'         => true,
                'sourceMapWriteTo'  => $set['map'],
                'sourceMapURL'      => $set['map_url'],
            );

            if(Waboot_Cache::needs_to_compile($less_files,$set['cache'])){
                $css_file_name = Less_Cache::Get(
                    $less_files,
                    $parser_options
                );

                $css = file_get_contents( $set['cache'].'/'.$css_file_name );
                file_put_contents($set['output'], $css);
            }

            return true;
        }catch(exception $e){
            throw $e;
        }
    }
}