dojo.provide("core.resources.Ajax");
require(["core/Loader"], function(Loader){
    core.resources.Ajax = function(){};
    dojo.declare("core.resources.Ajax", null, {
        constructor: function(){
            this.Loader = new Loader(dojo.byId('loader'));
            return this;
        },
        xhr: function(url, params, method, responseFormat){
            // Нормализация параметров
            method = method.toUpperCase();
            responseFormat = responseFormat.toLowerCase();

            // Ответ по умолчанию для POST запросов
            if((method == 'POST' || method == "DELETE") && responseFormat == undefined) {
                responseFormat = 'json';
            }

            var self = this;
            self.Loader.show();

            var xhr = dojo.xhr(method, {
                url: url,
                timeout: 10000,
                handleAs: responseFormat,
                content: params,
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
                },
                handle: function(response, ioArgs) {
                    self.Loader.hide();
                }
            });
            return xhr;
        }
    });
});

