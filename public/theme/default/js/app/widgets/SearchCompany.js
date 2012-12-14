define([
    "dojo/_base/declare", // declare
    "dijit/Dialog",
    "dojo/dom",
    "dojo/dom-construct",
    "dojo/_base/array",
    "dojo/_base/lang",
    "dojo/query",
    "core/layout/Overlay",
    "core/Ajax",
    "dojo/dom-style",
    "dojo/aspect",
    "dojo/on"
], function(declare, DialogBox, dom, domConstruct, array, lang, query, Overlay, Ajax, domStyle, aspect, on){

    var SearchCompany = declare([DialogBox], {
        Overlay: null,
        constructor: function(){
            var that = this;
            var onShowHandler = aspect.after(this, 'onShow', function(){
                that.Overlay = new Overlay({
                    domNode: this.containerNode
                 });
                // Взависимости от результата получения данных - текущая обработка
                // будет повторяться или нет
                this._getContent(onShowHandler);
            });
        },
        /**
         * Загрузить контент с сервера
         * @param onShowHandler
         * @private
         */
        _getContent: function(onShowHandler){
            var that = this;

            // Отразить заглушку на время загрузки данных
            domStyle.set(this.Overlay.overlayNode, {
                width: "200px",
                height: "100px"
            });

            var request = Ajax.load('http://192.168.1.51/manager/billing/search-company', {
                handleAs: 'html',
                overlay: this.Overlay,
                processing: false
            });

            request.then(function(response){
                setTimeout(function(){
                    that.set('content', response);
                    onShowHandler.remove();
                }, that.Overlay.delay);
            },function(){
                setTimeout(function(){
                    that.set('content', 'Неудалось загрузить контент');
                }, that.Overlay.delay);
            });

            // Событие по закрытию диалога во время загрузки контента
            var onHideHandler = aspect.after(this, 'hide', function(){
                onHideHandler.remove();
                if(!request.isFulfilled()){
                    request.cancel();
                    //Ajax.Messenger.send('PROCESS_STATE_ABORTED');
                }
            })
        }
    });

    return SearchCompany;
});