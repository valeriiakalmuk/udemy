jQuery(document).ready(function($){
    if(localStorage.getItem('payment_cart') != ''){
        val =  localStorage.getItem('payment_cart');
        console.log( val );
        $(".input-radio[value=" + val + "]").prop('checked', true);
    }
    $('.input-radio').change(function(){
        category=this.value;
        localStorage.setItem('payment_cart', category);
        console.log( localStorage.getItem('payment_cart') );
    });
});