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
            PROCESS_STATE_FAILED:   'Ошибка',
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
        timeout:    15000,   // Время автоновного завершения процесса и вывод сообщения об ошибке
        duration:   2000,    // Продолжительность показа сообщения
        fadeDuration: 700, // Время угасания/проявления сообщения
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

            var _Messenger = this;

            // Обработчик
            var handler = {
                // Автозапуск удаления
                clearTimeout: setTimeout(function(){
                    handler.remove();
                }, this.duration + _Messenger.fadeDuration),
                // Скрыть сообщение
                hide: function(){
                    return fx.fadeOut({
                        node:       container,
                        duration:   _Messenger.fadeDuration
                    }).play();
                },
                // Показать сообщение
                show: function(){
                    return  fx.fadeIn({
                        node:       container,
                        duration:   _Messenger.fadeDuration
                    }).play();
                },
                // Удалить сообщение
                remove: function(){
                    aspect.after(handler.hide(), "onEnd", function(){
                        handler.message.destroy(); // Уничтожить диджит
                        delete handler.show;
                        delete handler.hide;
                    });
                },
                // Сообщение ContentPane instance
                message: message
            };

            return handler;
        },
        /**
         * Заполнить текст сообщения
         * @param status
         * @param msg
         * @return {String}
         */
        fullText: function(status, msg){
            var html = '<p><span class="status">= ' + this.statuses[status] + ' =</span>';
            if(msg) html += '<span class="message">' + msg + '</span>';
            html += '</p>';
            return html;
        },
        // Синхронный процесс
        process: function(callback){
            var timeout;
            var deferred = new Deferred();
            var _Messenger = this;

            // TODO: Отображать все сообщения (ошибки и статусы процессов)
/*            deferred.promise.always(function(status){
                clearTimeout(timeout);
                _Messenger.send(status).show();
            });*/

            // TODO: Отображать только сообщения об ошибках. ЗАменить на предыдущий блок
            deferred.promise.then(function(){
                clearTimeout(timeout);
            }, function(status){
                _Messenger.send(status).show();
            });

            // Завершение процесса по истечении времени ожидания
            timeout = setTimeout(function(){
                // Если было уже инициировано Ошибка или Успешный ответ, то данные дальше не перейдут
                deferred.reject('PROCESS_STATE_TIMEOUT');
            }, this.timeout);

            // Возможность отменить Автозавершение процессы
            if(callback) callback(timeout);

            // Инициализация процесса resolve должна быть инициализирована извне
            return deferred;
        }
    });

    return Messenger;

});