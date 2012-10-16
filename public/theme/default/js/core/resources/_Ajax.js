dojo.provide("core.resources.Ajax");
require(["core/Loader"], function(Loader){
    core.resources.Ajax = function(){};
    dojo.declare("core.resources.Ajax", null, {
        constructor: function(){
            this.timeout = 10000;
        },
        xhr: function(params){
            var xhr = dojo.xhr(params.method, {
                url: params.url,
                timeout: 10000,
                handleAs: params.handleAs,
                content: params.content,
                load: function(response, ioArgs){
                    return response;
                },
                error: function(response, ioArgs){
                    switch(ioArgs.xhr.status) {
                        case 403:
                            alert('403 |  Ваша сессия закончилась. Для продолжения работы необходимо авторизоваться');
                            window.location.reload();
                            break;
                        case 404:
                            alert('404 | Страница "' + window.location.href + '/' + url + '" не найдена');
                            break;
                        case 500:
                            alert('500 | Ошибка при обращении к серверу');
                            break;
                        default:
                            alert('All | Ошибка обмена данными с сервером');
                    }
                    xhr.cancel();
                    return response;
                }
            });
            return xhr;
        }
    });
});

