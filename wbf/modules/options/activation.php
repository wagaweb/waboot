<?

namespace WBF\modules\options;

add_action("wbf_activated",'\WBF\modules\options\set_theme_option_root_id');

function set_theme_option_root_id(){
	Framework::set_theme_option_default_root_id();
}