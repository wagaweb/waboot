<?php
namespace Waboot\inc\core\mvc;

interface ViewInterface{
    public function display($vars = []);
    public function get($vars = []);
}