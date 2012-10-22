dojo.provide("core.layout.Processing");
require([
    "dojo/_base/lang",
    "dojo/_base/fx",
    "dojo/dom-style",
    "dojo/window"
    ], function(lang, fx, domStyle, window){
    core.layout.Processing = function(){};
    dojo.declare("core.layout.Processing", null, {
        _statuses: {
            SEND:           'Отправка данных',
            LOAD:           'Получение данных',
            PROCESSING:     'Обработка данных'
        },
        /**
         * Инициализация модуля
         * @param options
         */
        constructor: function(options){
            this._init = this._intersection({
                node:       null,   // Узел, который отображает процесс
                timeout:    10000   // Время автонмного завершения процесса
            }, options || {});
            if(!options.node) throw new Error('Не выбран узел node');
        },
        // TODO: заменить на dojo.lang::mixin
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
         * @param status
         * @return {*|Number}
         */
        show: function(status){
            var that = this;
            that._fillText(status);

            domStyle.set(this._init.node, {
                'width': window.getBox().w + 'px',
                'height': window.getBox().h + 'px' // Для абсолютного позиционирования. Для статического - можно указать смещение.
            });

            // Показать процесс
            setTimeout(function(){
                domStyle.set(that._init.node, {
                    'display': 'block',
                    'visibility': 'visible'
                });
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
            domStyle.set(this._init.node, {
                'display': 'none',
                'visibility': 'hidden'
            });
        },
        /**
         * Текст процесса
         * @param status
         * @private
         */
        _fillText: function(status){
            if(!this._statuses.hasOwnProperty(status)) {
                throw new Error('Статус сообщения неопределен2');
            }
            this._init.node.innerHTML = '<span id="processing-text">' + this._statuses[status] + '</span>';
            return this._init.node;
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