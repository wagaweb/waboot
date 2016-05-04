import FrontEnd from "./controllers/frontend.js";
import Dashboard from "./controllers/dashboard.js";

jQuery(document).ready(function($) {
    "use strict";
    if (wbData.isAdmin) {
        /*************
         *************
         * ADMIN
         *************
         *************/
        new Dashboard();
    }else{
        /*************
         *************
         * PUBLIC
         *************
         *************/
        new FrontEnd();
    }
});
