jQuery( document ).ready( function ( $ ) {

    var ISSettings = window._sedInfiniteScrollSettings;

    if( ! _.isUndefined( ISSettings.items ) && ! _.isEmpty( ISSettings.items ) ){

        var maxPages = {};

        _.each( ISSettings.items , function( options , key ){

            var newOptions = $.extend( {}, options.defaults, options ) ,
                $infinte_scroll_container = $( newOptions.contentSelector );

            maxPages[key] = 0;

            var callback = function (newElements, data, url) {

                if( !_.isUndefined( newOptions.callback ) ) {
                    eval(newOptions.callback);
                }

                maxPages[key] += 1;

                if( !_.isUndefined( data.maxPage )  && ( data.maxPage - 1 ) == maxPages[key] ){

                    $( newOptions.buttonSelector ).hide();

                }

            };

            var errorCallback = function(){

                if( !_.isUndefined( newOptions.errorCallback ) ) {
                    eval(newOptions.errorCallback);
                }

                if( ! _.isUndefined( newOptions.type ) && newOptions.type == "load_more_button" ){

                    $( newOptions.buttonSelector ).hide();

                }

            };

            var finalOptions = $.extend( true , {} , newOptions );

            if( !_.isUndefined( finalOptions.errorCallback ) ) {
                delete finalOptions.errorCallback;
            }

            finalOptions.errorCallback = errorCallback;

            $infinte_scroll_container.infinitescroll(finalOptions, callback );


            if( ! _.isUndefined( newOptions.type ) && newOptions.type == "load_more_button" ){

                $infinte_scroll_container.infinitescroll( 'unbind' );

                $( newOptions.navSelector ).hide();

                $( newOptions.buttonSelector ).on( 'click', function(e) {
                    e.preventDefault();

                    // Use the retrieve method to get the next set of posts
                    $infinte_scroll_container.infinitescroll( 'retrieve' );

                });

            }

        });

    }

});



/**
 * All Options :


 loading: {
        finished: undefined,
        finishedMsg: "<em>Congratulations, you've reached the end of the internet.</em>",
        img: null,
        msg: null,
        msgText: "<em>Loading the next set of posts...</em>",
        selector: null,
        speed: 'fast',
        start: undefined
    },
 state: {
        isDuringAjax: false,
        isInvalidPage: false,
        isDestroyed: false,
        isDone: false, // For when it goes all the way through the archive.
        isPaused: false,
        currPage: 1
    },
 behavior: undefined,
 binder: $(window), // used to cache the selector for the element that will be scrolling
 nextSelector: "div.navigation a:first",
 navSelector: "div.navigation",
 contentSelector: null, // rename to pageFragment
 extraScrollPx: 150,
 itemSelector: "div.post",
 animate: false,
 pathParse: undefined,
 dataType: 'html',
 appendCallback: true,
 bufferPx: 40,
 errorCallback: function () { },
 infid: 0, //Instance ID
 pixelsFromNavToBottom: undefined,
 path: undefined, // Can either be an array of URL parts (e.g. ["/page/", "/"]) or a function that accepts the page number and returns a URL
 maxPage:undefined // to manually control maximum page (when maxPage is undefined, maximum page limitation is not work)
 *
 */