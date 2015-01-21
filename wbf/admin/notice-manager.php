<?php
/**
 * Created by PhpStorm.
 * User: wagadev
 * Date: 21/01/15
 * Time: 11.11
 */

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

    function clear_notices($id = null){
        $notices = $this->get_notices();
        if(isset($id)){
            foreach($notices as $k => $notice){
                if($k == $id){
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
        }
    }

    private function get_notices(){
        $notices = get_option("wbf_admin_notices",array());
        return $notices;
    }

    function add_notice($id,$message,$level,$condition = null, $cond_args = null){
        $notices = $this->get_notices();
        $notices[$id] = array(
            'message' => $message,
            'level'   => $level,
            'condition' => $condition,
            'condition_args' => $cond_args
        );
        $this->notices = $notices;
        $this->update_notices($notices);
    }

    function remove_notice($id){
        $notices = $this->get_notices();
        if(isset($notices['id'])) unset($notices['id']);
        $this->notices = $notices;
        $this->update_notices($notices);
    }

    function update_notices($notices){
        $result = update_option("wbf_admin_notices",(array) $notices);
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