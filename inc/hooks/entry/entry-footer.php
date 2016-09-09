<?php

namespace Waboot\hooks\entry;

/**
 * Prints out the entry footer wrapper start
 */
function entry_footer_wrapper_start(){
	echo '<footer class="entry-footer">';
}
add_action("waboot/entry/footer",__NAMESPACE__."\\entry_footer_wrapper_start",10);

/**
 * Prints out the entry footer wrapper end
 */
function entry_footer_wrapper_end(){
	echo '</footer>';
}
add_action("waboot/entry/footer",__NAMESPACE__."\\entry_footer_wrapper_end",9999);