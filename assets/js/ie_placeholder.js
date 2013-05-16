$(function(){
    if ( $.browser.msie ){
        $('input[placeholder], textarea[placeholder]').each(function() {
            var self = $(this);
            if (self.val()==''){
                self.val(self.attr('placeholder')).css({'color': 'gray'});
            };
            self.on( 'focus', function(){
                if (self.val()==self.attr('placeholder')){
                    self.val('').css({'color': 'black'});
                };   
            });
            self.on( 'blur', function(){
                if (self.val()==''){
                    self.val(self.attr('placeholder')).css({'color': 'gray'});
                };               
            });
        });
    };
});