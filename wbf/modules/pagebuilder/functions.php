<?php

namespace WBF\modules\pagebuilder;

function init() {
	if(!get_current_builder()) return;
    Manager::init();
    GUI::init();
}

function set_current_builder() {
    $builder_classname = ucfirst( get_current_builder() ) . "PageBuilder";
    $builder_filename  = lcfirst( get_current_builder() ) . "-pagebuilder.php";
    require_once PAGEBUILDERS_PATH . $builder_filename;
    if ( class_exists( $builder_classname ) ) {
        $GLOBALS['wbpagebuilder'] = new $builder_classname();
    } else {
        $GLOBALS['wbpagebuilder'] = false;
    }
}

function get_current_builder($obj = false) {
	if(!function_exists("theme_get_pagebuilder")){
		return false;
	}
	$builder_name = theme_get_pagebuilder(); //"bootstrap";
    if(!$obj) return $builder_name;
    else{
        $builder_classname = ucfirst( get_current_builder() ) . "PageBuilder";
        $builder_filename  = lcfirst( get_current_builder() ) . "-pagebuilder.php";
        require_once PAGEBUILDERS_PATH . $builder_filename;
        if ( class_exists( $builder_classname ) ) {
            return new $builder_classname();
        }else{
            return false;
        }
    }
}

/** HELPERS AND DEVS **/

function wbpb_json_encode($array){
    $doing_ajax = false;
    if(isset($_POST['array'])){
        $doing_ajax = true;
        foreach($_POST['array'] as $k => $v){
            $_POST['array'][$k] = stripslashes($v);
        }
        $array = $_POST['array'];
    }

    $json = json_encode($array, JSON_HEX_QUOT | JSON_HEX_TAG);

    if($doing_ajax){
        echo $json;
        die();
    }
    return $json;
}

function wbpb_json_decode($json){
    $doing_ajax = false;
    if(isset($_POST['json'])){
        $doing_ajax = true;
        $json = stripslashes($_POST['json']);
    }
    $decoded = json_decode($json,TRUE);

    if($doing_ajax){
        echo $decoded;
        die();
    }
    return $decoded;
}

/**
 * Get last key of an array
 * @param $array
 *
 * @return mixed
 */
function get_last_key($array){
	end($array);
	return key($array);
}

function wbpb_demo_content() {
    /**
     * DEMO
     */
    /*
    <div class="pbrow ui-sortable" data-children="2" data-max-children="4" data-max-children-per-row="4" data-block="row" data-selectable="" data-sortable="pbcolumn" id="row-1">
        <div class="tools"><a class="label label-danger remove">X</a><a class="label label-default drag">D</a>
        </div>
        <div class="pbcolumn ui-sortable" data-children="0" data-max-children="1" data-block="column" data-selectable="" data-sortable="widget" id="column-3" data-colspan="2">
            <div class="tools"><a class="label label-danger remove">X</a><a class="label label-default drag">D</a>
            </div>
            <div class="pbhtml widget" data-children="0" data-block="text" id="text-5">
                <div class="tools"><a class="label label-danger remove">X</a><a class="label label-default edit">E</a>
                </div>HTML</div>
        </div>
        <div class="pbcolumn ui-sortable" data-children="0" data-max-children="1" data-block="column" data-selectable="" data-sortable="widget" id="column-4" data-colspan="2">
            <div class="tools"><a class="label label-danger remove">X</a><a class="label label-default drag">D</a>
            </div>
        </div>
    </div>
    <div class="pbrow ui-sortable ui-selected" data-children="1" data-max-children="4" data-max-children-per-row="4" data-block="row" data-selectable="" data-sortable="pbcolumn" id="row-2">
        <div class="tools"><a class="label label-danger remove">X</a><a class="label label-default drag">D</a>
        </div>
        <div class="pbcolumn" data-children="0" data-max-children="1" data-block="column" data-selectable="" data-sortable="widget" id="column-6" data-colspan="4">
            <div class="tools"><a class="label label-danger remove">X</a><a class="label label-default drag">D</a>
            </div>
        </div>
    </div>
     */
    $content = "<div class=\"pbrow ui-sortable\" data-children=\"2\" data-max-children=\"4\" data-max-children-per-row=\"4\" data-block=\"row\" data-selectable=\"\" data-sortable=\"pbcolumn\" id=\"row-1\"><div class=\"tools\"><a class=\"label label-danger remove\">X</a><a class=\"label label-default drag\">D</a></div><div class=\"pbcolumn ui-sortable\" data-children=\"0\" data-max-children=\"1\" data-block=\"column\" data-selectable=\"\" data-sortable=\"widget\" id=\"column-3\" data-colspan=\"2\"><div class=\"tools\"><a class=\"label label-danger remove\">X</a><a class=\"label label-default drag\">D</a></div><div class=\"pbhtml widget\" data-children=\"0\" data-block=\"text\" id=\"text-5\"><div class=\"tools\"><a class=\"label label-danger remove\">X</a><a class=\"label label-default edit\">E</a></div>HTML</div></div><div class=\"pbcolumn ui-sortable\" data-children=\"0\" data-max-children=\"1\" data-block=\"column\" data-selectable=\"\" data-sortable=\"widget\" id=\"column-4\" data-colspan=\"2\"><div class=\"tools\"><a class=\"label label-danger remove\">X</a><a class=\"label label-default drag\">D</a></div></div></div><div class=\"pbrow ui-sortable ui-selected\" data-children=\"1\" data-max-children=\"4\" data-max-children-per-row=\"4\" data-block=\"row\" data-selectable=\"\" data-sortable=\"pbcolumn\" id=\"row-2\"><div class=\"tools\"><a class=\"label label-danger remove\">X</a><a class=\"label label-default drag\">D</a></div><div class=\"pbcolumn\" data-children=\"0\" data-max-children=\"1\" data-block=\"column\" data-selectable=\"\" data-sortable=\"widget\" id=\"column-6\" data-colspan=\"4\"><div class=\"tools\"><a class=\"label label-danger remove\">X</a><a class=\"label label-default drag\">D</a></div></div></div>";

    return $content;
}