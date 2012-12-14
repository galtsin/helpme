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
                if(options.overlay) options.overlay.show();
            });

            var status = (options.method  == ('POST' || 'PUT' || 'DELETE')) ? 'PROCESS_SEND' : 'PROCESS_LOAD';
            var handler = this.Messenger.send(status);
            handler.show();
            clearTimeout(handler.clearTimeout);

            // Удалить Сообщение и Оверлей
            processDeferred.promise.always(function(){
                if(options.overlay) options.overlay.hide();
                setTimeout(function(){
                    handler.remove();
                }, 700);
            });

/*            // Версия с равномерным исчезновением сообщания о Загрузке или Передачи данных
            handler.changeState = function(){};
            var test = false;
            setTimeout(function(){
                // Событие отработало
                test = true;
                handler.changeState();
                setTimeout(function(){
                    //handler.remove();
                }, Ajax.Messenger.timeout - (Ajax.Messenger.duration + Ajax.Messenger.fadeDuration));
            }, Ajax.Messenger.duration + Ajax.Messenger.fadeDuration);
            handler.show();

            processDeferred.promise.always(function(){
                if(options.overlay) options.overlay.hide();
                if(test == true){
                    handler.remove();
                } else {
                    aspect.after(handler, 'changeState', function(){
                        //alert('change');
                        handler.remove();
                    });
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