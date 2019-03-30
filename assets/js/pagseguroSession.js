/* global PagSeguroDirectPayment */

$(function(){
    var sessionCode = $('#cart_items').attr("data-session");
    PagSeguroDirectPayment.setSessionId(sessionCode);
});

