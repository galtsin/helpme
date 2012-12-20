/**
 * Переписать Deferred с Dojo >= 1.8
 */
dojo.provide("core.Layout");
require([
    "dojo/_base/lang",
    "core/layout/Messenger",
    "core/layout/Processing"
    ], function(lang, Messenger, Processing){
    core.Layout = function(){};
    dojo.declare("core.Layout", null, {
        _delay: 500,   // Системная задержка. Не изменяется. Для визуального отображения Процесса
        constructor: function(options) {
            this._init = this._intersection({
                timeout: 10000,
                messenger: {},
                processing: {}
            }, options);

            this._init.processing.timeout = this._init.timeout;
            try {
                this.Messenger = new Messenger(this._init.messenger);
                this.Processing = new Processing(this._init.processing);
            } catch (e){
                throw new Error('Не указаны обязательные параметры');
            }
        },
        /**
         * Отправить данные на сервер
         * @param options
         * @return {*}
         */
        send: function(options){
            return this._request(this._intersection({
                url:            '',
                method:         'POST',
                format:         'json',
                args:           {},
                process:        true,
                preventCache:   false
            }, options || {}));
        },
        /**
         * Загрузить информацию (контент или данные)
         * @param options
         * @return {*}
         */
        load: function(options){
            return this._request(this._intersection({
                url:            '',
                method:         'GET',
                format:         'json',
                args:           {},
                process:        true,
                preventCache:   true,
                node:           null
            }, options || {}));
        },
        // TODO: заменить на dojo.lang::mixin
        /**
         * Передать свойства источника, свойству приемника при условии совпадения свойств
         * @deprecated use dojo-lang-mixin
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
            // Инициализация
            var requestParams = {
                timeout:        this._init.timeout,
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

            var that = this;
            var deferred = new dojo.Deferred(/* Здесь можно указать функцию отмены */ function(){
                dojo.connect(window, "onkeypress", function(event){
                    if(event.keyCode == dojo.keys.ESCAPE) {
                        // TODO: проработать далее
                    }
                });
            });

            // Отобразить процесс обработки данных
            // Порядок действий определяется в deferred
            that.Processing.process(function(){
                var self = this;
                if(options.process){
                    var status = (options.method  == ('POST' || 'PUT' || 'DELETE')) ? 'SEND' : 'LOAD';
                    var handleTimeout = self.show(status);
                    // Первым делом убираем загрузчик
                    deferred.addBoth(function(response){
                        clearTimeout(handleTimeout);
                        self.hide();
                        return response;
                    });
                }

                // Обработать сообщения Приложения
                deferred.addCallback(function(response){
                    if(null !== response && 'object' == typeof response) {
                        if(response.result) {
                            switch (response.status) {
                                case 'ok':
                                    that.Messenger.send('PROCESS_OK');
                                    break;
                                case 'error':
                                    that.Messenger.send('PROCESS_FAILED');
                                    break;
                            }
                        }
                    }
                    return response;
                });

                // Обработать сообщения Сервера в случае ошибки при получении/отправки запроса
                deferred.addErrback(function(ioArgs){
                    that.Messenger.send('SERVER_ERROR');
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
            setTimeout(function(){
                dojo.xhr(options.method, requestParams);
            }, that._delay);

            return deferred; // TODO :или dojo.xhr(options.method, requestParams);
        },
        /**
         * Вставить контент внутрь узла
         * @deprecated see dojo-dom-construct
         * @param node
         * @param content
         */
        place: function(node, content){
            node.innerHTML = content;
        },
        /**
         * Очистить узел
         * @deprecated see dojo-dom-construct
         * @param node
         */
        clear: function(node){
            while(node.firstChild) {
                node.removeChild(node.firstChild);
            }
        },
        /**
         * Получить полный URL-адрес с привязкой к протоколу и доменному имени
         * @param url
         * @param obj
         * Если в url используются статические переменные, то подставляются параметры из объекта obj
         * domain/api/:name ({name: igor})
         */
        baseUrl: function(/* String */url, obj) {
            obj = obj || {};
            for(var param in obj){
                if(obj.hasOwnProperty(param)){
                    url = url.replace(':' + param, obj[param]);
                }
            }
            return window.location.protocol + '//' + window.location.host + '/' + url;
        },
        clearWithCaching: function(domNode){

        }
    });
});