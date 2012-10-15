/* Менеджер слоев */
dojo.provide("core.sandbox.Layout");
require(["core/resources/Ajax", "core/sandbox/layout/Loader", "core/sandbox/layout/Messenger"],
    function(Ajax, Loader, Messenger){
    core.sandbox.Layout = function(){};
    dojo.declare("core.sandbox.Layout", null, {
        constructor: function(){
            this.Ajax = new Ajax();
            this.Loader = new Loader();
            this.Messenger = new Messenger();
        },
        // Отправка данных на сервер (POST, DELETE, PUT)
        send: function(params){
            // Параметры по умолчанию
            var _default = {
                method: "POST",
                args: {},
                process: true,
                handleAs: "json",
                handle: function(results){
                    if(results['result'] == -1) {
                        if(true == this.process) {

                        }
                        that.Messenger.show();
                    }
                }
            };

            if(false == params.hasOwnProperty('url')){
                throw new URIError({message: "Url адрес не указан"})
            }

            for(var param in params) {
                if(params.hasOwnProperty(param) && _default.hasOwnProperty(param)) {
                    _default[param] = params[param];
                }
            }

            var that = this;
            //this['method'].call(this, args);
            // _default[param] =  || preferences.max_width || 500

            if(true == _default['process']){
                that.Loader.show();
            }

            var call = this.Ajax.xhr(_default);

            call.addCallback(function(results){
                // Удалить загрузчик
                if(true == _default['process']) {
                    that.Loader.hide();
                }
                if(results['result'] == -1) {

                }

            });

            return call;
        },

        // Получение данных с сервера в различных форматах
        load: function(params){},

        // Вставить контент внутрь ноды
        place: function(node, content){},

        _initMethod: function(){},

        // @Deprecated Отправить данные на сервер.
        dataSender: function(url, data, method){
            if(null === data || "object" !== typeof data) {
                data = {}
            }
            data['format'] = "json";
            switch(method){
                case "POST": break;
                case "DELETE": break;
                default: method = "POST"; break;
            }
            return this.Ajax.xhr(this.urlCorrect(url), data, method, "json");
        },
        // Загрузить данные с сервера JSON-данные
        dataLoader: function(url, params){
            params.format = 'json';
            return this.Ajax.xhr(this.urlCorrect(url), params, "GET", "json");
        },
        // Загрузить удаленный контент HTML-данные
        contentLoader: function(url, params){
            params.format = 'html';
            return this.Ajax.xhr(this.urlCorrect(url), params, "GET", "text");
        },
        // Очистить контейнер с контентом
        clear: function(node){
            while(node.firstChild) {
                node.removeChild(node.firstChild);
            }
            node.innerHTML = "";
        },
        // Добавить контент в контейнер
        placeContent: function(node, content) {
            node.innerHTML = content;
        },
        urlCorrect: function(url) {
            return appConfig.baseUrl + '/' + url;
        },
        // Распарсить короткую Ajax ссылку вида: '#!/{action}/{arg}/{arg} ... '
        parseShortLink: function(hash){
            var obj = {};
            var parts = hash.split('/');
            if(hash.indexOf('#') != -1) {
                obj['prefix'] = parts.shift().slice(1);
            }
            obj['action'] = parts.shift();
            obj['args'] = parts;
            return obj;
        }
    });
});