/**
 * @dwprecated
 */
dojo.provide("core.layout.Messenger");
require([
    "dojo/_base/lang",
    "dojo/_base/fx"
    ], function(lang, fx){
    core.layout.Messenger = function(){};
    dojo.declare("core.layout.Messenger", null, {
        _statuses: {
            PROCESS_LOAD:           'Загрузка данных',
            PROCESS_SEND:           'Отправка данных',
            PROCESSING:             'Обработка данных',
            PROCESS_STATE_OK:       '',
            PROCESS_STATE_FAILED:   '',
            PROCESS_STATE_WAITING:  '',
            PROCESS_OK:             'Операция выполнена успешно',
            PROCESS_FAILED:         'Операция не выполнена',
            SERVER_DISCONNECT:      'Не удалось получить ответ от сервера',
            SERVER_ERROR:           'Ошибка на сервере'
        },
        /**
         * Инициализация модуля
         * @param options
         */
        constructor: function(options){
            this._init = this._intersection({
                node:       null,   // Узел, который отображает процесс
                timeout:    3000,   // Время автоновного завершения процесса
                duration:   500,    // Продолжительность анимации проявления/исчезновения узла
                delay:      500     // Задержка проявления сообщения
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
         * Отобразить сообщение
         * @param code
         * @return {*|Number}
         */
        send: function(code){
            var that = this;
            that._fillText(code);

            // Показать процесс
            setTimeout(function(){
                fx.fadeIn({
                    node:       that._init.node,
                    duration:   that._init.duration,
                    delay:      that._init.delay
                }).play();
            }, 0);

            // Автономное завершение процесса
            return setTimeout(function(){
                that.clear();
            }, that._init.timeout);
        },
        /**
         * Очистить сообщения
         */
        clear: function() {
            fx.fadeOut({
                node:       this._init.node,
                duration:   this._init.duration,
                delay:      this._init.delay
            }).play();
        },
        /**
         * Текст процесса
         * @param status
         * @return {*}
         * @private
         */
        _fillText: function(status){
            if(!this._statuses.hasOwnProperty(status)) {
                throw new Error('Статус сообщения неопределен');
            }
            this._init.node.innerHTML = '<p><span id="messenger-text">' + this._statuses[status] + '</span></p>';
            return this._init.node;
        }
    });
});