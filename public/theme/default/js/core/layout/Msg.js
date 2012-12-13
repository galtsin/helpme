define([
    "dojo/_base/declare",
    "dojo/dom",
    "dojo/dom-construct",
    "dojo/dom-class",
    "dojo/keys",
    "dojo/on",
    "dojo/Deferred",
    "dojo/_base/lang",
    "dojo/_base/fx"
], function(declare, dom, domConstruct, domClass, keys, on, Deferred, lang, fx){

    var Messenger = declare(null, {
        statuses: {
            PROCESS_LOAD:           'Загрузка данных',
            PROCESS_SEND:           'Отправка данных',
            PROCESS_STATE_OK:       'Выполнено',
            PROCESS_STATE_FAILED:   'Ошибка',
            PROCESS_STATE_WAITING:  'Обработка',
            PROCESS_STATE_ABORTED:  'Операция прервана',
            PROCESS_STATE_TIMEOUT:  'Превышено время ожидания',
            SERVER_DISCONNECT:      'Не удалось получить ответ от сервера',
            SERVER_ERROR:           'Ошибка на сервере'
        },
        domNode:    null,   //
        timeout:    15000,   // Время автоновного завершения процесса и вывод сообщения об ошибке
        duration:   3000,    // Продолжительность сообщения
        constructor: function(options){
            lang.mixin(this, options);
        },
        /**
         * Отправка сообщения
         * @param status Символьный статус сообщения
         * @param msg Пользовательское сообщение
         * @return {*|Number}
         */
        send: function(status, msg){
            setTimeout(function(){

            }, 800);
            domConstruct.place(this._fullText(status, msg), this.domNode, 'first');
            fx.fadeIn({
                node:       this.domNode,
                duration:   600
            }).play();

            var messenger = this;
            // Автоматическая очистка сообщений
            return setTimeout(function(){
                messenger.clear();
            }, this.duration);
        },
        /**
         * Очистить сообщение
         */
        clear: function(){
            fx.fadeOut({
                node:       this.domNode,
                duration:   600
            }).play();

            var messenger = this;
            setTimeout(function(){
                domConstruct.empty(messenger.domNode);
            }, 600 + 400);
        },
        /**
         * Заполнить текст сообщения
         * @param status
         * @param msg
         * @return {String}
         * @private
         */
        _fullText: function(status, msg){
            var html = '<p>' +
                '<span class="status">' + this.statuses[status] + '</span>';
            if(msg) {
                html += '<span class="text">' + msg + '</span>';
            }
            html += '</p>';
            return html;
        },
        // Синхронный процесс
        process: function(callback){
            var timeout;
            var messenger = this;
            var deferred = new Deferred();

            if(callback) callback(this);

            deferred.promise.always(function(status){
                clearTimeout(timeout);
                messenger.send(status);
            });

            // Завершение процесса по истечении времени ожидания
            timeout = setTimeout(function(){
                // Если было уже инициировано Ошибка или Успешный ответ, то данные дальше не перейдут
                deferred.reject('PROCESS_STATE_TIMEOUT');
            }, this.timeout);

            // Инициализация процесса resolve должна быть инициализирована извне
            return deferred;
        }
    });

    return Messenger;

});