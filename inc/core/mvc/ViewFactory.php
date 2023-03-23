<?php

namespace Waboot\inc\core\mvc;

class ViewFactory
{
    /**
     * @throws ViewException
     */
    static function createHtmlView(string $templateFile, array $args = [], bool $pathIsRelative = true): HTMLView
    {
        $m = new HTMLView($templateFile,$pathIsRelative);
        if(!empty($args)){
            foreach ($args as $k => $v){
                $m->setVar($k,$v);
            }
        }
        return $m;
    }
}