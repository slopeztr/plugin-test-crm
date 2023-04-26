var $ = jQuery.noConflict();

try {
  $( document ).ready( function() {
    // silent is gold.
  }); 

} catch( err ) {
  _error( err );
}

function _error( err ) {
  if( typeof console !== "undefined" ) {
    console.log( err );
  }
}