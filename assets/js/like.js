// всё, что надо для работы с куками и лайками

// ставим куку на год
function setCookie(cookieName,cookieValue) {
 var today = new Date();
 var expire = new Date();
 expire.setTime(today.getTime() + 3600000*24*356);
 document.cookie = cookieName+"="+escape(cookieValue)
                 + ";expires="+expire.toGMTString();
}

// получить куку
function getCookie(name) {
    var cookie = " " + document.cookie;
    var search = " " + name + "=";
    var setStr = null;
    var offset = 0;
    var end = 0;
    if (cookie.length > 0) {
        offset = cookie.indexOf(search);
        if (offset != -1) {
            offset += search.length;
            end = cookie.indexOf(";", offset)
            if (end == -1) {
                end = cookie.length;
            }
            setStr = unescape(cookie.substring(offset, end));
        }
    }
    return(setStr);
}

// получить массив лайков из куки, если она пустая - вернуть пустой массив
function getLike(){
    var like = getCookie('like');
    if (like){
        like = JSON.parse(like);
    } else{
        like = new Array;
    };
    return like;
}

// проверяем, есть ли в куке такой ид
function inLike(id){
    var like = getLike();
    if ( $.inArray(id, like) == -1 ){     
        return false;
    }else{
        return true;
    };
        
}

// добавляем ид в куку
function setLike(id){
    var like = getLike();
    // кладем туда ид лайкнутого сообщения, если там такого еще нету, и сохраняем
    if ( $.inArray(id, like) == -1 ){     
        like.push(id);
        like = JSON.stringify(like);
        setCookie('like', like);
    };
}