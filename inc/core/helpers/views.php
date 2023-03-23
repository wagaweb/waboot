<?php

namespace Waboot\inc\core\helpers;

use Waboot\inc\core\mvc\ViewException;
use Waboot\inc\core\mvc\ViewFactory;

/**
 * @param string $templateFile
 * @param array $args
 * @param bool $pathIsRelative
 * @return void
 */
function renderHtmlView(string $templateFile, array $args, bool $pathIsRelative = true): void {
    try{
        ViewFactory::createHtmlView($templateFile,$args,$pathIsRelative)->display();
    }catch (ViewException $e){}
}

/**
 * @param string $templateFile
 * @param array $args
 * @param bool $pathIsRelative
 * @return string
 */
function getHtmlView(string $templateFile, array $args, bool $pathIsRelative = true): string {
    try{
        return ViewFactory::createHtmlView($templateFile,$args,$pathIsRelative)->get();
    }catch (ViewException $e){
        return '';
    }
}