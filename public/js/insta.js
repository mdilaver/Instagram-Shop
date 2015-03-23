
$(function() {
    $(".box-product .boxgrid").fadeTo("fast", 1.0); // This sets the opacity of the thumbs to fade down to 100% when the page loads.
    // Thumbnail hover.
    $(".box-product .boxgrid").hover(function() {
        $('img', this).fadeTo("fast", 0.9); // This should set the opacity to 80% on hover.
    }, function() {
        $('img', this).fadeTo("fast", 1.0); // This should set the opacity back to 100% on mouseout.
    });
});