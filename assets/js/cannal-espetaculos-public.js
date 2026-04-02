(function($) {
    'use strict';

    $(document).ready(function() {
        // Inicializar Fancybox para galeria de espetáculos
        if (typeof Fancybox !== 'undefined') {
            Fancybox.bind('[data-fancybox="galeria-espetaculo"]', {
                Toolbar: {
                    display: {
                        left: [],
                        middle: [],
                        right: ["close"],
                    },
                },
                Images: {
                    zoom: true,
                },
                Thumbs: {
                    autoStart: true,
                },
            });
        }
    });

})(jQuery);
