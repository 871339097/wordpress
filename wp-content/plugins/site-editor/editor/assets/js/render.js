(function($) {
    
    /**====================================================
    * for right side panel like page & ....
    *
    $( "#effect" ).hide();
    $( "#button" ).click(function() {
        $( "#effect" ).toggle("slide");
        if($( "#button span" ).hasClass('right-open')){
            $( "#button span" ).removeClass("right-open").addClass("left-open");
        }else if($( "#button span" ).hasClass('left-open')){
            $( "#button span" ).removeClass("left-open").addClass("right-open");
        }

    });

    $('#sed-right-side-panel a.iconf2').click(function (e) {
        e.preventDefault();
        $( "#effect" ).show("slide");

        if($( "#button span" ).hasClass('right-open')){
            $( "#button span" ).removeClass("right-open").addClass("left-open");
        }
    });

    $('#sed-right-side-panel a').click(function (e) {
        e.preventDefault();
        $(this).tab('show');
    });
    =======================================================*/
    
    //tooltip for settings help
    $('.field_desc').livequery( function(){
      $(this).tooltip({
          html : true ,
          placement : "auto top" ,
      });
    });

    //search modules
    $('#key_module').keyup(function(){
      var filter = $(this).val(), count = 0;
      // Loop through the icon list
      $("#modules-tab-content .sed-module-pb").each(function(){

          // If the list item does not contain the text phrase fade it out
          //
          key = $(this).attr("sed-module-name");
          $parent = $(this).parent();
          if( filter != "" ){
            $('#other_modules_panel_items .heading-item').parent().hide();
            if ( key.search( new RegExp(filter, "i") ) < 0) {

              if( $(this).parents('#other_modules_panel_items').length > 0 )
                $parent.fadeOut();
              else
                if($parent.hasClass('highlight'))
                  $parent.removeClass('highlight');
            } else {

              if( $(this).parents('#other_modules_panel_items').length > 0 )
                $parent.show();
              else
                $parent.addClass('highlight');

                count++;
            }
          }else{
            $('#other_modules_panel_items .heading-item').parent().show();
            if($parent.hasClass('highlight'))
              $parent.removeClass('highlight');
            else
              $parent.show();
          }
      });
    });

    // for main site editor app tap( header or toolbar tab )
    $('#myTab a').click(function (e) {
        e.preventDefault();
        $(this).tab('show');
    });


    // for all dialog custom scroll bar
    $(".content").mCustomScrollbar({
        //autoHideScrollbar:true ,
        advanced:{
            updateOnBrowserResize:true, /*update scrollbars on browser resize (for layouts based on percentages): boolean*/
            updateOnContentResize:true,
        },
        callbacks:{
            onOverflowY:function(){
               $(this).find(".mCSB_container").addClass("mCSB_ctn_margin");
            },

            onOverflowYNone:function(){
              $(this).find(".mCSB_container").removeClass("mCSB_ctn_margin");
            }
        }
    });

    //for jquery ui spinner
    $('.ui-spinner-input').livequery(function(){

        icons = {
            down: "icon-expand-less",
            up: "icon-expand-more"
        };

        var spinner = $(this).spinner({
            icons : icons,
        });

    });

    //for chosen select
    $("select.sed-custom-select").livequery(function(){
        $(this).select2({
            placeholder: 'Select an option' ,
            allowClear: true
        });
    });

    /**
     * for module & widget group and show in left panel
     * @param label
     * @param type
     * @returns {*}
     */
    var addGroupToModulesPanel = function ( label , type ){
        var html = $("#tmpl-" + type + "-group-panel").html();
        html = html.replace("{{GroupLabel}}" , label);
        var group = $( html ).appendTo( $("#other_" + type + "_panel_items") );
        return group;
    };

    var createOtherPanel = function( type ){

        var $tb_content_width = $("#" + type + "-tab-content > .tab_inner").width() ,
            $width   = 0 ,
            $last_i  = 0 ,
            $last_i2 = 0 ,
            $tb_content_children_length = $("#" + type + "-tab-content .tab_inner_content").children(".element_group").length ,
            $wspr = $("#" + type + "-tab-content > .tab_inner .spr").outerWidth(),
            $wtd = $("#" + type + "-tab-content .iconz_table > tbody > tr > td").outerWidth(),
            $groups = $("#" + type + "-tab-content > .tab_inner   .element_group") ;

        $groups.each(function(index , element){
            var $i_width = $(this).outerWidth( true );
            $width += $i_width + $wspr;
            if($width >= $tb_content_width ){
                $mode = ( ($width - $wspr) == $tb_content_width) ? 1:2;
                $last_i = index;
                $last_i_width = $i_width;
                return false;
            }
        });
        $last_i2 = ($last_i * 2) - 1;

        $width -= $wspr;

        if( $last_i == ($tb_content_children_length - 1) && $width == $tb_content_width)
            return ;



        if($last_i <= ($tb_content_children_length - 1) && $width >= $tb_content_width){
            $('#other_' + type + '_panel').show();
            if($mode == 2){
                var w1 = $width - $tb_content_width , w2 = $last_i_width - w1 ,
                    lastChildVisible = Math.floor( w2 / $wtd ) - 1,
                    $dGroup = $groups.eq($last_i),
                    group = addGroupToModulesPanel( $dGroup.attr("data-group-label") , type ),
                    moduleItems = group.find(".module-items") ,
                    $moved = 0;

                    $dGroup.find(".iconz_table > tbody > tr > td").each(function(itd , vtd){
                        if(itd > lastChildVisible){
                            $moved++;
                            var $li = $("<li></li>").appendTo( moduleItems );
                            $(this).find(".sed-module-pb").appendTo( $li );
                        }
                    });

                    if( $moved == $dGroup.find(".iconz_table > tbody > tr > td").length ){
                        $dGroup.prev().hide();
                        $dGroup.hide();
                    }

                    $dGroup.next().hide();


            }

            if($last_i < ($tb_content_children_length - 1) ){
                $groups.each(function(index , element){
                   if(index > $last_i){
                      var group = addGroupToModulesPanel( $(this).attr("data-group-label") , type ),
                          moduleItems = group.find(".module-items");

                      $(this).find(".iconz_table > tbody > tr > td").each(function(itd , vtd){

                         var $li = $("<li></li>").appendTo( moduleItems );
                         $(this).find(".sed-module-pb").appendTo( $li );

                      });

                      $(this).hide();
                      $(this).next().hide();

                   }
                });
            }

        }

    };

    var initializ_modules_tab = false;
    //other modules panel
    $('#myTab #modules a').on('shown.bs.tab', function (e) {

        if( initializ_modules_tab === false){
            createOtherPanel( "modules" );
            initializ_modules_tab = true;
        }

    });

    var initializ_widgets_tab = false;
    //other modules panel
    $('#myTab #widgets a').on('shown.bs.tab', function (e) {

        if( initializ_widgets_tab === false){
            createOtherPanel( "widgets" );
            initializ_widgets_tab = true;
        }

    });

    $( "#other-modules-toggle" ).click(function() {
        $( ".dropdown-other-modules" ).toggle( "slow" , "linear" );
    });

    $( "#other-widgets-toggle" ).click(function() {
        $( ".dropdown-other-widgets" ).toggle( "slow" , "linear" );
    });
    /****** END *****************/

})(jQuery);

