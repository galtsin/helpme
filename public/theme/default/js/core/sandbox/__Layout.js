/* Менеджер слоев */
// this['method'].call(this, args);
// _default[param] =  || preferences.max_width || 500
dojo.provide("core.sandbox.Layout");
require([
    "core/resources/Ajax",
    "core/sandbox/layout/Loader",
    "core/sandbox/layout/Messenger"], function(Ajax, Loader, Messenger){
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
                method: 'POST',
                args: { format: 'json' },
                process: true
            };

            if(params.hasOwnProperty('args')) {
                if(!params.args.hasOwnProperty('format')){
                    params.args.format = _default.args.format;
                }
            }

            for(var param in params) {
                if(params.hasOwnProperty(param) && _default.hasOwnProperty(param)) {
                    _default[param] = params[param];
                }
            }

            return this._io(_default);
        },
        // Получение данных с сервера в различных форматах
        load: function(params){
            // Параметры по умолчанию
            var _default = {
                method: 'GET',
                args: { format: 'json' },
                process: true
            };

            if(params.hasOwnProperty('args')) {
                if(!params.args.hasOwnProperty('format')){
                    params.args.format = _default.args.format;
                }
            }

            for(var param in params) {
                if(params.hasOwnProperty(param) && _default.hasOwnProperty(param)) {
                    _default[param] = params[param];
                }
            }

            return this._io(_default);
        },
        _io: function(params){
            var that = this;
            switch (params.args.format) {
                case 'json':
                    params.handleAs = 'json';
                    break;
                case 'html':
                    params.handleAs = 'text';
                    break;
            }
            // Проверка URL
            if(false == params.hasOwnProperty('url')){
                throw new URIError({message: "Url адрес не указан"})
            }
            // Отобразить загрузчик
            if(true == params['process']){
                that.Loader.show();
            }
            // Получить Ajax запрос
            return this.Ajax.xhr({
                handleAs: params.handleAs,
                content: params.args,
                url: params.url,
                method: params.method.toUpperCase()
            }).addBoth(function(results){
                // Удалить загрузчик
                if(true == params['process']) { that.Loader.hide(); }
                // Показать системное сообщение
                results['result'] == -1 ? that.Messenger.send({message: "Ошибка на севере"}) : that.Messenger.send({message: "Данные успешно обработаны"});
                return results;
            });
        },
        // Вставить контент
        place: function(node, content){
            node.innerHTML = content;
        },
        // Очистить контейнер с контентом
        clear: function(node){
            while(node.firstChild) {
                node.removeChild(node.firstChild);
            }
            node.innerHTML = "";
        }
    });
});