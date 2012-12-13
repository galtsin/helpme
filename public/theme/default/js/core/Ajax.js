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
                processing:     true,
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

            var Ajax = this;

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

            var processDeferred = this.Messenger.process(function(){
                if(options.overlay){
                    options.overlay.show();
                }
                var status = (options.method  == ('POST' || 'PUT' || 'DELETE')) ? 'PROCESS_SEND' : 'PROCESS_LOAD';
                var handler = Ajax.Messenger.send(status);
                clearTimeout(handler); // TODO: зачем?
            });

            // Прерывание процесса пользователем
/*            on(window, "keypress", function(event){
                 if(event.keyCode == keys.ESCAPE) {
                     processDeferred.cancel('PROCESS_STATE_ABORTED');
                 }
             });*/

            if(options.overlay){
                // Отключить оверлей
                processDeferred.promise.always(function(){
                    options.overlay.hide();
                });
            }

            var request =  xhr(url, options);
            request.then(function(response){
                if(response && 'object' == typeof response) {
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
                processDeferred.reject('PROCESS_STATE_FAILED');
            });

            return request;
        }
    });

    return new Ajax();

});