<?php

namespace WBF\includes\compiler\less;

class Less_Cache extends \Less_Cache{

    /**
     * Check if less files needs to be compiled [Added by LostCore]
     * @param $less_files
     * @param $cache_dir
     * @return bool
     */
    public static function needs_to_compile($less_files,$cache_dir){
        $less_files = (array)$less_files;
        $hash = md5(json_encode($less_files));
        $list_file = $cache_dir.'/lessphp_'.$hash.'.list';

        if( file_exists($list_file) ){
            $list = explode("\n",file_get_contents($list_file));
            $compiled_name = self::CompiledName($list);
            $compiled_file = $cache_dir."/".$compiled_name;
            if( file_exists($compiled_file) ){
                return false;
            }else{
                return true;
            }
        }else{
            return true;
        }
    }

    /**
     * Clone of parent CompiledName() method (which is private and cannot be used here unless redefined)
     * @param $files
     * @return string
     */
    private static function CompiledName( $files ){
        //save the file list
        $temp = array(\Less_Version::cache_version);
        foreach($files as $file){
            $temp[] = filemtime($file)."\t".filesize($file)."\t".$file;
        }

        return 'lessphp_'.sha1(json_encode($temp)).'.css';
    }
}

