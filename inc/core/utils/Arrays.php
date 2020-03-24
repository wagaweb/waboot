<?php
namespace Waboot\inc\core\utils;

trait Arrays {
    /**
     * Search $array for the $key=>$value pair.
     *
     * @param array $array the target array
     * @param mixed $key the key to find
     * @param mixed $value the value to find into the $key
     *
     * @return array with the found pairs, or empty.
     */
    static function associativeArraySearch($array,$key,$value){
        $search_r = function($array, $key, $value, &$results, $subarray_key = null) use(&$search_r){
            if (!is_array($array)) {
                return;
            }

            if (isset($array[$key]) && $array[$key] == $value) {
                if(isset($subarray_key))
                    $results[$subarray_key] = $array;
                else
                    $results[] = $array;
            }

            foreach ($array as $k => $subarray) {
                $search_r($subarray, $key, $value, $results, $k);
            }
        };
        $results = array();
        $search_r($array, $key, $value, $results);
        return $results;
    }

    /**
     * Insert an $element after $key in $array (associative)
     *
     * @param array $element
     * @param string $key
     * @param array $array
     *
     * @return array
     */
    static function associativeArrayAddElementAfter(array $element,$key,array $array){
        $i = 1;
        foreach($array as $k => $v){
            if($k == $key){
                break;
            }
            $i++;
        }
        $head = array_slice($array,0,$i,true);
        $tail = array_slice($array,$i);
        $result = array_merge($head,$element);
        $result = array_merge($result,$tail);
        return $result;
    }

    /**
     * Get the next and prev element in an array relative to the current
     *
     * @param array $arr of items
     * @param string $key of current item
     * @return array
     */
    static function arrayNeighbor($arr, $key){
        $keys = array_keys($arr);
        $keyIndexes = array_flip($keys);

        $return = array();
        if (isset($keys[$keyIndexes[$key]-1])) {
            $return[] = $keys[$keyIndexes[$key]-1];
        }
        else {
            $return[] = $keys[sizeof($keys)-1];
        }

        if (isset($keys[$keyIndexes[$key]+1])) {
            $return[] = $keys[$keyIndexes[$key]+1];
        }
        else {
            $return[] = $keys[0];
        }

        return $return;
    }

    /**
     * Recursive version of array_diff
     *
     * @link http://stackoverflow.com/questions/3876435/recursive-array-diff
     *
     * @param array $arr1
     * @param array $arr2
     *
     * @return array
     */
    static function recursiveArrayDiff($arr1, $arr2) {
        $outputDiff = [];
        foreach ($arr1 as $key => $value) {
            //if the key exists in the second array, recursively call this function
            //if it is an array, otherwise check if the value is in arr2
            if (array_key_exists($key, $arr2)) {
                if (is_array($value)) {
                    $recursiveDiff = self::recursiveArrayDiff($value, $arr2[$key]);
                    if (count($recursiveDiff)) {
                        $outputDiff[$key] = $recursiveDiff;
                    }
                }
                else if (!in_array($value, $arr2)) {
                    $outputDiff[$key] = $value;
                }
            }
            //if the key is not in the second array, check if the value is in
            //the second array (this is a quirk of how array_diff works)
            else if (!in_array($value, $arr2)) {
                $outputDiff[$key] = $value;
            }
        }
        return $outputDiff;
    }

    /**
     * Recursive version of array_diff_assoc
     *
     * @link https://www.drupal.org/files/1850798-base-array_recurse-drupal-68.patch
     *
     * @param array $array1
     * @param array $array2
     *
     * @return array
     */
    static function recursiveArrayDiffAssoc($array1, $array2) {
        $difference = array();
        foreach ( $array1 as $key => $value ) {
            if ( is_array( $value ) ) {
                if ( ! array_key_exists( $key, $array2 ) || ! is_array( $array2[ $key ] ) ) {
                    $difference[ $key ] = $value;
                } else {
                    $new_diff = self::recursiveArrayDiffAssoc( $value, $array2[ $key ] );
                    if ( ! empty( $new_diff ) ) {
                        $difference[ $key ] = $new_diff;
                    }
                }
            } elseif ( ! array_key_exists( $key, $array2 ) || $array2[ $key ] !== $value ) {
                $difference[ $key ] = $value;
            }
        }
        return $difference;
    }

    /**
     * Guess what :)
     *
     * @param $needle
     * @param $haystack
     * @return bool|int|string
     */
    static function recursiveArraySearch($needle,$haystack) {
        foreach($haystack as $key=>$value) {
            $current_key=$key;
            if($needle===$value OR (is_array($value) && recursive_array_search($needle,$value) !== false)) {
                return $current_key;
            }
        }
        return false;
    }

    /**
     * Check if $thing is an array with at least one value
     *
     * @param mixed $thing
     *
     * @return bool
     */
    static function isIterable($thing){
        return \is_array($thing) && \count($thing) !== 0;
    }

    /**
     * json_encode() version that generate a string ready to be put in an HTML data-* attribute
     *
     * @param mixed $thing
     *
     * @return string
     */
    static function jsonEncodeForHtmlDataAttr($thing){
        return htmlspecialchars(json_encode($thing),ENT_QUOTES,'UTF-8');
    }
}