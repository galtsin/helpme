define([
    "dojo/_base/declare",
    "dojo/dom",
    "dojo/dom-construct",
    "dojo/dom-style",
    "dojo/dom-class",
    "dojo/keys",
    "dojo/on",
    "dojo/aspect",
    "dojo/Deferred",
    "dojo/_base/lang",
    "dojo/_base/fx",
    "dijit/layout/ContentPane"
], function(declare, dom, domConstruct, domStyle, domClass, keys, on, aspect, Deferred, lang, fx, ContentPane){

    var Messenger = declare(null, {
        statuses: {
            PROCESS_LOAD:           'Загрузка данных',
            PROCESS_SEND:           'Отправка данных',
            PROCESS_STATE_OK:       'Операция выполнена',
            PROCESS_STATE_FAILED:   'Произошла ошибка',
            PROCESS_STATE_WAITING:  'Идет обработка',
            PROCESS_STATE_ABORTED:  'Операция прервана',
            PROCESS_STATE_TIMEOUT:  'Превышено время ожидания',
            REST_STATE_OK:          '',
            SERVER_DISCONNECT:      'Не удалось получить ответ от сервера',
            SERVER_FORBIDDEN:       'Доступ запрещен',
            SERVER_ERROR:           'Ошибка на сервере',
            SERVER_NOT_FOUND:       'Ресурс не найден',
            SERVER_UNAUTHORIZED:    'Необходима авторизация пользователя'
        },
        domNode:    null,   //
        duration:   3000,    // Продолжительность показа экземпляра сообщения
        fadeDuration: 700, // Время угасания/проявления экземпляра сообщения
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
            var _Messenger = this;
            var container = domConstruct.create('div');
            domStyle.set(container, {
                opacity: 0
            });
            domConstruct.place(container, this.domNode, 'first');
            var message = new ContentPane({
                content: this.fullText(status, msg),
                style: {padding: '0px', margin: '0px'}
            }, container);

            message.startup();

            // Обработчик
            var messageHandler = {
                // Автоудаление сообщения
                clearTimeout: setTimeout(function(){
                    messageHandler.remove();
                }, this.duration + _Messenger.fadeDuration),
                // Показать сообщение
                show: function(){
                    fx.fadeIn({
                        node:       container,
                        duration:   _Messenger.fadeDuration
                    }).play();
                },
                // Удалить сообщение
                remove: function(){
                    var animation = fx.fadeOut({
                        node:       container,
                        duration:   _Messenger.fadeDuration,
                        onEnd: function(){
                            messageHandler.message.destroy(); // Уничтожить диджит
                        }
                    });
                    animation.play();
                },
                message: /* ContentPane */message
            };

            on(messageHandler.message.domNode, 'button:click', function(event){
                event.preventDefault();
                messageHandler.remove();
            });

            return messageHandler;
        },
        /**
         * Заполнить текст сообщения
         * @param status
         * @param msg
         * @return {String}
         */
        fullText: function(status, msg){
            var html = '<p style="position: relative">' +
                '<span style="position: absolute; top: 5px; right: 5px;"><button type="button" class="close">×</button></span>' +
                '<span class="status">= ' + this.statuses[status] + ' =</span>';
            if(msg) html += '<span class="message">' + msg + '</span>';
            html += '</p>';
            return html;
        }
    });

    return Messenger;

});