dojo.provide("core.Layout");
require([
    "core/layout/Messenger",
    "core/layout/Processing"], function(Messenger, Processing){
    core.Layout = function(){};
    dojo.declare("core.Layout", null, {
        constructor: function(options) {
            var initialization = this._intersection({
                messenger: {},
                processing: {}
            }, options);

            this.Messenger = new Messenger(initialization.messenger);
            this.Processing = new Processing(initialization.processing);
        },
        /**
         * Отправить данные на сервер
         * @param options
         * @return {*}
         */
        send: function(options){
            return this._request(this._intersection({
                method:         'POST',
                format:         'json',
                args:           {},
                process:        true,
                preventCache:   false
            }, options));
        },
        /**
         * Загрузить информацию (контент или данные)
         * @param options
         * @return {*}
         */
        load: function(options){
            return this._request(this._intersection({
                method:         'GET',
                format:         'json',
                args:           {},
                process:        true,
                preventCache:   true,
                node:           null
            }, options));
        },
        /**
         * Передать свойства источника, свойству приемника при условии совпадения свойств
         * @param recipient
         * @param source
         * @return {*}
         * @private
         */
        _intersection: function(recipient, source){
            for(var option in source) {
                if(source.hasOwnProperty(option) && recipient.hasOwnProperty(option)) {
                    recipient[option] = source[option];
                }
            }
            return recipient;
        },
        /**
         *
         * @param options
         * @return {*}
         * @private
         */
        _request: function(options){

            // Проверка URL
            if(false == options.hasOwnProperty('url')){
                throw new URIError({message: "URL is undefined"})
            }

            // Инициализация
            var that = this;
            var requestParams = {
                timeout:        10000,
                content:        options.args || {},
                url:            options.url,
                preventCache:   options.preventCache || false
            };

            // Определить формат возвращаемых данных
            requestParams.content.format = options.format || 'html';
            switch (options.format) {
                case 'json': requestParams.handleAs = 'json';
                    break;
                case 'html': requestParams.handleAs = 'text';
                    break;
                default:
                    requestParams.handleAs = 'text';
            }


            var deferred = new dojo.Deferred(/* Здесь можно указать функцию отмены */ function(){
                dojo.connect(window, "onkeypress", function(event){
                    if(event.keyCode == dojo.keys.ESCAPE) {
                        // TODO: проработать далее
                    }
                });
            });

            // Отобразить процесс обработки данных
            that.Processing.process(function(){
                var self = this;
                if(options.process){
                    var showOptions = {timeout: requestParams.timeout};
                    if('POST' == options.method || 'PUT' || 'DELETE'){
                        showOptions.type = 1;
                    } else {
                        showOptions.type = 2;
                    }
                    var handleTimeout = self.show(showOptions);
                    deferred.addBoth(function(response){
                        clearTimeout(handleTimeout);
                        self.hide();
                        return response;
                    });
                }

                // Обработать сообщения Приложения
                deferred.addCallback(function(response){
                    if(response.result) {
                        switch (response.status) {
                            case 'ok':
                                that.Messenger.send({status: 'PROCESS_OK'});
                                break;
                            case 'failed':
                                that.Messenger.send({status: 'PROCESS_FAILED'});
                                break;
                        }
                    }
                    return response;
                });

                // Обработать сообщения Сервера
                deferred.addErrback(function(ioArgs){
                    that.Messenger.send({status: 'SERVER_ERROR'});
                    return ioArgs;
                });

                // Вставить контент, если указана нода
                if(options.node && requestParams.handleAs == 'text') {
                    deferred.addCallback(function(response){
                        that.place(options.node, response);
                        return response;
                    });
                }

            });

            // Выполнить внутреннюю операцию в случае успеха
            requestParams.load = function(response, ioArgs){
                deferred.callback(response);
                return response;
            };

            // Выполнить внутреннюю операцию в случае неудачи
            requestParams.error = function(response, ioArgs){
                deferred.errback(ioArgs);
                return response;
            };

            // Отправить запрос на сервер
            dojo.xhr(options.method, requestParams);
            // TODO :или dojo.xhr(options.method, requestParams);
            return deferred;
        },
        /**
         * Вставить контент внутрь узла
         * @param node
         * @param content
         */
        place: function(node, content){
            node.innerHTML = content;
        },
        /**
         * Очистить узел
         * @param node
         */
        clear: function(node){
            while(node.firstChild) {
                node.removeChild(node.firstChild);
            }
        }
    });
});