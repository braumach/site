require(['jquery', 'jquery/ui'], function($){

	$(document).ready(function(){

        // Mark _active
        $('.top-wrapper:first, .gallery-thumb:first').addClass('_active');
        $('.gallery-thumb._active').css('opacity',1);

        // Set parent container height base on absolute child
        $('.column.main.layout_2').css('height',$('.top-wrapper._active').height());
        
        // Positioning product contents for slider
        var x = 0;
        $('.top-wrapper').each(function(){
            var width = $(this).width();
            $(this).css({
                'left': x + 'px'
            });
            x = x + width;
        });     

        
        // Positioning Nav thumb images for slider
        var x = 0;
        $('.gallery-thumb').each(function(){
            var width = $(this).width();
            $(this).css({
                'left': x + 'px'
            });

            x = x + (width + 30);
        });

        // Display elements when ready
        $('.top-wrapper').css('display','block');
        $('.gallery-thumb').css('display','inline-block');
        $('.gallery-border').css('visibility','visible');
    });


    $('.gallery-thumb').click(function(){

        var selected = $(this).index();
        var current = $('.gallery-thumb._active').index();
        var left = $(this).position().left;

        // Remove _active class
        $('.top-wrapper._active').removeClass('_active');
        $('.gallery-thumb._active').removeClass('_active').css('opacity',0.5);

        // Moving class _active to the selected element
        $('.top-wrapper').eq(selected-1).addClass('_active');
        $(this).addClass('_active').css('opacity',1);
        
        // Moving gallery-border position on selected element
        $('.gallery-border').css('left',left);

        if (current < selected || selected != length) {
            $('.product-container').css({
                'transform':'translate3d(-'+ $('.top-wrapper._active').position().left +'px,0px,0px)'
            });
        } else {
            $('.product-container').css({
                'transform':'translate3d('+ $('.top-wrapper._active').position().left +'px,0px,0px)'
            });
        } 

        // Adjust product container height base on active content
        $('.column.main.layout_2').css('height',$('.top-wrapper._active').height());
    });

    $('.mobile-nav a').click(function(e){


        // Prevent scroll top
        e.preventDefault();

        var topLength = $('.top-wrapper').length;
        var clicked = $(this).index();
        var active = $('.top-wrapper._active').index();

        if (clicked == 0 && active != 0 && active <= topLength-1) {

            $('.top-wrapper._active').removeClass('_active').prev().addClass('_active');
            $('.product-container').css({
            'transform':'translate3d(-' + $('.top-wrapper._active').position().left +'px,0px,0px)'
            });
        } else if(clicked==1 && active != topLength-1) {

            $('.top-wrapper._active').removeClass('_active').next().addClass('_active'); 
            $('.product-container').css({
            'transform':'translate3d(-' + $('.top-wrapper._active').position().left +'px,0px,0px)'
            });
        }

        // Moving gallery-border position on selected element
        $('.gallery-thumb._active').removeClass('_active').css('opacity',0.5);
        $('.gallery-thumb:eq('+ $('.top-wrapper._active').index() +')').addClass('_active').css('opacity',1);
        $('.gallery-border').css('left',$('.gallery-thumb._active').position().left);

        // Adjust product container height base on active content
        $('.column.main.layout_2').css('height',$('.top-wrapper._active').height());

        // SHOW / HIDE ARROW NAV
        var active = $('.top-wrapper._active').index();

        if((active == topLength-1 || active == 0) && topLength != 2) {
            $(this).css({
                'visibility':'hidden'
            });
        }else {
            $('.mobile-nav a').css({
                'visibility':'visible'
            });
        }
    });



    // AJAX to update search widget base on current search

    function urldecode(str) {
       return decodeURIComponent((str+'').replace(/\+/g, '%20'));
    }

    function urlParam(name) {
        var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
        return results[1] || 0;
    }
 });