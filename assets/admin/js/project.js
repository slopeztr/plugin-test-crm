var $ = jQuery.noConflict();

try {
  $( document ).ready( function() {
    $( '#acf-field_6265f6dc5c8a9' ).on( 'change', function() {
      var dealer = $( this ).val();

      if( dealer !== '' ) {
        $.ajax({
          type: 'post',
          url: project_vars.url,
          data: { 
            action: project_vars.action,
            project_nonce: project_vars.project_nonce,
            dealer: dealer
          },
          success: function( response ) {
            $( '#acf-field_626821aa1f000' ).empty();
            $( '#acf-field_626821aa1f000' ).append( '<option value="">- Elige -</option>' ); 
            $.each( response.data, function( key, item ) {
              $( '#acf-field_626821aa1f000' ).append(
                $( '<option></option>' ).val( item[ 'post_id' ] ).html( item[ 'name' ] )
              );
            });
          }
        });
      }
    });

    // remover link del post en comment
    setTimeout( function() {
      $( '.column-comment > a' ).contents().unwrap();
    }, 500 );

    // ocultar y deshabilitar el campo value_m2 y final_value
    $( '#hide_value_m2' ).hide();
    if( $( '#acf-field_62b33004fc86a-field_62b3361a24ec9' ).val() ) {
      $( '#acf-field_62b33004fc86a-field_62b3361a24ec9' ).prop( 'readonly', true );
      $( '#hide_value_m2' ).show();
    }

    $( '#hide_final_value' ).hide();
    if( $( '#acf-field_62b33004fc86a-field_62b33934cb2bf' ).val() ) {
      $( '#acf-field_62b33004fc86a-field_62b33934cb2bf' ).prop( 'readonly', true );
      $( '#hide_final_value' ).show();
    }

    // deshabilitar el campo estimated_value.
    if( $( '#acf-field_62791fcde9f99' ).val() ) {
      $( '#acf-field_62791fcde9f99' ).prop( 'readonly', true );
    }

    // calcular el estimado por entrada(input) del usuario.
    $( '#acf-field_62791f83e9f98, #acf-field_6287fb07d3e7d' ).on( 'input', function() {
      var project_value = $( '#acf-field_62791f83e9f98' ).val();
      var estimated_percentage = $( '#acf-field_6287fb07d3e7d' ).val();
      $( '#acf-field_62791fcde9f99' ).val( project_value * estimated_percentage );
    });
  });

} catch( err ) {
  _error( err );
}

function _error( err ) {
  if( typeof console !== "undefined" ) {
    console.log( err );
  }
}