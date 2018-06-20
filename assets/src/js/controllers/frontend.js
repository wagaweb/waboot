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
    }
}