var $ = jQuery.noConflict();

try {
  $( document ).ready( function() {
    $( 'div.submitted-on > a' ).contents().unwrap();
    $( 'td.column-comment > a' ).contents().unwrap();
  });

} catch( err ) {
  _error( err );
}

function _error( err ) {
  if( typeof console !== "undefined" ) {
    console.log( err );
  }
}