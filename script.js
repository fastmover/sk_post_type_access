(function($){

    $(document).ready(function(){

        $('.accordion').accordion({
            active: false,
            autoHeight: false,
            collapsible: true,
            header: '> h3.parent',
            heightStyle: "content"
        });
//        $('.accordion2').accordion({
//            active: false,
//            autoHeight: false,
//            collapsible: true,
//            header: '> h4.child',
//            heightStyle: "content"
//        });

    });

})(jQuery);