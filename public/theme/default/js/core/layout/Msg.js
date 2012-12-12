define([
    "dojo/_base/declare",
    "dojo/dom",
    "dojo/dom-construct",
    "dojo/keys",
    "dojo/on",
    "dojo/Deferred",
    "dojo/_base/lang",
    "dojo/_base/fx",
    "dojo/dom-style",
    "dojo/window",
    "dojo/_base/window"
], function(declare, dom, domConstruct, keys, on, Deferred, lang, fx, domStyle, win, winBase){

    var Overlay = declare(null, {
        domNode: null,
        constructor: function(){
            this.domNode = domConstruct.create('div');
            domStyle.set(this.domNode, {
                'position':     'absolute',
                'top':          0,
                'opacity':      0.7,
                'background':   '#ffffff',
                'display':      'block',
                'z-index':      9000,
                'visibility':   'visible'
            });
        },
        show: function(){
            this._resize();
            domConstruct.place(this.domNode, winBase.body(), 'last');
        },
        _resize: function(){
            domStyle.set(this.domNode, {
                'width':      win.getBox().w + 'px',
                'height':     win.getBox().h + 'px'
            });
        },
        hide: function(){
            domConstruct.destroy(this.domNode);
        }
    });

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
        timeout:    15000,   // Время автоновного завершения отображения сообщения
        duration:   3000,    // Продолжительность сообщения
        overlay:    null,
        constructor: function(options){
            lang.mixin(this, options);
            this.overlay = new Overlay();
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

            // Прерывание процесса пользователем
            on(window, "keypress", function(event){
                if(event.keyCode == keys.ESCAPE) {
                    deferred.cancel('PROCESS_STATE_ABORTED');
                }
            });

            if(callback) callback(this);

            messenger.overlay.show();
            deferred.promise.always(function(status){
                clearTimeout(timeout);
                messenger.send(status);
                messenger.overlay.hide();
            });

            // Завершение процесса по истечении времени ожидания
            timeout = setTimeout(function(){
                // Если было уже инициировано Ошибка или Успешный ответ, то данные дальше не перейдут
                deferred.reject('PROCESS_STATE_TIMEOUT');
            }, this.timeout);

            // Инициализация процесса удачного завершения процесса должна быть инициализирована извне
            return deferred;
        }
    });

    return Messenger;

});