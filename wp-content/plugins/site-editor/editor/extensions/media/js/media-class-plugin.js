/**
 * siteEditorCss.js
 *
 *
 * License: http://www.siteeditor.org/license
 * Contributing: http://www.siteeditor.org/contributing
 */

/*global diagram:true */
(function( exports, $ ){
	var api = sedApp.editor;
	
    var Attachments , Attachment ,
        Library , Query , media , Uploader;

    media = api.media = {};
   /*
     ( mediaLibraryLoad: function ( ){

          this.loading.removeClass( "empty-lib-loading" );
          this.loading.removeClass( "new-item-lib-loading" );
          this.loading.show();
          if(this.is_empty() === true){
              this.loading.addClass( "empty-lib-loading" );
          }else{
              this.loading.addClass( "new-item-lib-loading" );
          }


          var mediaAjaxloader = new api.Ajax({
              data : {
                  action              : 'load_medias',
                  nonce               : self.mediaSettings.nonce ,
                  post_mime_type      : self.mediaType,
                  offset              : self.startItem,
                  posts_per_page      : self.itemPerPage,
                  search              : self.keyword,
                  sed_page_ajax       : 'media_loader'
              },

      }
    } );  */

    var getMediaTypes = function(){
        return _.keys( api.mediaSettings.types );
    };

    var checkAttachmentType = function( ext , cType ){
        if(!ext)
            return ;

        var mType;
        $.each( api.mediaSettings.types , function( type , setting ){
            if($.inArray( ext , setting.ext) != -1){
                mType = type;
                return false;
            }
        });

        return mType == cType;
    };

	/**
	 * wp.media.model.Attachment
	 *
	 * @constructor
	 * @augments Backbone.Model
	 */
	Attachment = api.Class.extend({
        initialize: function( attachment ){

            $.extend( this, attachment || {} );
            Attachment.create( this );
        },
		/**
		 * Triggered when attachment details change
		 * Overrides Backbone.Model.sync
		 *
		 * @param {string} method
		 * @param {wp.media.model.Attachment} model
		 * @param {Object} [options={}]
		 *
		 * @returns {Promise}
		 */
		sync: function( method, model, options ) {
			// If the attachment does not yet have an `id`, return an instantly
			// rejected promise. Otherwise, all of our requests will fail.
			if ( _.isUndefined( this.id ) ) {
				return $.Deferred().rejectWith( this ).promise();
			}

			// Overload the `read` request so Attachment.fetch() functions correctly.
            if ( 'delete' === method ) {
				options = options || {};

				if ( ! options.wait ) {
					this.destroyed = true;
				}

				options.context = this;
				options.data = _.extend( options.data || {}, {
					action:   'delete-post',
					id:       this.id,
					_wpnonce: this.get('nonces')['delete']
				});

				return media.ajax.send( options ).done( function() {
					this.destroyed = true;
				}).fail( function() {
					this.destroyed = false;
				});

			// Otherwise, fall back to `Backbone.sync()`.
			} else {
				/**
				 * Call `sync` directly on Backbone.Model
				 */
				return Backbone.Model.prototype.sync.apply( this, arguments );
			}
		},
		/**
		 * Convert date strings into Date objects.
		 *
		 * @param {Object} resp The raw response object, typically returned by fetch()
		 * @returns {Object} The modified response object, which is the attributes hash
		 *    to be set on the model.
		 */
		parse: function( resp ) {
			if ( ! resp ) {
				return resp;
			}

			resp.date = new Date( resp.date );
			resp.modified = new Date( resp.modified );
			return resp;
		}
	}, {
		/**
		 * Add a model to the end of the static 'all' collection and return it.
		 *
		 * @static
		 * @param {Object} attrs
		 * @returns {wp.media.model.Attachment}
		 */
		create: function( attrs ) {
		    ////api.log( attrs );
            Attachments.all.models = _.uniq(Attachments.all.models , function(item, key, id){
                return item.id;
            });
            api.attachmentsSettings = $.extend( true, {} , Attachments.all.models );
            ////api.log( _.size(Attachments.all.models) );
			return Attachments.all.models.push( attrs );
		},
		/**
		 * Retrieve a model, or add it to the end of the static 'all' collection before returning it.
		 *
		 * @static
		 * @param {string} id A string used to identify a model.
		 * @param {Backbone.Model|undefined} attachment
		 * @returns {wp.media.model.Attachment}
		 */
		get: _.memoize( function( id, attachment ) {
			return Attachments.all.push( attachment || { id: id } );
		})
	});

    Attachments = api.Attachments = api.Class.extend({

        initialize: function( id, options ){
            var self = this;

            this.models = [];
            this.props = (options && options.props) ? options.props : {};
            this.modelsTemplate = "";
            this.cid = (this.cid) ? this.cid + 1 : 1;
            //id = ( !id ) ? "All" : id;

            //api.Events.bind( "changeQuery" + id  , this.test , this);
            //api.Events.trigger( "changeQuery" + id );

            //this.props.on( 'change:order',   this._changeOrder,   this );

        },

        changedView : function( type , model ){
            var compiled = api.template("sed-media-lib-item");
            switch ( type ) {
              case "append":
                  this.modelsTemplate += compiled( model );
                  ////api.log( this.cid );
                  //alert( this.modelsTemplate );
              break;
              case "remove":

              break;
              case "preFrom":

              break;
              case "nextFrom":

              break;
            }
        },

        fetch : function(options) {
            var model = this;
            options = options ? _.clone(options) : {};

            options.success = function( resp ){
                if( resp.length == 0 )
                    return false;

                _.each( resp , function( value, key ){
                    model.addModel( value , options);
                });
                    ////api.log(model.length);
                model.models = _.uniq(model.models , function(item, key, id){
                    return item.id;
                });
                    ////api.log(model.length);

            };

            return this.sync( options );
        },

        addModel : function( resp , options){
            var self = this;

            var attachment = new Attachment( resp );
            self.models.push( attachment );
            self.length = self.models.length;
            //self.changedView( "append" , value );

        },

        removeModel : function( model ){
            var self = this;
            self.removeModelById(model.id);

        },

        removeModelById : function( id ){
            var self = this;
            this.models = _.filter( this.models , function(model){ return model.id != id; })
        },

        bind: function( event , func , args ){
            api.Events.bind( "changeQuery" + id  , function(){

            });
        },

		/**
		 * @access private
		 */
        _changeQuery: function( params , options ){
			var props;
			if ( this.props.get('query') ) {
				props = this.props.toJSON();
				props.cache = ( true !== refresh );
				this.mirror( Query.get( props ) );
			}
        }

    } , {

		/**
		 * @namespace
		 */
		filters: {
			/**
			 * @static
			 * Note that this client-side searching is *not* equivalent
			 * to our server-side searching.
			 *
			 * @param {wp.media.model.Attachment} attachment
			 *
			 * @this wp.media.model.Attachments
			 *
			 * @returns {Boolean}
			 */
			search: function( props , attachment ) {
				if ( ! props.search ) {    //this.props
					return true;
				}

				return _.any(['title','filename','description','caption','name'], function( key ) {
					var value = attachment.get( key );
					return value && -1 !== value.search( props.search );
				}, this );
			},
			/**
			 * @static
			 * @param {wp.media.model.Attachment} attachment
			 *
			 * @this wp.media.model.Attachments
			 *
			 * @returns {Boolean}
			 */
			type: function( props , attachment , ext ) {
				var type = props.type;
				return ! type || -1 !== type.indexOf( attachment.type ) || checkAttachmentType( ext , type );
			},
			/**
			 * @static
			 * @param {wp.media.model.Attachment} attachment
			 *
			 * @this wp.media.model.Attachments
			 *
			 * @returns {Boolean}
			 */
			uploadedTo: function( attachment ) {
				var uploadedTo = this.props.get('uploadedTo');
				if ( _.isUndefined( uploadedTo ) ) {
					return true;
				}

				return uploadedTo === attachment.get('uploadedTo');
			},
			/**
			 * @static
			 * @param {wp.media.model.Attachment} attachment
			 *
			 * @this wp.media.model.Attachments
			 *
			 * @returns {Boolean}
			 */
			status: function( attachment ) {
				var status = this.props.get('status');
				if ( _.isUndefined( status ) ) {
					return true;
				}

				return status === attachment.get('status');
			}
		}
    });

	/**
	 * @static
	 * @member {wp.media.model.Attachments}
	 */
	Attachments.all = new Attachments();

	/**
	 * wp.media.query
	 *
	 * @static
	 * @returns {wp.media.model.Attachments}
	 */
   /*	media.query = function( props ) {
		return new Attachments( null, {
			props: _.extend( _.defaults( props || {}, { orderby: 'date' } ), { query: true } )
		});
	}; */

    Query = api.MediaQuery = api.Attachments.extend({

		/**
		 * @global wp.Uploader
		 *
		 * @param {Array} [models=[]] Array of models used to populate the collection.
		 * @param {Object} [options={}]
		 */
		initialize: function( models, options ) {
			var allowed;

			options = options || {};
			Attachments.prototype.initialize.apply( this, arguments );

			this.args     = options.args;
			this._hasMore = true;
			this.created  = new Date();
            this.length = 0;

		   /*	this.filters.order = function( attachment ) {
				var orderby = this.props.get('orderby'),
					order = this.props.get('order');

				if ( ! this.comparator ) {
					return true;
				}

				// We want any items that can be placed before the last
				// item in the set. If we add any items after the last
				// item, then we can't guarantee the set is complete.
				if ( this.length ) {
					return 1 !== this.comparator( attachment, this.last(), { ties: true });

				// Handle the case where there are no items yet and
				// we're sorting for recent items. In that case, we want
				// changes that occurred after we created the query.
				} else if ( 'DESC' === order && ( 'date' === orderby || 'modified' === orderby ) ) {
					return attachment.get( orderby ) >= this.created;

				// If we're sorting by menu order and we have no items,
				// accept any items that have the default menu order (0).
				} else if ( 'ASC' === order && 'menuOrder' === orderby ) {
					return attachment.get( orderby ) === 0;
				}

				// Otherwise, we don't want any items yet.
				return false;
			};

			// Observe the central `wp.Uploader.queue` collection to watch for
			// new matches for the query.
			//
			// Only observe when a limited number of query args are set. There
			// are no filters for other properties, so observing will result in
			// false positives in those queries.
			allowed = [ 's', 'order', 'orderby', 'posts_per_page', 'post_mime_type', 'post_parent' ];
			if ( wp.Uploader && _( this.args ).chain().keys().difference( allowed ).isEmpty().value() ) {
                this.observe( wp.Uploader.queue );
			} */
		},

		/**
		 * @returns {Boolean}
		 */
		hasMore: function() {
			return this._hasMore;
		},
		/**
		 * @param {Object} [options={}]
		 * @returns {Promise}
		 */
		more: function( options ) {
			var query = this;

			if ( this._more && 'pending' === this._more.state() ) {
				return this._more;
			}

			if ( ! this.hasMore() ) {
				return $.Deferred().resolveWith( this ).promise();
			}

			options = options || {};
			options.remove = false;

			return this._more = this.fetch( options ).done( function( resp ) {
				if ( _.isEmpty( resp ) || -1 === this.args.posts_per_page || resp.length < this.args.posts_per_page ) {
		  	        query._hasMore = false;
				}
			});
		},

		/**
		 * Overrides Backbone.Collection.sync
		 * Overrides wp.media.model.Attachments.sync
		 *
		 * @param {String} method
		 * @param {Backbone.Model} model
		 * @param {Object} [options={}]
		 * @returns {Promise}
		 */
		sync: function( options ) {
			var args, self = this;

            options = options || {};
            options.context = this;
            options.data = _.extend( options.data || {}, {
            	action         : 'load_medias',
            	sed_page_ajax  : 'media_loader' ,
                nonce          : api.settings.nonce.media.load,

            });

            // Clone the args so manipulation is non-destructive.
            args = _.clone( this.args );

            // Determine which page to query.
            if ( -1 !== args.posts_per_page ) {
            	args.paged = Math.floor( this.length / args.posts_per_page ) + 1;
            }

            options.data.query = args;

            return media.ajax.send( options );
		},
    }, {
		/**
		 * @readonly
		 */
		defaultProps: {
			orderby: 'date',
			order:   'DESC'
		},
		/**
		 * @readonly
		 */
		defaultArgs: {
			posts_per_page: 56
		},
		/**
		 * @readonly
		 */
		orderby: {
			allowed:  [ 'name', 'author', 'date', 'title', 'modified', 'uploadedTo', 'id', 'post__in', 'menuOrder' ],
			valuemap: {
				'id':         'ID',
				'uploadedTo': 'parent',
				'menuOrder':  'menu_order ID'
			}
		},
		/**
		 * @readonly
		 */
		propmap: {
			'search':    's',
			'type':      'post_mime_type',
			'perPage':   'posts_per_page',
			'menuOrder': 'menu_order',
			'uploadedTo': 'post_parent',
			'status':     'post_status'
		},
		/**
		 * @static
		 * @method
		 *
		 * @returns {wp.media.model.Query} A new query.
		 */
		// Caches query objects so queries can be easily reused.
		get: (function(){
			/**
			 * @static
			 * @type Array
			 */
			var queries = [];
			/**
			 * @param {Object} props
			 * @param {Object} options
			 * @returns {Query}
			 */
			return function( props, options ) {
				var args     = {},
					orderby  = Query.orderby,
					defaults = Query.defaultProps,
					query,
					cache    = !! props.cache || _.isUndefined( props.cache );

				// Remove the `query` property. This isn't linked to a query,
				// this *is* the query.
				delete props.query;
				delete props.cache;

				// Fill default args.
				_.defaults( props, defaults );

				// Normalize the order.
				props.order = props.order.toUpperCase();
				if ( 'DESC' !== props.order && 'ASC' !== props.order ) {
					props.order = defaults.order.toUpperCase();
				}

				// Ensure we have a valid orderby value.
				if ( ! _.contains( orderby.allowed, props.orderby ) ) {
					props.orderby = defaults.orderby;
				}

				// Generate the query `args` object.
				// Correct any differing property names.
				_.each( props, function( value, prop ) {
					if ( _.isNull( value ) ) {
						return;
					}

					args[ Query.propmap[ prop ] || prop ] = value;
				});

				// Fill any other default query args.
				_.defaults( args, Query.defaultArgs );

				// `props.orderby` does not always map directly to `args.orderby`.
				// Substitute exceptions specified in orderby.keymap.
				args.orderby = orderby.valuemap[ props.orderby ] || props.orderby;

				// Search the query cache for matches.
				if ( cache ) {
					query = _.find( queries, function( query ) {
						return _.isEqual( query.args, args );
					});
				} else {
					queries = [];
				}

				// Otherwise, create a new query and add it to the cache.
				if ( ! query ) {
					query = new Query( [], _.extend( options || {}, {
						props: props,
						args:  args
					} ) );
					queries.push( query );
				}

                Query.queries = queries;

				return query;
			};
		}())
	});

    api.MediaSelection = api.Attachments.extend({
        initialize: function( models, options ){
			Attachments.prototype.initialize.apply( this, arguments );
			this.type = (options && options.type) ?  options.type : "single";

			//this.on( 'add remove reset', _.bind( this.single, this, false ) );
        },

        add: function( model , options){
            this.addModel( model , options);
            api.Events.trigger("addedModelToSelection" , this.models );
        },

        remove: function( id ){
            this.removeModelById( id );
            api.Events.trigger("removedModelFromSelection" , this.models );
        },

        render : function( modelId , mode ){

            var model = _.findWhere(Attachments.all.models, {id: modelId})

            if(mode){
                if(this.type == "single"){
                    //reset models
                    this.models = [];
                }

                this.add( model );

            }else{
                this.remove( modelId );
            }
        }

    });

    api.MediaOrganize = api.Attachments.extend({
        initialize: function( options ){
            this.models = [];
            $.extend( this , options);

            this.ready();
        },

        ready: function(  ){
            var self = this;
            this.sortable();
            $( "#sed_media_library_organize .sed-media-item-remove-icon").livequery(function(){
                $(this).off("click");
                $(this).on("click" , function(){
                    self.remove( $(this) );
                });
            });
        },
        sortable: function(  ){

            var prevIndex , currentIndex , self = this;
            $( "#site-editor-media-gallery" ).sortable({

                start : function( event, ui ){
                    prevIndex = $( "#site-editor-media-gallery > .attachment" ).index( ui.item[0] );
                },

                update : function( event, ui ){
                    var modelId = ui.item.data("id");
                    currentIndex = $( "#site-editor-media-gallery > .attachment" ).index( ui.item[0] );
                            //alert( currentIndex );
                    $("#site-editor-media-gallery > .attachment").each(function( index ,el ){
                        var mId = $(this).data("id");

                        if(currentIndex == index){
                            self.models = _.map(self.models , function(model , idx){
                                if(model.id == mId)
                                    model.order_id = index;

                                return model;
                            });

                        }else if(currentIndex < prevIndex && currentIndex < index && index <= prevIndex){

                            self.models = _.map(self.models , function(model , idx){
                                if(model.id == mId)
                                    model.order_id = index + 1;

                                return model;
                            });

                        }else if(currentIndex > prevIndex && currentIndex > index && index >= prevIndex){

                            self.models = _.map(self.models , function(model , idx){
                                if(model.id == mId)
                                    model.order_id = index - 1;

                                return model;
                            });

                        }
                    });

                    self.models = _.sortBy( self.models , function( model ){
                        return model.order_id;
                    });

                    //update shortcodes models order_id s
                    _.each(self.mediaListsItems , function(listItems){
                        var currentItem = listItems[prevIndex];

                        ////api.log( listItems );

                        var shPrevIndex = currentItem.order_id ,
                            shNextIndex = listItems[currentIndex].order_id ,
                            itemTreeChildren = self.findAllTreeChildrenShortcode(currentItem.id) ,
                            iTreeChOIds = _.pluck(itemTreeChildren, 'order_id');

                        ////api.log( iTreeChOIds );
                        ////api.log( currentItem ); //api.log( shPrevIndex , shNextIndex );

                        self.shortcodeModels = _.map(self.shortcodeModels , function( item , index){

                            if($.inArray(index , iTreeChOIds) != -1 || index == shPrevIndex){

                                item.order_id += shNextIndex - shPrevIndex;
                                //item.order_id += (shNextIndex < shPrevIndex) ? itemTreeChildren.length : -itemTreeChildren.length;

                            }else if(shNextIndex < shPrevIndex && shNextIndex <= index && index < shPrevIndex){

                                item.order_id += itemTreeChildren.length + 1;

                            }else if(shNextIndex > shPrevIndex && ( shNextIndex + itemTreeChildren.length ) >= index && index > (shPrevIndex + itemTreeChildren.length )){

                                item.order_id -= itemTreeChildren.length + 1;

                            }
                            return item;

                        });

                    });

                    self.mediaListsItems = _.object(_.map(self.mediaListsItems , function(listItems , key){
                        listItems = _.sortBy( listItems , function( item ){
                            return item.order_id;
                        });
                        return [key , listItems];
                    }));

                    ////api.log(self.shortcodeModels);

                    self.shortcodeModels = _.sortBy( self.shortcodeModels , function( item ){
                        return item.order_id;
                    });

                    ////api.log(self.shortcodeModels);

                }

            });

            $( "#site-editor-media-gallery" ).disableSelection();
        },

        remove : function( element ){
            var self = this , attachment = element.parents(".attachment:first") ,
                modelId = attachment.data("id") ,
                //currentModel ,
                currentIndex;

                ////api.log( self.models );

            _.each(self.models, function(model, idx) {
               if (model.id == modelId) {
                  //currentModel = model;
                  currentIndex = idx;
                  return;
               }
            });

            //remove from models
            self.removeModelById( modelId );

            _.each(self.mediaListsItems , function(list){
                var itemTreeChildren = self.findAllTreeChildrenShortcode( list[currentIndex].id ) ,
                    ids = _.pluck(itemTreeChildren, 'id') ,
                    orderIds = _.pluck(itemTreeChildren, 'order_id') ,
                    maxOIds;

                orderIds.push( list[currentIndex].order_id );
                maxOIds = Math.max.apply(null, orderIds)

                ids.push( list[currentIndex].id );   //alert( maxOIds );

                //update shortcodes models order id
                self.shortcodeModels  = _.map( self.shortcodeModels  , function(model){
                    if(model.order_id > maxOIds){
                        model.order_id -= ids.length;
                        return model;
                    }else
                        return model;
                });


                //remove from shortcodes models
                self.shortcodeModels  = _.filter( self.shortcodeModels  , function(model){
                    if($.inArray( model.id , ids ) != -1)
                        return false;
                    else
                        return true;
                });
            });

            ////api.log(self.shortcodeModels);

            //remove from list items
            self.mediaListsItems = _.object(_.map(self.mediaListsItems , function(listItems , key){

                listItems = _.filter( listItems , function( item , index ){
                    return currentIndex != index;
                });

                return [key , listItems];
            }));



            //remove from selections and back to media library list
            var element = $( Library.library ).find("li.attachment[data-id='" + modelId + "']");
            api.media.library.selectionView( element );
            api.media.library.selection.render( modelId , false );

            //remove from hidden models
            Library.hiddenSelectedModels( api.media.library.selection.models );

            api.Events.trigger("updateModelSelectionIds" , modelId);

            //remove from dom
            attachment.detach();
        },

        addModel : function( options){
            var self = this;

            var attachment = new Attachment( options );
            self.models.push( attachment );
            //self.length = self.models.length;
            //self.changedView( "append" , value );

        },

        removeModel : function( model ){
            var self = this;
            self.removeModelById(model.id);

        },

        removeModelById : function( id ){
            var self = this;
            this.models = _.filter( this.models , function(model){ return model.id != id; });
        },

        //we have max 2 list of items in each module
        addShortcodeModels: function( models ){
            var self = this , listModel, mainListItems;
            this.shortcodeModels = models;
            this.mediaListsItems = {};

            this.shortcodeModels = _.map(this.shortcodeModels , function(item , index){
                //item.cid = _.uniqueId("shortcode_model_");
                item.order_id = index;
                return item;
            });

            if( !_.isUndefined( this.moduleId ) ){
                this.mainShortcodeModel = _.find(this.shortcodeModels , function(shModel){
                    return shModel.id == self.moduleId ;
                });
            }else{
                alert("not module id for main shortcode models in line 886 in siteeditor/modules/mediaClass.js");
                return ;
            }


            this.mediaLists = _.filter(models, function(model) {
                return !_.isUndefined(model.attrs) && !_.isUndefined(model.attrs.sed_role) && model.attrs.sed_role == "media-list";
            });

            if(this.mediaLists.length == 0){
                alert("not one main media list in shortcode pattern line 824 in siteeditor/modules/mediaClass.js");
                return ;
            }else if(this.mediaLists.length == 1){
                this.mainListModel = this.mediaLists[0];
            }else{
                this.mainListModel = _.find(this.mediaLists , function(model){
                    return !_.isUndefined(model.attrs.sed_list_type) && model.attrs.sed_list_type == "main";
                });
            }

            _.each( this.mediaLists , function(listModel){

                var listItems = _.filter(models , function(model){
                    return model.parent_id == listModel.id;
                });

                if( _.isEqual(self.mainListModel, listModel) )
                    mainListItems = listItems;

                self.mediaListsItems[listModel.id] = listItems;
            });


            var mediaItems = [];
            _.each( mainListItems , function(child){
                var itemChildren = self.findAllTreeChildrenShortcode(child.id);
                itemChildren.push(child);

                mItem = _.find(itemChildren , function(itemCh){
                    return !_.isUndefined(itemCh.attrs) && !_.isUndefined(itemCh.attrs.sed_main_media) && itemCh.attrs.sed_main_media;
                });

                if(mItem){
                    //mItem.listItemId = child.id;
                    mediaItems.push( mItem );
                }
            });

            var attachments = [] ,
                allAttachments = $.extend( {} , Attachments.all.models , api.attachmentsSettings );

            _.each(mediaItems , function(item , index){

                if( _.isUndefined( api.media.library.media_attrs ) ){
                    alert("Error : media attrs do not sended. line 938 mediaClass js ");
                    return ;
                }

                var media_attrs = api.media.library.media_attrs ,
                    attach_id_attr = media_attrs[0] ,
                    media_url_attr = media_attrs[1] ,
                    attachment;

                if( item.attrs[attach_id_attr] > 0 ){
                    attachment = _.findWhere( api.attachmentsSettings , { id : item.attrs[attach_id_attr]}  );
                }

                if( !_.isUndefined( attachment ) && attachment ){
                    var model = _.findWhere( allAttachments , { id : item.attrs[attach_id_attr]} );
                    attachments.push( model );

                    api.Events.trigger("updateModelSelectionIds" , model.id , "add" );

                    api.media.library.selection.add( model );

                    model.order_id = index;
                    self.addModel( model );

                }else{
                    var url;
                    if( !_.isUndefined( attachment ) && attachment && !_.isUndefined( attachment.url ) ){
                        if( attachment.type == "image" && !_.isUndefined( attachment.sizes ) && !_.isUndefined( attachment.sizes.thumbnail ) ){
                            url = attachment.sizes.full.url;
                        }else{
                            url = attachment.url;
                        }
                    }else{
                        url = item.attrs[media_url_attr];
                    }

                    var model =  {
                        id          : _.uniqueId('from_url_') ,
                        type        : item.attrs.sed_media_role || "image" ,
                        date        : new Date(),
                        url         : url ,
                        sizes       : {
                            thumbnail   : {
                                url         : url
                            }
                        },
                        caption     : "" ,
                        title       : "" ,
                        //sync_id     : item.listItemId ,
                        order_id    : index
                    };

                    self.addModel( model );
                }
            });

            Library.hiddenSelectedModels( api.media.library.selection.models );

            /*

            //remove from selections and back to media library list
            var element = $( Library.library ).find("li.attachment[data-id='" + modelId + "']");
            api.media.library.selectionView( element );
            api.media.library.selection.add( modelId , false );

            //remove from hidden models

            */

            this.view();

        },

        view: function(){
            var html = "" , self = this;
            this.template = api.template("sed-media-lib-item");  //self.template( model )

            _.each(this.models , function( model , key){
                html += self.template( model );
            });

            $("#site-editor-media-gallery").html( html );
            //self.selection.models

        },

        addMediaItemsShortcode: function( newModels ){
            var self = this;

            _.each(self.mediaLists , function(list , index){

               var indexLCH = self.findAllTreeChildrenShortcode( list.id ).length + list.order_id + 1;

                _.each( newModels , function( model ){

                    var patternModel = _.find(self.shortcodeModels , function( shModel ){
                        return shModel.tag == "sed_add_item_pattern" && !_.isUndefined(shModel.attrs) && !_.isUndefined(shModel.attrs.sed_media_type) && shModel.attrs.sed_media_type == model.type
                               && !_.isUndefined(shModel.attrs.sed_rel_list) && shModel.attrs.sed_rel_list == list.attrs.sed_list_id;
                    });

                    var treeChildren = self.addNewShortcoModel( patternModel.id , model ,list.id , list );

                    if(indexLCH < self.shortcodeModels.length){
                        var args = $.merge([indexLCH ,0 ] , treeChildren);      
                        Array.prototype.splice.apply(self.shortcodeModels , args);
                    }else{
                        self.shortcodeModels = $.merge(self.shortcodeModels , treeChildren);
                    }
                    indexLCH += treeChildren.length;

                    //update media list items for sortable & remove helper
                    self.mediaListsItems[list.id].push(treeChildren[0]);
                    //update main list items
                    if( self.mainListModel.id == list.id ){   // or _.isEqual(self.mainListModel, list)
                        //self.listItems.push(treeChildren[0]);
                        //model.sync_id = treeChildren[0].id;
                        //alert("test");
                        ////api.log( model.sync_id );
                    }



                });

                //update shortcodes models order id
                self.shortcodeModels  = _.map( self.shortcodeModels  , function(model , index){
                    model.order_id = index;
                    return model;
                });

                ////api.log( self.mediaListsItems[list.id] );


            });

        },

        //this function for add new items by sed_add_shortcode
        addNewShortcoModel : function( elmId , attachmentModel , parent_id , list){
            var children = this.getShortcodeChildren( elmId );

            if(!$.isArray(children) || children.length == 0){
                return [];
            }else{
                var id ,
                    shortcodes = [] , new_shortcode , self = this;

                $.each( children , function( index , shortcode){

                    id = _.uniqueId("shortcode_model_");

                    new_shortcode = {
                      parent_id : parent_id,
                      tag       : shortcode.tag,
                      attrs     : self.getNewAttrs( shortcode , attachmentModel , list ),
                      id        : id,
                      newModel  : true
                    };

                    if( shortcode.tag == "content" ){
                        new_shortcode.content = shortcode.content;
                    }

                    shortcodes.push( new_shortcode );

                    var shortcodes_children = self.addNewShortcoModel( shortcode.id , attachmentModel , id , list);
                    shortcodes = $.merge( shortcodes , shortcodes_children || []);

                });

                return shortcodes;
            }
        },

        getNewAttrs: function( shortcodeModel , attachmentModel , list ){

            var shModel = shortcodeModel , model = attachmentModel , attrs = _.clone( shModel.attrs ) ;
            if( !_.isUndefined(shModel.attrs) && !_.isUndefined(shModel.attrs.sed_main_media) && shModel.attrs.sed_main_media ){

                attrs[api.media.library.media_attrs[0]] = attachmentModel.id;
                attrs.attachment_model = attachmentModel;
                if( !_.isUndefined( attrs[api.media.library.media_attrs[2]] ) ){
                    attrs[api.media.library.media_attrs[2]] = "attachment";
                }

                api.previewer.trigger( 'addAttachmentSizes' , {
                    id    : attachmentModel.id  ,
                    sizes : attachmentModel.sizes
                });

            }

            return attrs;
        },

        getShortcodeChildren: function( parent_id ){
            var children = [];
            ////api.log( this.postsContent );
            _.each(this.shortcodeModels , function(shortcode , i){
                //alert(shortcode.parent_id);
                if(shortcode.parent_id == parent_id){
                    children.push( shortcode );
                }
            });

            return children;
        },

        findAllTreeChildrenShortcode: function( parent_id ){
            var self = this , allChildren = [];

            _.each(this.shortcodeModels , function(shortcode , index){
                if(shortcode.parent_id == parent_id){
                    allChildren.push( _.clone(shortcode) );
                    allChildren = $.merge( allChildren , self.findAllTreeChildrenShortcode( shortcode.id  ) );
                }
            });

            return allChildren;
        },

        send: function( ){
            api.previewer.send( "updateMediaListModule" , {
                moduleId          : this.moduleId  ,
                shortcodes        : this.shortcodeModels ,
                moduleContainerId : this.moduleContainerId
            });
        },

    });


    /*api.mediaSyncPreview = api.Class.extend({

        initialize: function( params ){

            this.mediaLib;
            this.attachment;
            this.targetElement;
            this.shortcode;
            this.attr;
            this.url;
            this.requestSize;
            this.doSync = true;
            this.imageSizeSync = true;

            $.extend( this, params || {} );

            this.ready();

        },

        ready : function(){

            if( ( this.doSync === true || this.imageSizeSync === true ) && _.isUndefined( this.targetElement ) )
                return ;

            if(this.doSync === true ){
                //update post_id attr in sed_image shortcode
                api.previewer.send( "syncMediaAttachments" , {
                    attachment    : this.attachment ,
                    targetElement : this.targetElement ,
                    shortcode     : this.shortcode
                });
            }

            switch ( this.attachment.type ) {
                case "image":
                    this.updateImage();
                break;
                case "video":
                case "audio":
                    this.updateMedia();
                break;
                default:
                    this.updateMedia();
            }

        },

        updateMedia : function(){
            this.url = this.attachment.url;
        },

        updateImage: function(){

            var sizes = api.addOnSettings.imageModule.sizes ,
                attachSizes = this.attachment.sizes , currSize ;

            if( !_.isUndefined( this.requestSize ) && this.requestSize && !_.isUndefined( attachSizes[this.requestSize] ) ){
                currSize = this.requestSize;
            }else if( !_.isUndefined( attachSizes.sedXLarge ) ){
                currSize = "sedXLarge";
            }else if( sizes.sedXLarge.width < attachSizes.full.width && !_.isUndefined( attachSizes.large ) ){
                currSize = "large";
            }else {
                currSize = "full";
            }

            if(this.imageSizeSync === true){
                //update using_size attr in sed_image shortcode
                api.previewer.send( "syncImageUsingSize" , {
                    size  : currSize ,
                    targetElement : this.targetElement
                });
            }

            this.currSize = currSize;
            this.url = attachSizes[currSize].url;

        }

    });*/



    api.LibraryButtons = api.Class.extend({

        initialize: function( params , options ){
            this.models = [];
        },

        trigger: function(){}

    });

    api.CancelBtn = api.LibraryButtons.extend({
        initialize: function( params , options ){

        },

        trigger: function(){
            $( Library.dialog.selector ).dialog( "close" );
        }

    });

    api.ChangeMediaBtn = api.LibraryButtons.extend({
        initialize: function( models ){

        },

        trigger: function(  models , mediaList , mediaLib  ){
            var self = this;
            if( models.length == 1 && mediaLib.selctedType == "single" ){
                var validate = this.validate( models[0] , mediaLib );
                if( validate && !_.isUndefined(mediaLib.eventKey) ){
                    //update post_id attr in sed_image shortcode
                    api.previewer.send( "syncMediaAttachments" , {
                        attachment    : models[0] ,
                        targetElement : "" ,
                        shortcode     : ""
                    });
                    api.previewer.trigger( "sedChangeMedia" + mediaLib.eventKey , models[0] );
                }
            }else if(  models.length > 0 && mediaLib.selctedType == "multiple" ){
                var validModels = [];
                _.each( models , function( model ){
                    var validate = self.validate( model , mediaLib );
                    if( validate && !_.isUndefined(mediaLib.eventKey) ){
                        //update post_id attr in sed_image shortcode
                        api.previewer.send( "syncMediaAttachments" , {
                            attachment    : model ,
                            targetElement : "" ,
                            shortcode     : ""
                        });
                        validModels.push( model );
                    }
                });

                if( validModels.length > 0 ){
                    api.previewer.trigger( "sedChangeMedia" + mediaLib.eventKey , validModels );
                }
            }

            $( Library.dialog.selector ).dialog( "close" );
        },

        validate: function( attachment , mediaLib ){

            if( _.isUndefined( mediaLib.subtypes ) || _.isEmpty( mediaLib.subtypes ) ){
                return true;
            }

            var validFormat = false;
            $.each( mediaLib.subtypes , function( idx , type ){
                var num = -(type.length) - 1;

                if( ("." + type) == attachment.filename.substr( num ) ){
                    validFormat = true;
                    return false;
                }
            });

            if(validFormat === false){
                alert( api.I18n.invalid_media_format );
                return null;
            }

            return true;

        }

    });

    api.AddToCollectionBtn = api.LibraryButtons.extend({
        initialize: function( params , options ){
            var self = this;
            this.oldModelIds = [];
            api.Events.bind("updateModelSelectionIds" , function( modelId , type ){
                if( _.isUndefined( type ) || type == "cancel" )
                    self.cancelSelected( modelId );
                else if( type == "add" )
                    self.addSelected( modelId );
            })
        },

        //remove item from gallery
        cancelSelected: function(  modelId  ){
            this.oldModelIds = _.filter(this.oldModelIds , function(id){
                return modelId != id;
            });
        },

        addSelected: function(  modelId  ){

            if($.inArray( modelId , this.oldModelIds) == -1)
                this.oldModelIds.push( modelId );

        },

        trigger: function(  models , mediaList , mediaLib , btnEl  ){
            var self = this , newModels;
            if(!$.isArray(models) || models.length == 0)
                return ;

            btnEl = $( btnEl );

            btnEl.prop("disabled" , true );


            if(self.oldModelIds.length > 0){
                newModels = _.filter(models , function(model){

                    if($.inArray( model.id , self.oldModelIds) != -1){
                        return false;
                    }else{
                        self.oldModelIds.push( model.id );
                        return true;
                    }

                });
            }else{
                newModels = models;
                self.oldModelIds = _.pluck( models , "id");
            }

            mediaList.models = $.merge(mediaList.models , newModels);
            mediaList.addMediaItemsShortcode( newModels );

            Library.hiddenSelectedModels( models );

            $(Library.tab.organize).tab("show");
            mediaList.view();

        }

    });

    api.updateMediaCollectionBtn = api.LibraryButtons.extend({
        initialize: function( params , options ){

        },

        trigger: function(  models , mediaList  ){
            mediaList.send();
            $( Library.dialog.selector ).dialog( "close" );
        }


    });


    api.LibraryButtonsConstructor = $.extend( api.LibraryButtonsConstructor || {}, {
        cancel                     : api.CancelBtn ,
        change_media               : api.ChangeMediaBtn ,
        add_to_collection          : api.AddToCollectionBtn ,
        update_media_collection    : api.updateMediaCollectionBtn
    });

    /*Uploader = api.media.Uploader = api.SedMediaLibrary.extend({
        initialize: function( params ){

        }
    });  */

    Library = api.SedMediaLibrary = api.Class.extend({
      initialize: function( params ){
          var self = this;

          this.currentType = "all";
          this.template;
          this.postId;
          this.selection;
          this.search = '';
          this.buttons = [];
          this.mediaList;
          this.uploadProcessing = false;
          this.uploadingFiles = [];
          this.ajaxProcessing = false;
          this.libLoadProcessing = false;
          //module id in dom and shortcode models
          this.moduleId;
          //module container id in dom and shortcode models( row ==module (container)== button module  )
          this.moduleContainerId;

          $.extend( this, params || {} );

          this.ready();

      },

      ready : function(){
          var self = this;

          //initialize library dialog
          $( Library.dialog.selector ).dialog( Library.dialog.options );

          $( Library.dialog.selector ).html( $(Library.dialog.tpl).html() );


          api.previewer.bind( 'openMediaLibrary' , function( data ) {
              if( !_.isUndefined( data.moduleId ) )
                self.moduleId = data.moduleId;

              if( !_.isUndefined( data.moduleContainerId ) )
                self.moduleContainerId = data.moduleContainerId;

              if( !_.isUndefined( data.models ) )
                  self.update( data.options.media , data.models );
               else
                  self.update( data.options.media );

              api.Events.trigger( "changeUploaderBehavior" );

              self.set({ type : self.currentType });
          });

          $( Library.container ).mCustomScrollbar({
              //autoHideScrollbar:true ,
              advanced:{
                  updateOnBrowserResize:true, //update scrollbars on browser resize (for layouts based on percentages): boolean
                  updateOnContentResize:true,
              },
              scrollButtons:{
                enable:true
              },
              callbacks:{
                  onTotalScroll:function(){
                      ////api.log("onTotalScroll");

                      if(self.ajaxProcessing === false && self.libLoadProcessing === false ){
                          self.libLoadProcessing = true;

                          var args = {type : self.currentType };
                          if( $.trim(self.search) )
                              args.search = self.search;

                          self.set( args , true);
                          //_.throttle(self.set( args , true), 300);
                      }

                  },
                  onTotalScrollOffset:300,
              }
          });

          $("#sed_media_library_organize").mCustomScrollbar({
              //autoHideScrollbar:true ,
              advanced:{
                  updateOnBrowserResize:true, //update scrollbars on browser resize (for layouts based on percentages): boolean
                  updateOnContentResize:true,
              },
              scrollButtons:{
                enable:true
              },
          });


          $(Library.searchBox).on("keyup" , function(){
              self.search = $(this).val();
              var args = {type : self.currentType };
              if( $.trim(self.search) )
                  args.search = self.search;

              self.set( args );
          });

          $(Library.filterBox).on("change" , function(){
              var value = $(this).val();
              self.currentType = (value == "all") ? "" : value;

              api.Events.trigger( "changeUploaderBehavior" );

              var args = {type : self.currentType };
              if( $.trim(self.search) )
                  args.search = self.search;

              $(Library.library).html( "" );
               self.set( args );
          });


          $(Library.library).find("li.attachment").livequery(function(){
              $(this).on("click" , function(e){
                  //e.preventDefault();
                  var modelId = $(this).data("id");

                  self.selectionView( $(this) );
                  self.selection.render( modelId , $(this).find(">a").hasClass("sed-media-item-selected") );
              });
          });

          $('#media-library-tab a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
             var name = $(this).data("name");
             $( Library.dialog.selector ).siblings(".ui-dialog-buttonpane").find('[role="button"]').each(function(){
                  if(name == $(this).data("btnLibraryDialogTab")){
                      $(this).show();
                  }else{
                      $(this).hide();
                  }
             });
          });

          this.initUploader();

      },

      initUploader : function(){

          var self = this;

          var _setUploaderExtensions = function(){
              var mimeTypes = [] , mediaExtensions , currMTypeTitle;

              if(self.currentType != "all" && !_.isEmpty( self.currentType ) && $.inArray( self.currentType , getMediaTypes() ) != -1 ){
                  mediaExtensions = api.mediaSettings.types[self.currentType].ext;
                  currMTypeTitle = api.mediaSettings.types[self.currentType].caption;
                  mediaExtensions = mediaExtensions.join(",");

                  mimeTypes.push( {title : currMTypeTitle , extensions : mediaExtensions} );

              }else{
                  $.each( api.mediaSettings.types, function(type , value){
                      mimeTypes.push( { title : value.caption , extensions : value.ext.join(",") } );
                  });
              }

              return mimeTypes;

          };

          var id = "sed-media-lib-uploader" ,
              uploaderUI = $( "#" + $("#" + id).attr("sed-uploader-tmpl") ).html();

          $("#" + id).html( uploaderUI );

          $(".media-progress-bar" , $("#" + id)).progressbar({
            value : 0
          });

          var uploader = new plupload.Uploader({
              browse_button         : id + '-browse',
              container             : id,
              runtimes              : "html5,flash,silverlight,html4",
              url                   : SED_UPLOAD_AJAX_URL,
              flash_swf_url         : SEDEXTBASE.url + "/media/js/Moxie.swf",
              silverlight_xap_url   : SEDEXTBASE.url + "/media/js/Moxie.xap",
              filedataname          : "file",
              multi_selection       : true,
              dragdrop              : true,
              drop_element          : 'sed-media-lib-uploader-drop-area' ,
              filters : {
                  max_file_size : api.mediaSettings.params.max_upload_size + 'mb',
                  mime_types: _setUploaderExtensions()
              }
          });

          uploader.init();

          api.Events.bind( "changeUploaderBehavior" , function(){

              var filters = uploader.getOption( "filters" );
              filters.mime_types = _setUploaderExtensions();

              uploader.setOption( "filters", filters);

          });

          uploader.bind("Init", function(up, res) {

            if( up.features.dragdrop ){
                var dropzone = $("#sed-media-lib-uploader-drop-area") ,
                    timer, active;
    			dropzone.toggleClass( 'supports-drag-drop', true );

    			// 'dragenter' doesn't fire correctly, simulate it with a limited 'dragover'.
    			dropzone.bind( 'dragover.sed-uploader', function() {
    				if ( timer ) {
    					clearTimeout( timer );
    				}

    				if ( active ) {
    					return;
    				}

    				dropzone.trigger('dropzone:enter').addClass('drag-over');
    				active = true;
    			});

    			dropzone.bind('dragleave.sed-uploader, drop.sed-uploader', function() {
    				// Using an instant timer prevents the drag-over class from
    				// being quickly removed and re-added when elements inside the
    				// dropzone are repositioned.
    				//
    				// @see https://core.trac.wordpress.org/ticket/21705
    				timer = setTimeout( function() {
    					active = false;
    					dropzone.trigger('dropzone:leave').removeClass('drag-over');
    				}, 0 );
    			});

            }

          });

          uploader.bind("Error", function(up, error) {

              function destroy() {
                  uploader.destroy();
                  //self.target.html(self.contentsBak);
                  //uploader = self.target = self.contentsBak = null;
              }

              if (error.code === plupload.INIT_ERROR) {
                  setTimeout(function() {
                      destroy();
                  }, 1);
              }

              $("#sed_media_library_panel_tab").tab('show');

              //show error template
              var template = api.template("sed-media-lib-uploader-errors") ,
                  errorData = {
                     title    :  error.code,
                     message  :  error.message
                  };

              var errorBox = $( template( errorData ) ).appendTo( $("#sed-media-lib-uploader-errors") );
              errorBox.slideDown( 300 ).delay( 5000 ).fadeOut( 400 );

          });

          var fileQueued = function(file){

          };

          uploader.bind("FilesAdded", function(up, files) {
              uploader.start();
              self.uploadProcessing = true;
              self.uploadingFiles = files;

              //show library tab
              $("#sed_media_library_panel_tab").tab('show');
              self.addFilesUploadingModels(files);

              var args = {type : self.currentType };
              if( $.trim(self.search) )
                  args.search = self.search;

              self.set( args );

              $.each(uploader.files, function(i, file) {
                   ////api.log(file);
                  if (file.status != plupload.DONE) {
                      fileQueued(file);
                  }

              });
          });

          uploader.bind("UploadFile", function(up, file) {

             // $('#' + file.id).addClass('sed-uploader-current-file');
          });

          uploader.bind("UploadProgress", function(up, file) {
              var self = this;
              var item = $( '[data-id="'+ file.id +'"]' , $(Library.library) );
              $('.progressbar', item).progressbar({
                  value: (200 * file.loaded) / file.size
              });

          	  $('.progressbar', item).width( (100 * file.loaded) / file.size );
          	  $('.percent', item).html( file.percent + '%' );

          });

          uploader.bind("FileUploaded", function(up, file, response) {
              self.uploadingFiles = _.filter( self.uploadingFiles , function(currFile){ return currFile.id != file.id; });

              response = $.parseJSON(response.response);
              if(response.success === true){
                  var currentModel;
                  //extend new info for file model
                  _.each( Query.queries , function( query , index ){
                      query.models = _.map(query.models , function( model , key){
                          if(model.id == file.id){
                             var modelSelected = _.findWhere( self.selection.models , { id : model.id } ) ;

                             if( !_.isUndefined( modelSelected ) )
                                self.selection.remove( model.id );

                             _.extend( model , _.extend(response.data , {uploading : false}) );
                             currentModel = model;

                            if( !_.isUndefined( modelSelected ) )
                                self.selection.add( model );

                             return model;
                          }else{
                             return model;
                          }
                      });

                      query.models = _.uniq(query.models , function(item, key, id){
                          return item.id;
                      });

                  });

                  api.Events.trigger( "fileUploadComplete" , currentModel );

              }else{
                  //show error template
                  var template = api.template("sed-media-lib-uploader-errors") ,
                      error = {
                         title    :  response.data.filename,
                         message  :  response.data.message
                      };

                  var errorBox = $("#sed-media-lib-uploader-errors").appendTo( template( error ) );
                  errorBox.slideDown( 300 ).delay( 5000 ).fadeOut( 400 );
                  //remove file model from queries
                  _.each( Query.queries , function( query , index ){
                      query.models = _.filter(query.models , function( model , key){
                          if(model.id == file.id){
                              var selected = _.findWhere( self.selection.models , { id : model.id } ) ;

                              if( !_.isUndefined( selected ) )
                                  self.selection.remove( model.id );
                          }
                          return model.id != file.id;
                      });
                      query.length = query.models.length;
                  });
              }

              var args = {type : self.currentType };
              if( $.trim(self.search) )
                  args.search = self.search;

              self.set( args );

              api.Events.trigger( "fileUploadCompleteCheckSelection" );
          });

          uploader.bind("UploadComplete", function(up, files) {
              self.uploadProcessing = false;
              self.uploadingFiles = [];
          });

      },

      addFilesUploadingModels : function( files , queries ){
          var self = this;
          _.each(files , function( file , index ){

              /*parts = /^(.+)(\.[^.]+)$/.exec(name);
              if (parts) {
                  name = parts[1];
                  ext = parts[2];
              } */

              var lIdx = file.name.lastIndexOf(".") ,
                  ext = file.name.substring(lIdx + 1) ,
                  name = file.name.substring( 0 , lIdx + 1 );
                     ////api.log( ext );
              var options = {
                  id          :  file.id ,
                  title       :  name,
                  filename    :  file.name,
                  description :  "",
                  name        :  name,
                  caption     :  "",
                  date        :  new Date(),
                  uploading   :  true ,
                  type        :  file.type
              };

              var attachment = new Attachment( options );

              queries = ( !$.isArray(queries) || queries.length == 0 ) ? Query.queries: queries;
                 ////api.log( queries );
              _.each( queries , function( query , index ){
                  var props = query.props;
                  if( Attachments.filters.search( props , attachment ) && Attachments.filters.type( props , attachment , ext ) ){
                      query.models.unshift( attachment );
                      query.length = query.models.length;
                  }
              });

          });

      },

      reset : function(){
          //reset hidden selection
          $("#hidden-selected-attachment-model").html( "" );
          //reset search box
          $(Library.searchBox).val("");

          $(Library.library).html( "" );

          //reset media lists for manage gallery & slideshow
          delete this.mediaList;
      },

      update : function( options , models ){

          delete this.options;

          var self = this;

          this.postId = api.currentPostId;

          this.options = {
              supportTypes      :   ["all"], //["all"] || ["image"] || ["image" , "video" , "audio"]
              activeTab         :   "library" , // library || upload || organize
              ShowOrganizeTab   :   false ,
              organizeTab       :   {
                  title             :  "" ,
                  buttons           :  [],
                  sortable          :  true ,
                  attachments       :  {}
              },
              selctedType       :   "single", //multiple || single
              dialog            :   {
                  title             :   "" ,
                  buttons           :  []
              } ,
              shortcode             :  "" ,
              attr                  :  "" ,
              subtypes              :  [] ,
              selectionSended       :  []
          };

          this.selectionSended = [];

          $.extend( this.options , options || {});

          $.extend( this , this.options);

          this.supportTypes = ( !$.isArray(this.supportTypes) ) ? [] : this.supportTypes;

          //delete unvalidate type
          this.supportTypes = $.grep( this.supportTypes , function( val , i){
              return val;
          });

          if( this.supportTypes.length == 0 )
              this.supportTypes.push("all");

          this.currentType = (this.supportTypes[0] == "all" || $.inArray( this.supportTypes[0] , getMediaTypes() ) == -1 ) ?  "": this.supportTypes[0];

          if(this.supportTypes.length == 1 && this.supportTypes[0] != "all"){
              $(Library.filterBox).hide();
          }else if( this.supportTypes.length > 1 ){
              $(Library.filterBox).show();
              $(Library.filterBox).find("option").hide();

              if( $.inArray( "all" , this.supportTypes ) == -1 ){
                  _.each( this.supportTypes , function( type ){
                     $(Library.filterBox).find("[value='"+ type + "']").show();
                  });
                  $(Library.filterBox).val( this.supportTypes[0] );
              }

          }

          //reset selection
          this.selctedType = (!this.selctedType) ? "single" : this.selctedType;
          delete this.selection;
          this.selection = new api.MediaSelection([] , {type : this.selctedType});
                              //alert( this.selectionSended );
          if( !_.isUndefined( this.selectionSended ) && !_.isEmpty( this.selectionSended ) && $.isArray( this.selectionSended ) && this.selctedType == "multiple" ){

              var allAttachments = $.extend( {} , Attachments.all.models , api.attachmentsSettings );

              _.each( this.selectionSended , function( attachmentId ){
                  attachmentId = parseInt( attachmentId ); 
                  if( attachmentId > 0 ){
                      var model = _.findWhere( allAttachments , { id : attachmentId } );

                      if( !_.isUndefined( model ) && model ){
                          api.Events.trigger("updateModelSelectionIds" , model.id , "add" );
                          self.selection.add( model );
                      }

                  }
              });
          }

          delete this.buttons;
          this.buttons = [];

          if( $.isArray( this.dialog.buttons ) && this.dialog.buttons.length > 0 ){

              if( !_.isUndefined( self.btnDisabledFuncs ) ){
                  _.each( self.btnDisabledFuncs , function( func ){

                      api.Events.unbind("addedModelToSelection" , func );

                      api.Events.unbind("removedModelFromSelection" , func );

                      api.Events.unbind("fileUploadCompleteCheckSelection" , func );

                  });
              }

              _.each( this.dialog.buttons , function( btn , index ){

                  var constructor   = api.LibraryButtonsConstructor[btn.type] || api.LibraryButtons ,
                      btnObj        = new constructor() ,
                      uId           = _.uniqueId('media_dialog_button_') ;

                  if(!_.isUndefined( btn.select_validation ) && btn.select_validation){

                      var _btnDisabled = function( models ){

                          models = !_.isUndefined( models ) ? models : self.selection.models ;

                          if( models.length > 0 ){
                              var VSelectedNum = 0;
                              _.each( models , function( model , key){
                                  //if attachment selected is show and not uploading proccess
                                  if( $(Library.library).find("li[data-id='" + model.id + "']").is(":visible") && ( _.isUndefined(model.uploading) || model.uploading === false ) ){
                                      VSelectedNum +=1;
                                  }
                              });

                              if( VSelectedNum > 0 )
                                  $( "#" + uId ).prop("disabled" , false);
                              else
                                  $( "#" + uId ).prop("disabled" , true);

                          }else
                              $( "#" + uId ).prop("disabled" , true);
                      };

                      if( _.isUndefined( self.btnDisabledFuncs ) )
                          self.btnDisabledFuncs = [];

                      self.btnDisabledFuncs.push( _btnDisabled );

                      api.Events.bind("fileUploadCompleteCheckSelection" , _btnDisabled );

                      api.Events.bind("addedModelToSelection" , _btnDisabled );

                      api.Events.bind("removedModelFromSelection" , _btnDisabled );

                  }

                  self.buttons.push({
                      text:  btn.title || "button",
                      create : function(){
                         $(this).data("btnLibraryDialogTab" , "library");
                         $(this).attr("id" , uId );
                         $(this).prop("disabled" , true);
                      },
                      click: function (e) {
                         btnObj.trigger( self.selection.models , self.mediaList , self , e.target );
                      }
                  });
              });
          }

          //show or hide org
          this.organizeTabReady( models );

          //active tab
          this.activeTab = (!this.activeTab) ? "library" : this.activeTab;
          $(Library.tab[this.activeTab]).tab('show');


          if( this.dialog.title )
            $( Library.dialog.selector ).dialog( "option" , "title", this.dialog.title );


          if( $.isArray( this.buttons ) && this.buttons.length > 0 )
              $( Library.dialog.selector ).dialog( "option", "buttons", this.buttons );


          $( Library.dialog.selector ).siblings(".ui-dialog-buttonpane").find('[role="button"]').each(function(){
              if(self.activeTab == $(this).data("btnLibraryDialogTab")){
                  $(this).show();
              }else{
                  $(this).hide();
              }
          });

          $( Library.dialog.selector ).dialog( "open" );
      },

      organizeTabReady : function( models ){
          var self = this;
          if( this.ShowOrganizeTab ){

              //init tab organize
              this.mediaList = new api.MediaOrganize({
                  moduleId : self.moduleId ,
                  moduleContainerId : self.moduleContainerId
              });

              ////api.log( this.mediaList );

              this.mediaList.addShortcodeModels(models);  // //api.log( this.mediaList );

             $(Library.tab.organize).parents("li:first").show();
             if( $.trim(this.organizeTab.title) )
                $(Library.tab.organize).find(".el_txt").text( this.organizeTab.title );

             if( $.isArray( this.organizeTab.buttons ) && this.organizeTab.buttons.length > 0 ){
                  _.each( this.organizeTab.buttons , function( btn , index ){

                      var constructor = api.LibraryButtonsConstructor[btn.type] || api.LibraryButtons ,
                      btnObj = new constructor();

                      self.buttons.push({
                          text:  btn.title || "button",
                          create : function(){
                             $(this).data("btnLibraryDialogTab" , "organize");
                          },
                          click: function () {
                             btnObj.trigger( self.selection.models , self.mediaList , self );
                          }
                      });

                  });
             }

          }else{
             $(Library.tab.organize).parents("li:first").hide();
          }

      },

      set : function( props , refresh ){
            var self = this , html = "";
            //delete this.template;

            var query = api.MediaQuery.get(props) , perPage = query.args.posts_per_page ;

            if( ( query.length >= perPage  && !refresh ) || !query._hasMore ){
                self.libView( query );
                self.libLoadProcessing = false;
                return ;
            }

            if(self.uploadProcessing === true){
                ////api.log(self.uploadingFiles);
                self.addFilesUploadingModels(self.uploadingFiles , [query]);
                self.libView( query );
            }

            ////api.log( query );
            self.ajaxProcessing = true;

            query.more().done(function(){
                ////api.log( query.models );

                var startTime = new Date();

                self.libView( query );

                ////api.log( new Date() - startTime );
                self.libLoadProcessing = false;
                self.ajaxProcessing = false;
            });

            //library.set();
      },

      libView : function( query ){
          var html = "" , self = this;
          this.template = api.template("sed-media-lib-item");  //self.template( model )
          
          _.each(query.models , function( model , key){
              html += self.template( model );
          });

          $(Library.library).html( html );
          //self.selection.models
          _.each(self.selection.models , function( model , key){
              $(Library.library).find("li[data-id='" + model.id + "'] > a").addClass( "sed-media-item-selected" );
          });
      } ,

      selectionView : function( item ){
          var self = this , aItem = item.find(">a");
          if(self.selctedType == "multiple"){
              aItem.toggleClass( "sed-media-item-selected" );
          }else if(self.selctedType == "single"){
              if(aItem.hasClass("sed-media-item-selected")){
                  aItem.removeClass( "sed-media-item-selected" );
              }else{
                  $(Library.library).find("li.attachment > a").removeClass( "sed-media-item-selected" );
                  aItem.addClass( "sed-media-item-selected" );
              }

          }
      }


    }, {

        hiddenSelectedModels : function( models ){
            var cssArr = [] , css = "";
            _.each( models , function(model , index){
                cssArr.push( "#site-editor-media-library .attachment[data-id='" + model.id + "']" );
            });
            css = cssArr.join(",");
            css += "{display:none;}";

            if( $("#hidden-selected-attachment-model").length == 0 )
                $("<style id='hidden-selected-attachment-model'>" + css + "</style>").appendTo( $("head") );
            else
                $("#hidden-selected-attachment-model").html( css );
        },
        /**
         * @readonly
         */
        dialog  : {
            selector : "#sed-dialog-media-library",
            tpl      : "#tmpl-dialog-media-library",
            options : {
                  autoOpen      : false,
                  dialogClass   : "library-dialog",
                  modal         : true,
                  width         : 850,
                  height        : 550 ,
                  close         : function(){
                       api.media.library.reset();
                  }
            }
        },
        /**
         * @readonly
         */
         library : '#site-editor-media-library' ,

         tab     : {
            library  : "#sed_media_library_panel_tab" ,
            upload   : "#sed_media_library_upload_tab" ,
            organize : "#sed_media_library_organize_tab"
         } ,


         tabContent : {
            library  : "#sed_media_library_panel" ,
            upload   : "#sed_media_library_upload" ,
            organize : "#sed_media_library_organize"
         } ,

         //uploader element
         uploader : "#sed-media-lib-uploader" ,
         //search box element
         searchBox : '#attachment-search' ,
         filterBox : '#attachment-type-filter',
         container : "#sed-media-library-container"

    });


	/**
	 * api.template( id )
	 *
	 * Fetches a template by id.
	 *
	 * @param  {string} id   A string that corresponds to a DOM element with an id prefixed with "tmpl-".
	 *                       For example, "attachment" maps to "tmpl-attachment".
	 * @return {function}    A function that lazily-compiles the template requested.
	 */
	api.template = _.memoize(function ( id ) {
		var compiled,
			options = {
				evaluate:    /<#([\s\S]+?)#>/g,
				interpolate: /\{\{\{([\s\S]+?)\}\}\}/g,
				escape:      /\{\{([^\}]+?)\}\}(?!\})/g,
				variable:    'data'
			};

		return function ( data ) {
			compiled = compiled || _.template( $( '#tmpl-' + id ).html(), null, options );
			return compiled( data );
		};
	});

                     
	media.ajax = api.wpAjax = {
		//settings: settings.ajax || {},

		/**
		 * wp.ajax.post( [action], [data] )
		 *
		 * Sends a POST request to WordPress.
		 *
		 * @param  {string} action The slug of the action to fire in WordPress.
		 * @param  {object} data   The data to populate $_POST with.
		 * @return {$.promise}     A jQuery promise that represents the request.
		 */
		post: function( action, data ) {
			return api.wpAjax.send({
				data: _.isObject( action ) ? action : _.extend( data || {}, { action: action })
			});
		},

		/**
		 * wp.ajax.send( [action], [options] )
		 *
		 * Sends a POST request to WordPress.
		 *
		 * @param  {string} action  The slug of the action to fire in WordPress.
		 * @param  {object} options The options passed to jQuery.ajax.
		 * @return {$.promise}      A jQuery promise that represents the request.
		 */
		send: function( action, options ) {

            var promise, deferred;

            if ( _.isObject( action ) ) {
                options = action;
            } else {
                options = options || {};
                options.data = _.extend( options.data || {}, { action: action });
            }

            options = _.defaults( options || {}, {
                type:    'POST',
                url:     SEDAJAX.url,
                context: this
            });

            deferred = $.Deferred( function( deferred ) {
                // Transfer success/error callbacks.
                if ( options.success )
                    deferred.done( options.success );
                if ( options.error )
                    deferred.fail( options.error );

                delete options.success;
                delete options.error;

                // Use with PHP's wp_send_json_success() and wp_send_json_error()
                deferred.jqXHR = $.ajax( options ).done( function( response ) {
                    // Treat a response of `1` as successful for backwards
                    // compatibility with existing handlers.
                    if ( response === '1' || response === 1 )
                        response = { success: true };

                    if ( _.isObject( response ) && ! _.isUndefined( response.success ) )
                        deferred[ response.success ? 'resolveWith' : 'rejectWith' ]( this, [response.data] );
                    else
                        deferred.rejectWith( this, [response] );
                }).fail( function() {
                    deferred.rejectWith( this, arguments );
                });
            });

            promise = deferred.promise();
            promise.abort = function() {
                deferred.jqXHR.abort();
                return this;
            };

            return promise;
            
		}
	};



    $( function() {



          api.media.library = new api.SedMediaLibrary();

           ////api.log( $("#tmpl-dialog-media-library") );
        /*

        $("#sed-media-lib-uploader").on("UploadFile",function(e , up, files){

        });

        $("#sed-media-lib-uploader").on("UploadComplete",function(e , up, files){

        });

          alert( _.template("Using 'with': <%= data.answer %>", {answer: 'no'}, {variable: 'data'}) );

        */
    });
})( sedApp, jQuery );