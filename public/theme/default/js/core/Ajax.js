define([
    "dojo/_base/declare",
    "dojo/_base/lang",
    "dojo/request/xhr",
    "dojo/Deferred",
    "core/layout/Msg",
    "core/layout/Messenger",
    "core/layout/Processing"
], function(declare, lang, xhr, Deferred, Msg, Messenger, Processing){

    var Ajax = declare(null, {
        // Время задержки. Служит для визуального отображения Процесса
        delay: 500,
        // Время ожидания ответа
        timeout: 15000,
        //
        Messenger: null,
        constructor: function(){
            this.Messenger = new Msg({
                domNode: 'messenger'
            });
        },
        send: function(url, options){
            var requestOptions = {
                data:           {},
                method:         'POST',
                handleAs:       'json',
                processing:     true,
                preventCache:   false
            };
            lang.mixin(requestOptions, options || {});
            return this._request(url, requestOptions);
        },
        load: function(url, options){
            var requestOptions = {
                query:          {},
                method:         'GET',
                handleAs:       'json',
                processing:     true,
                preventCache:   true
            };
            lang.mixin(requestOptions, options);
            return this._request(url, requestOptions);
        },
        _request: function(url, options){

            options.timeout = this.timeout;
            var Ajax = this;
            var processDeferred = this.Messenger.process(function(){
                var status = (options.method  == ('POST' || 'PUT' || 'DELETE')) ? 'PROCESS_SEND' : 'PROCESS_LOAD';
                var handler = Ajax.Messenger.send(status);
                clearTimeout(handler);
            });

            // Или можно создать дополнительный объект Deferred
            return xhr(url, options).then(function(response){
                if(response && 'object' == typeof response) {
                    if(response.result) {
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
                    }
                } else {
                    processDeferred.resolve('PROCESS_STATE_OK');
                }
                return response;
            }, function(error){
                // Ошибка сервера
                processDeferred.reject('SERVER_ERROR');
                return error;
            });
        }
    });

    return new Ajax();

});