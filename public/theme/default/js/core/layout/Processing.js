dojo.provide("core.layout.Processing");
require(["dojo/_base/fx"], function(fx){
    core.layout.Processing = function(){};
    dojo.declare("core.layout.Processing", null, {
        constructor: function(options){
            if(!options.node) throw new Error({message: 'Не указан узел'});
            this._node      = options.node; // Узел, который отображает процесс
            this.timeout    = options.timeout || 10000; // Время автоновного завершения процесса
            this.duration   = 0; // Продолжительность анимации проявления/исчезновения узла
        },
        _intersection: function(recipient, source){
            for(var option in source) {
                if(source.hasOwnProperty(option) && recipient.hasOwnProperty(option)) {
                    recipient[option] = source[option];
                }
            }
            return recipient;
        },
        // Отобразить процесс
        show: function(options){
            var that = this;
            var settings = this._intersection({
                type:       0
            }, options || {});

            // Получить
            switch(settings.type) {
                case 0:
                    settings.text = 'отправка данных';
                    break;
                case 1:
                    settings.text = 'получение данных';
                    break;
                default:
                    settings.text = 'обработка данных';
            }

            that._fillText(settings.text);
            setTimeout(function(){
                fx.fadeIn({node: that._node, duration: that.duration}).play();
            }, 0);

            // Автономное завершение процесса
            return setTimeout(function(){
                that.hide();
            }, that.timeout);
        },
        // Скрыть процесс
        hide: function(){
            fx.fadeOut({node: this._node, duration: this.duration}).play();
        },
        // Описание процесса
        _fillText: function(text){
            this._node.innerHTML = '<span id="processing-data">' + text + '</span>';
        },
        // Управление процессом
        process: function(callback){
            // Открываем доступ к переменным текущего класса
            callback.call(this);
        }
    });
});