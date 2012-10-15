/* Менеджер слоев */
dojo.provide("core.sandbox.Layout");
require(["core/resources/Ajax", "core/sandbox/layout/Dialog"], function(Ajax, Dialog){
    core.sandbox.Layout = function(){};
    dojo.declare("core.sandbox.Layout", null, {
        constructor: function(){
            this.container = [];
            this.Ajax = new Ajax();
            this.Dialog = new Dialog();
        },


        // Отправка данных на сервер (POST, DELETE, PUT)
        send: function(params){
            if(false == params.hasOwnProperty('url')){
                throw new URIError();
            }
            var _default = {
                method: "POST",
                args: {}
            };
            // Форматирование метода
            if(params.hasOwnProperty('method')){
                switch(params['method'].toUpperCase()){
                    case "POST": break;
                    case "DELETE": break;
                    case "PUT": break;
                    default: params['method'] = "POST"; break;
                }
            } else {
                params['method'] = "POST";
            }
            return this.Ajax.xhr(this.urlCorrect(params['url']), params['args'], params['method'], "json");
        },

        // Получение данных с сервера в различных форматах
        load: function(){},

        // Вставить контент внутрь ноды
        place: function(){},

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