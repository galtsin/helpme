define([
    "dojo/_base/declare",
    "dojo/_base/lang",
    "dojo/request/xhr",
    "dojo/request",
    "dojo/request/notify",
    "dojo/on",
    "dojo/keys",
    "dojo/Deferred",
    "core/layout/Msg",
    "dojo/aspect",
    "core/helper/fn"
], function(declare, lang, xhr, request2, notify, on, keys, Deferred, Msg, aspect, HelperFn){

    var Ajax = declare(null, {
        // Время ожидания ответа. Глобальная переменная
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
            return this.request(url, undefinedOptions);
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
            return this.request(url, undefinedOptions);
        },
        request: function(url, options){
            lang.mixin(options, {
                timeout: this.timeout
            });

            if(url.indexOf('?') > -1) url += '&';
            else url += '?';

            switch(options.handleAs.toLowerCase()){
                case 'json':
                    url += 'format=json';
                    break;
                default:
                    url += 'format=html';
            }

            var processDeferred = new Deferred();
            var Messenger = this.Messenger;

            //Отображать все сообщения (ошибки и статусы процессов)
            //deferred.promise.always
            // Отображать только сообщения об ошибках
            processDeferred.promise.then(null, function(status){
                var message = Messenger.send(status);
                message.show();
            });

/*            request.response.then(function(response){
             console.log(response);
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
                // Состояние сервера
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
                    case 401:
                        processDeferred.reject('SERVER_UNAUTHORIZED');
                        break;
                    default:
                        processDeferred.reject('PROCESS_STATE_FAILED');
                }
            });

            return request;
        },
        /**
         * @param url
         * Example: domain.com/:param1/:param2
         * @param args
         * Example:
         * {param1: value, param2: value}
         * @return {String}
         */
        url: function(/*String*/url, /*Object*/args){
            if(args) {
                url = HelperFn.doPath(url, args);
            }
            return window.location.protocol + '//' + window.location.host + '/' + url;
        }
    });

    return new Ajax();

});