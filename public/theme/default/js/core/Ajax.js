define([
    "dojo/_base/declare",
    "dojo/_base/lang",
    "dojo/request/xhr",
    "dojo/on",
    "dojo/keys",
    "dojo/Deferred",
    "core/layout/Msg",
    "dojo/aspect"
], function(declare, lang, xhr, on, keys, Deferred, Msg, aspect){

    var Ajax = declare(null, {
        // Время ожидания ответа. Изменная для глобального пространства
        timeout: 15000,
        // Мессенджер
        Messenger: null,
        constructor: function(){
            this.Messenger = new Msg({
                domNode:        'messenger'
            });
        },
        send: function(url, options){
            // undefined
            var undefinedOptions = {
                data:           {},
                method:         'POST',
                handleAs:       'json',
                processing:     true, // Подробное отображение всех этапов процесса
                preventCache:   false
            };
            lang.mixin(undefinedOptions, options || {});
            return this._request(url, undefinedOptions);
        },
        load: function(url, options){
            // undefined
            var undefinedOptions = {
                query:          {},
                method:         'GET',
                handleAs:       'json',
                processing:     true,
                preventCache:   true
            };
            lang.mixin(undefinedOptions, options);
            return this._request(url, undefinedOptions);
        },
        _request: function(url, options){
            lang.mixin(options, {
                timeout: this.timeout
            });

            switch(options.handleAs.toLowerCase()){
                case 'json':
                    url += '/format/json';
                    break;
                default:
                    url += '/format/html';
            }

            var processDeferred = this.Messenger.process(function(timeout){
                clearTimeout(timeout); // Удалить счетчик автозавершения процесса
                if(options.overlay) options.overlay.show();
            });

            processDeferred.promise.always(function(){
                if(options.overlay) options.overlay.hide();
            });


/*            var status = (options.method  == ('POST' || 'PUT' || 'DELETE')) ? 'PROCESS_SEND' : 'PROCESS_LOAD';
            if(true === options.processing){
                var handler = this.Messenger.send(status);
                handler.show();
                clearTimeout(handler.clearTimeout);
            }

            // Удалить Сообщение и Оверлей
            processDeferred.promise.always(function(){
                if(options.overlay) options.overlay.hide();
                if(true === options.processing){
                    setTimeout(function(){
                        handler.remove();
                    }, 700);
                }
            });*/

            // Прерывание процесса пользователем
/*            on(window, "keypress", function(event){
                 if(event.keyCode == keys.ESCAPE) {
                     processDeferred.cancel('PROCESS_STATE_ABORTED');
                 }
             });*/

            var request =  xhr(url, options);
            request.then(function(response){
                if(response && 'object' == typeof response) {
                    // Состояния приложения и внутренних операций
                    switch (response.status.toLowerCase()) {
                        case 'ok':
                            // Запустить цепочку успешного получения данных
                            processDeferred.resolve('PROCESS_STATE_OK');
                            break;
                        case 'error':
                            // ЗАпустить цепочку неудачного получения данных
                            processDeferred.reject('PROCESS_STATE_FAILED');
                            break;
                    }
                } else {
                    processDeferred.resolve('PROCESS_STATE_OK');
                }
            }, function(error){
                //error.response;
                // Состояние операции
                switch(error.response.xhr.status){
                    case 0:
                        processDeferred.reject('PROCESS_STATE_ABORTED');
                        break;
                    case 500:
                        processDeferred.reject('SERVER_ERROR');
                        break;
                    case 404:
                        processDeferred.reject('SERVER_NOT_FOUND');
                        break;
                    case 403:
                        processDeferred.reject('SERVER_FORBIDDEN');
                        break;
                    default:
                        processDeferred.reject('PROCESS_STATE_FAILED');
                }
            });

            return request;
        }
    });

    return new Ajax();

});