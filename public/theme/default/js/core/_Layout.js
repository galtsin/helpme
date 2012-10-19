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
                method: 'POST',
                format: 'json',
                args: {},
                process: true,
                preventCache: false
            }, options));
        },
        /**
         * Загрузить информацию (контент или данные)
         * @param options
         * @return {*}
         */
        load: function(options){
            return this._request(this._intersection({
                method: 'GET',
                format: 'json',
                args: {},
                process: true,
                preventCache: true,
                node: null
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
                timeout: 10000,
                load: function(response, ioArgs){
                    setTimeout(function(){
                        if('POST' == options.method){
                            switch (response.status) {
                                case 'ok':
                                    that.Messenger.send({status: 'PROCESS_OK'});
                                    break;
                                case 'failed':
                                    that.Messenger.send({status: 'PROCESS_FAILED'});
                                    break;
                            }
                        }
                    }, 500);
                    return response;
                },
                error: function(response, ioArgs){
                    Messenger.send({status: 'SERVER_ERROR'});
                    return response;
                }
            };

            // Определить формат возвращаемых данных
            switch (options.format) {
                case 'json': requestParams.handleAs = 'json';
                    break;
                case 'html': requestParams.handleAs = 'text';
                    break;
                default:
                    requestParams.handleAs = 'text';
            }

            requestParams.content = options.args || {};
            requestParams.content.format = options.format || 'html';
            requestParams.url = options.url;
            requestParams.preventCache = options.preventCache || false;

            // Отправить запрос на сервер
            var response = dojo.xhr(options.method, requestParams);

            // Отобразить процесс обработки данных
            that.Processing.process(function(){
                var that = this;
                if(options.process){
                    var showOptions = {timeout: requestParams.timeout};
                    if('POST' == options.method || 'PUT' || 'DELETE'){
                        showOptions.type = 1;
                    } else {
                        showOptions.type = 2;
                    }
                    var handleTimeout = that.show(showOptions);
                    response.addBoth(function(results){
                        clearTimeout(handleTimeout);
                        that.hide();
                        return results;
                    });
                }
            });

            // Вставить контент, если указана нода
            if(options.node) {
                response.addCallback(function(results){
                    that.place(options.node, results);
                    return results;
                });
            }

            return response;
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