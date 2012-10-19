dojo.provide("core.sandbox.layout.Processing");
require(["dojo/_base/fx"], function(fx){
    core.sandbox.layout.Processing = function(){};
    dojo.declare("core.sandbox.layout.Processing", null, {
        constructor: function(options){
            if(!options.node) {
                throw new Error({message: 'Не указан узел'});
            }
            this._node = options.node;
        },
        _intersection: function(recipient, source){
            for(var option in source) {
                if(source.hasOwnProperty(option) && recipient.hasOwnProperty(option)) {
                    recipient[option] = source[option];
                }
            }
            return recipient;
        },
        // Показать процесс
        show: function(options){
            var that = this;
            var settings = this._intersection({
                timeout:    10000,
                duration:   500,
                type:       0
            }, options || {});

            // Получить
            switch(settings.type) {
                case 0:
                    settings.text = 'обработка данных';
                    break;
                case 1:
                    settings.text = 'отправка данных';
                    break;
                case 3:
                    settings.text = 'получение данных';
                    break;
            }

            this._node.appendChild(this._fillText(settings.text));
            setTimeout(function(){
                fx.fadeIn({node: this._node, duration: settings.duration}).play();
            }, 0);

            // Автономное завершение процесса
            return setTimeout(function(){
                that.hide();
            }, settings.timeout);
        },
        // Скрыть процесс
        hide: function(options){
            var settings = this._intersection({
                timeout: 10000,
                duration: 500
            }, options || {});
            fx.fadeOut({node: this._node, duration: settings.duration}).play();
        },
        // Описание процесса
        _fillText: function(text){
            var span = document.createElement('span');
            span.setAttribute('id', 'data-processing');
            span.innerHTML = text;
            return span;
        },
        // Управление процессом
        process: function(callback){
            // Открываем доступ к переменным текущего класса
            callback.call(this);
        }
    });
});