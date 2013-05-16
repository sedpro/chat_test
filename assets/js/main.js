// отображаем полученные сообщения
function render(content){

    var container = $('#messages');

    var all_incoming = [];
    $.each( content,function(index, elem){
        all_incoming.push(elem.id); 
    });
    // console.log(all_incoming);
    // удаляем старые
    container.find('.message').each( function(index, elem){
        if ( ! in_array($(elem).data('id'), all_incoming) ){
            $(elem).slideUp(function(){
                $(this).remove();
            });
            // console.log($(this));
        };
    });

    // выкладываем новые
    $.each( content, function( index, obj ) {
        if( ! $("#message_"+obj.id).length ){

            // аттачменты этого сообщения
            var attach_frag = '';
            if (obj.attaches){
                $.each( obj.attaches, function( index, attach ) {
                    attach_frag += $.trim( $('#attach_template_'+attach.a_type).html() )
                        .replace( /{{id}}/ig, attach.a_id )
                        .replace( /{{link}}/ig, attach.a_link );
                });
            };

            // вставляем, где надо, кнопки "удалить" и "лайкнуть"
            var button = '';
            if ( $('#name_span').text() == obj.name ){
                // свои можно удалить
                button = $('<span></span>',{
                    'class':'message_delete button',
                    'text': 'удалить'
                })[0].outerHTML;
            }else{
                // чужие можно лайкнуть, но только те, кого нет в куке
                if( ! inLike(obj.id) ){
                    button = $('<span></span>',{
                            'class':'message_like button',
                            'text': 'лайкнуть'
                        })[0].outerHTML;
                }else{
                    button = $('<span></span>',{
                            'class':'message_like_done',
                            'text': 'лайкнуто'
                        })[0].outerHTML;
                }
            };

            // получаем html сообщения
            var frag = $.trim( $('#message_template').html() )
                .replace( /{{id}}/ig, obj.id )
                .replace( /{{name}}/ig, obj.name )
                .replace( /{{text}}/ig, obj.text )
                .replace( /{{like}}/ig, obj.like )
                .replace( /{{time}}/ig, obj.time )
                .replace( /{{delete_span}}/ig, button )
                .replace( /{{attaches}}/ig, attach_frag );

            // определяем, куда его воткнуть
            var all_messages = [];
            container.find('.message').each( function(index, elem){
                all_messages.push($(elem).data('id')); 
            });
            
            if (all_messages.length===0){
                container.append(frag);
            }else{
                all_messages.sort(function(a,b){return a-b;});

                // новый элемент должен быть первым
                if(obj.id<all_messages[0]){
                    container.append(frag);
                };
                
                // новый элемент должен быть последним
                if(obj.id>all_messages[all_messages.length-1]){
                    container.prepend(frag);
                };

                // вставлем перед первым элементом, чей ид больше
                for (i=0; i<all_messages.length; i++){               
                    if(obj.id<all_messages[i]){
                        $('message_'+all_messages[i]).before(frag);
                    };
                }
            }

            // обработчики событий
            if( $('#name_span').text() == obj.name ){
                // удаление сообщения - обработка нажатия кнопки
                $('#message_'+obj.id).find('.message_delete').on('click', function(){
                    var id = $(this).parent('.header').parent('.message').data('id');
                    $.post('/message/delete', { 'id': id }, function(data){
                        data = JSON.parse(data);
                        render(data);
                    });
                });
            }else{
                // лайкнуть - обработка нажатия кнопки
                $('#message_'+obj.id).find('.message_like').on('click', function(){
                    var self = $(this);
                    var id = $(this).parent('.header').parent('.message').data('id');
                    if (inLike(id)) return;
                    $.post('/message/like', { 'id': id }, function(data){
                        self.removeClass('message_like')
                            .addClass('message_like_done')
                            .removeClass('button')
                            .text('лайкнуто');
                        setLike(id);
                        self.parent('.header').find('.message_like_num').text(data);
                    });
                });
            };
        }else{
            // если это сообщение уже есть, обновляем лайки
            $('#message_'+obj.id).find('.message_like_num').text(obj.like);
        };
    });

};

// проверяет наличие элемента в массиве
function in_array(value, array) 
{
    for(var i = 0; i < array.length; i++) 
    {
        if(array[i] == value) return true;
    }
    return false;
}

// возвращает случайное число от m до n
function randomNumber (m,n)
{
  m = parseInt(m);
  n = parseInt(n);
  return Math.floor( Math.random() * (n - m + 1) ) + m;
}

// получаем список сообщений и отображаем его
function refresh(){
    $.post('/message/get', function(data){
        data = JSON.parse(data);
        render(data);
    });
    // периодически повторяем
    setTimeout(refresh,randomNumber(4000, 6000));
}

$(document).ready(function() {

    // сразу после загрузки страницы получаем список сообщений и выводим его.
    refresh();

    // пользователь вводит имя
    $('#name_form').on('submit', function(){
        if ($('#name').length <1 ) return false;
        $.post('/user/add', $(this).serialize(), function(data){
            data = JSON.parse(data);
            if (data.status=="zero") return;
            if (data.status=="duplicate"){
                $('<div></div>', {
                    'text':'Это имя уже используется. Выберите другое.'
                }).appendTo('#name_form').animate({
                    'opacity':0
                }, 5000, function(){
                    $(this).remove();
                });
                return;
            };
            if (data.status=="created"){
                $('#name_span').text(data.name);
                $('#name_form').addClass('hide');
                $('#main_form').removeClass('hide');
                $('#main_greeting').removeClass('hide');
            };
        });
        return false;
    })

    // добавление инпутов под аттачменты
    $('#more_link').on('click', function(){
        $('<input/>',{
            'type':"text",
            'class':"link",
            'name':"link[]",
            'placeholder':"адрес сайта(для роликов - youtube.com)"
        }).appendTo('#link_container');
    });

    // добавление инпутов под фотки
    $('#more_photo').on('click', function(){
        $('<input/>',{
            'type':"file",
            'class':"photo",
            'name':"photo[]"
        }).appendTo('#photo_container');
    });
});