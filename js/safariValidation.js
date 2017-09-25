// JQuery function to validate safari form submission
// Safari does not support HTML5's required field. 
$(document).ready(function(){
    $("form").submit(function(e) {

    var ref = $(this).find("[required]");

    $(ref).each(function(){
        if ( $(this).val() == '' )
        {
            alert("Required field should not be blank.");

            $(this).focus();

            e.preventDefault();
            return false;
        }
    });  return true;
});
});