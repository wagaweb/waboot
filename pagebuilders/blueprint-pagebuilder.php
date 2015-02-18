<?php

class BlueprintPageBuilder extends \WBF\modules\pagebuilder\PageBuilder {
	var $blocks = array(
		'container',
		'row'
	);

	var $containers = array(
		'column'
	);

	function container() {
		return "<div class='container'></div>";
	}

	function row() {
		return "<div class='row'></div>";
	}
}