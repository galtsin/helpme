dojo.provide("core.layout.Processing");
require(["dojo/_base/fx"], function(fx){
    core.layout.Processing = function(){};
    dojo.declare("core.layout.Processing", null, {
        constructor: function(options){
            this._init = this._intersection({
                node:       null,   // Узел, который отображает процесс
                timeout:    10000,  // Время автоновного завершения процесса
                duration:   100     // Продолжительность анимации проявления/исчезновения узла
            }, options || {});
            if(!options.node) throw new Error('Не указан узел node для отображения процессов');
        },
        _intersection: function(recipient, source){
            for(var option in source) {
                if(source.hasOwnProperty(option) && recipient.hasOwnProperty(option)) {
                    recipient[option] = source[option];
                }
            }
            return recipient;
        },
        /**
         * Отобразить процесс
         * @param type
         * @return {*|Number}
         */
        show: function(type){
            var that = this;
            switch(type) {
                case 0:
                    that._fillText('Отправка данных');
                    break;
                case 1:
                    that._fillText('Получение данных');
                    break;
                default:
                    that._fillText('Обработка данных');
            }

            // Показать процесс
            setTimeout(function(){
                fx.fadeIn({
                    node:       that._init.node,
                    duration:   that._init.duration
                }).play();
            }, 0);

            // Автономное завершение процесса
            return setTimeout(function(){
                that.hide();
            }, that._init.timeout);
        },
        /**
         * Скрыть процесс
         */
        hide: function(){
            fx.fadeOut({
                node:       this._init.node,
                duration:   this._init.duration
            }).play();
        },
        /**
         * Текст процесса
         * @param text
         * @private
         */
        _fillText: function(text){
            this._init.node.innerHTML = '<span id="processing-text">' + text + '</span>';
        },
        /**
         * Управление процессом
         * @param callback
         */
        process: function(callback){
            callback.call(this); // Открываем доступ к переменным текущего класса
        }
    });
});