// Set options
        var options = {
            offset: 200 ,
        //  offset: '#showHere',
            classes: {
                clone:   'header-clone',
                stick:   'header-stick',
                unstick: 'header-unstick'
            }
        };

        // Initialise with options
        var banner = new Headhesive('#header-wrapper', options);

        // Headhesive destroy
        // banner.destroy();