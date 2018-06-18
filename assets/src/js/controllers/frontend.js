import * as FS from "FastClick";
import $ from "jquery";

export default class{
    constructor() {
        "use strict";
        this.on_ready();
    }
    on_ready(){
        "use strict";
        if (wbData.isMobile){
            this.do_mobile_actions();
        }
    }
    
    /**
     * Performs mobile oriented actions
     */
    do_mobile_actions(){
        "use strict";

        //https://getbootstrap.com/docs/3.3/getting-started/#third-parties
        if (navigator.userAgent.match(/IEMobile\/10\.0/)) {
            let msViewportStyle = document.createElement('style');
            msViewportStyle.appendChild(
                document.createTextNode(
                    '@-ms-viewport{width:auto!important}'
                )
            );
            document.querySelector('head').appendChild(msViewportStyle);
        }

        if(typeof FS !== "undefined"){
            //Prepare fastclick-select2 fix
            //https://github.com/select2/select2/issues/3222
            //https://github.com/almasaeed2010/AdminLTE/issues/802
            //https://github.com/select2/select2/issues/3381
            if(typeof jQuery.fn.select2 !== "undefined"){
                let $customSelects = $('select'),
                    $select2_containers = $(".select2-container");

                $customSelects.addClass("needsclick");
                $select2_containers.find("span").addClass("needsclick");
                $select2_containers.find("*").addClass("needsclick");
            }

            //Attach Fastclickl
            FS.FastClick.attach(document.body);
        }
    }
}