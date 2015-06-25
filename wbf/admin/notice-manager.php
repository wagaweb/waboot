<?php

namespace WBF\admin;

class Notice_Manager {

    var $notices = array();

    function __construct(){
        $notices = $this->get_notices();
        if(!empty($notices)){
            foreach($notices as $id => $notice){
                if(isset($notice['condition']) && is_string($notice['condition'])){
                    if($this->conditions_met($notice)){ //Remove notices that mets the conditions
                        unset($notices[$id]);
                        continue;
                    }
                }
            }
            $this->update_notices($notices);
            $this->notices = $notices;
        }
    }

    function clear_notices($category = null){
        $notices = $this->get_notices();
        if(isset($category)){
            foreach($notices as $k => $notice){
                if($notice['category'] == $category){
                    unset($notices[$k]);
                }
            }
        }else{
            $notices = array();
        }

        $this->notices = $notices;
        $this->update_notices($notices);
    }

    function enqueue_notices(){
        add_action( 'admin_notices', array($this,'show_notices'));
    }

    function show_notices(){
        foreach($this->notices as $id => $notice){
            switch($notice['level']){
                case 'updated':
                    ?>
                    <div class="updated">
                        <p><?php echo $notice['message']; ?></p>
                    </div>
                    <?php
                    break;
                case 'error':
                    ?>
                    <div class="error">
                        <p><?php echo $notice['message']; ?></p>
                    </div>
                    <?php
                    break;
                case 'nag':
                    ?>
                    <div class="update-nag">
                        <p><?php echo $notice['message']; ?></p>
                    </div>
                    <?php
                    break;
            }
	        if($notice['category'] == "_flash_"){
		        $this->remove_notice($id);
	        }
        }
    }

    private function get_notices(){
        $notices = get_option("wbf_admin_notices",array());
        return $notices;
    }

	/**
	 * Add a new notice to the system
	 *
	 * @param String $id
	 * @param String $message
	 * @param String $level (can be: "updated","error","nag"
	 * @param String $category (can be anything. Categories are used to group notices for easy clearing them later. If the category is set to "_flash_", however, the notice will be cleared after displaying.
	 * @param null|String $condition a class name that implements Condition interface
	 * @param null|mixed $cond_args parameters to pass to $condition constructor
	 */
	function add_notice($id,$message,$level,$category = 'base', $condition = null, $cond_args = null){
        $notices = $this->get_notices();
        $notices[$id] = array(
            'message' => $message,
            'level'   => $level,
            'category' => $category,
            'condition' => $condition,
            'condition_args' => $cond_args
        );
        $this->notices = $notices;
        $this->update_notices($notices);
    }

    function remove_notice($id){
        $notices = $this->get_notices();
        if(isset($notices[$id])) unset($notices[$id]);
        $this->notices = $notices;
        $this->update_notices($notices);
    }

    function update_notices($notices){
	    $current_notices = get_option("wbf_admin_notices",array());
	    if(is_array($notices)){
		    $result = update_option("wbf_admin_notices", $notices);
	    }else{
		    $result = update_option("wbf_admin_notices", $current_notices);
	    }
        return $result;
    }

    private function conditions_met($notice){
        $className = "\WBF\admin\conditions\\".$notice['condition'];
        if(isset($notice['condition_args'])){
            $cond = new $className($notice['condition_args']);
        }else{
            $cond = new $className();
        }
        if($cond){
            if($cond->verify()){
                return true;
            }
        }
        return false;
    }
}