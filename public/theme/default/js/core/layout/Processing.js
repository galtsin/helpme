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

            // Типы процессов
            this.types = {
                SEND:       0,
                LOAD:       1,
                PROCESSING: 2,
                _values:     [
                    'Отправка данных',
                    'Получение данных',
                    'Обработка данных'
                ]
            };
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
         * @param code
         * @return {*|Number}
         */
        show: function(code){
            var that = this;
            that._fillText(code);

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
         * @param code
         * @private
         */
        _fillText: function(code){
            if('number' == typeof code && (0 <= code >= this.types._values.length)) {
                this._init.node.innerHTML = '<span id="processing-text">' + this.types._values[code] + '</span>';
            } else {
                throw new Error('Указан неверный тип процесса');
            }
            // TODO: доделать!!!!!!!
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