let YoutubeImportSecondLine = {};

YoutubeImportSecondLine.API = {

  post : function( path, request_data, callback ) {
    return this.__request( path, 'POST', request_data, callback );
  },

  fetch : function( action, request_data, callback ) {
    return this.__request( action, 'GET', request_data, callback );
  },

  __request : function( path, method, request_data, callback ) {
    return jQuery.ajax( {
      url        : window.youtube_import_settings.rest_url + path,
      method     : method,
      beforeSend : function ( xhr ) {
        xhr.setRequestHeader( 'X-WP-Nonce', window.youtube_import_settings.rest_nonce );
      },
      data       : request_data
    } ).done( function ( response, statusText, xhr ) {
      callback( response );
    } ).fail( function( $xhr ) {
      callback( $xhr.responseJSON );
    });
  },

};

function YIS_element_loader( type ) {
  let response = '';

  response += '<div class="pis-application-loader-wrapper"' + ( type === 'mini' ? ' data-pis-loader-type="mini"' : '' ) + '>';

  if( type === 'mini' ) {
    response += '<div><div></div></div>';
  } else {
    response += '<img alt="loader" src="' + window.youtube_import_settings.loader_icon + '"/>';
    response += '<div>' +
                  '<div>' +
                    '<div>' +
                      '<div>' +
                      '</div>' +
                    '</div>' +
                  '</div>' +
                '</div>';
  }


  response += '</div>';

  return response;
}

jQuery(document).ready( function() {
  jQuery( "#youtube-importer-import_youtube-dismissible" ).on( "click", ".notice-dismiss", function( event ) {
    event.preventDefault();
    event.stopImmediatePropagation();

    YoutubeImportSecondLine.API.post( "youtube-importer-import_youtube/v1/admin-dismiss-notice", {}, function() {} );
  });

  jQuery( "[data-youtube-importer-rest-api-request]" ).off( "click" ).on( "click", function() {
    let request_path = jQuery(this).attr( 'data-youtube-importer-rest-api-request' ),
        trigger = jQuery(this),
        trigger_text = trigger.html();

    trigger.attr( "disabled", "disabled" ).html( YIS_element_loader( "mini" ) );

    YoutubeImportSecondLine.API.post( request_path, {}, function( response ) {
      if ( typeof trigger.attr( "data-importyoutube-import-success-message" ) !== 'undefined' )
        trigger.html( trigger.attr( "data-importyoutube-import-success-message" ) );
      else
        trigger.html( trigger_text );
    });
  });

  jQuery( '.youtube_importer_form' ).each( function() {
    let formObject = jQuery(this);

    formObject.find( '[name="post_type"]' ).on( "change", function() {
      let taxonomiesSelectObject = formObject.find( '[name="post_taxonomies[]"]'),
          current_post_type = jQuery(this).val();

      taxonomiesSelectObject.find( " > option" ).each( function() {
        if( jQuery(this).is( '[data-post-types~="' + current_post_type + '"]' ) )
          jQuery(this).show();
        else
          jQuery(this).hide().prop( "selected", false );
      });

      formObject.find( '.importyoutube-post-type-logic > option' ).each( function() {
        if( typeof jQuery(this).attr( 'data-post-types' ) === 'undefined' )
          return true;

        if( jQuery(this).is( '[data-post-types~="' + current_post_type + '"]' ) )
          jQuery(this).show();
        else
          jQuery(this).hide();
      });

    }).trigger( "change" );

    formObject.find( '.importyoutube_import_advanced_settings_toggle' ).on( "click", function() {
      if( jQuery(this).hasClass( "active" ) ) {
        jQuery(this).parent().find( ' > .importyoutube_import_advanced_settings' ).slideUp( "slow" );
        jQuery(this).removeClass( "active" );
      } else {
        jQuery(this).parent().find( ' > .importyoutube_import_advanced_settings' ).slideDown( "slow" );
        jQuery(this).addClass( "active" );
      }
    });

    formObject.find( '.importyoutube_import_field_media_image_handler .button-primary' ).on( "click", function( event ) {
      event.preventDefault();
      event.stopImmediatePropagation();

      let image_container = jQuery(this).parents( '.importyoutube_import_field_media_image_handler:first' );

      let image = wp.media({
        title: 'Upload Image File',
        multiple: false
      }).open().on('select', function(e){
        let uploaded_file = image.state().get('selection').first();

        if( typeof uploaded_file.toJSON().id === 'undefined' ) {
          alert( "Missing Image ID, it needs to be uploaded on the server" );
          return;
        }

        image_container.find( 'input[type="hidden"]' ).val( uploaded_file.toJSON().id );

        if( image_container.find( " img " ).length !== 0 )
          image_container.find( " img " ).attr( 'src', uploaded_file.toJSON().url );
        else
          image_container.prepend( '<img src="' + uploaded_file.toJSON().url + '"/>' );

        image_container.find( '.button-secondary' ).show();
      });
    });

    formObject.find( '.importyoutube_import_field_media_image_handler .button-secondary' ).on( "click", function( event ) {
      event.preventDefault();
      event.stopImmediatePropagation();

      let image_handler = jQuery(this).parents( '.importyoutube_import_field_media_image_handler:first' );

      image_handler.find( '.button-secondary' ).hide();
      image_handler.find( 'img' ).remove();
      image_handler.find( 'input[type="hidden"]' ).val( '' );
    });

    formObject.on( "submit", function( event ) {
      event.preventDefault();
      event.stopImmediatePropagation();

      formObject.find( '[data-importyoutube-import-notification]' ).remove();

      let request_data = {};

      // Hidden inputs are defaults for the checkboxes, PHP way of achieving it, and emulating it here.
      formObject.find( 'input:not([type="checkbox"]), select:not([multiple])' ).each( function() {
        request_data[ jQuery(this).attr( "name" ) ] = jQuery(this).val();
      });

      formObject.find( 'input[type="checkbox"]:checked' ).each( function() {
        request_data[ jQuery(this).attr( "name" ) ] = jQuery(this).val();
      });

      formObject.find( 'select[multiple]' ).each( function() {
        request_data[ jQuery(this).attr( "name" ).replace( '[]', '' ) ] = jQuery(this).val();
      });

      formObject.slideUp( "slow" );
      formObject.after( YIS_element_loader() );

      YoutubeImportSecondLine.API.post( "youtube-importer-import_youtube/v1/import-feed", request_data, function( response ) {
        formObject.parent().find( '.pis-application-loader-wrapper' ).remove();

        if( typeof response === 'undefined' ) {
          formObject.find( 'button:last' ).before( '<div data-importyoutube-import-notification="danger">Server Error</div>');
        } else if( typeof response.data !== 'undefined' && ( response.data.status > 400 || response.data.status < 300 ) ) {
          formObject.find( 'button:last' ).before( '<div data-importyoutube-import-notification="danger">' + response.message + '</div>');
        } else if( typeof response.message !== 'undefined' ) {
          formObject.find( 'button:last' ).before( '<div data-importyoutube-import-notification="success">' + response.message + '</div>');
        } if( typeof response.messages !== 'undefined' ) {
          let content = '';

          jQuery.each( response.messages, function( k, message_data ) {
            content += '<div data-importyoutube-import-notification="' + message_data.type + '">' + message_data.message + '</div>';
          });

          formObject.find( 'button:last' ).before( content );
        }

        formObject.slideDown( "slow" );
      } );
    });
  });
});
jQuery(document).on('click',".removefromexport",function(){
	//e.preventDefault();
	if (jQuery(this).is(":checked")) {
		
		var alreadyids=jQuery("input[name='channel_removed_id']").val();
		var newaddedval=jQuery(this).attr('data-id');
		var newmainval="";
		if(alreadyids){
			newmainval=alreadyids+','+newaddedval;
		}else{
			newmainval=newaddedval;
		}
		jQuery("input[name='channel_removed_id']").val(newmainval);
	}else{
		var alreadyids=jQuery("input[name='channel_removed_id']").val();
		var newaddedval=jQuery(this).attr('data-id');
		var newmainval="";
		newmainval=alreadyids.replace(newaddedval, '');
		jQuery("input[name='channel_removed_id']").val(newmainval);
	}
	
	//jQuery("#remove"+newaddedval+"").remove();
});
jQuery("#previewimport").on('click',function(e){
	e.preventDefault();
	jQuery("#importedvideoscheck").append( YIS_element_loader() );
	jQuery("input[name='channel_removed_id']").val('');
	jQuery("#importedvideoscheck").html('');
	var channelid=jQuery("input[name='channel_id']").val();
	var datefrom=jQuery("input[name='import_date_from']").val();
	let request_data = {};
	if(channelid){
		jQuery('div#mainloaderdatecheck').show();
		jQuery("#loadingimage").show();
		request_data['channel_id'] = channelid;
		request_data['import_date_from'] = datefrom;
		
		
		YoutubeImportSecondLine.API.post( "youtube-importer-import_youtube/v1/check-feed", request_data, function( response ) {
		if( typeof response === 'undefined' ) {
          jQuery("#importedvideoscheck").html( '<div data-importyoutube-import-notification="danger">Server Error</div>');
        } else if( typeof response.data !== 'undefined' && ( response.data.status > 400 || response.data.status < 300 ) ) {
          jQuery("#importedvideoscheck").html( '<div data-importyoutube-import-notification="danger">' + response.message + '</div>');
        } else if( typeof response.message !== 'undefined' ) {
          jQuery("#importedvideoscheck").html( '<div data-importyoutube-import-notification="success">' + response.message + '</div>');
        } if( typeof response.messages !== 'undefined' ) {
          let content = '';
			
          jQuery.each( response.messages, function( k, message_data ) {
            content += '<div class="youtubevideoinners" data-importyoutube-import-notification="' + message_data.type + '">' + message_data.message + '</div>';
          });
		  jQuery("#loadingimage").hide();
			jQuery("#importedvideoscheck").html(content);
          
        }	
			
			
			
		});
		/* jQuery.ajax({
         type : "post",
         url : "http://localhost/testwp/wp-admin/admin-ajax.php",
         data : {action: "import_youtube_preview_channel", channelid : channelid},
         success: function(response) {
            if(response.type == "success") {
               //jQuery("#vote_counter").html(response.vote_count)
            }
            
         }
      })  */
	}else{
	alert('Please Enter channel id');	
	}
	
	
});
jQuery("span.close-imp").on('click',function(e){
	jQuery('div#mainloaderdatecheck').hide();
});