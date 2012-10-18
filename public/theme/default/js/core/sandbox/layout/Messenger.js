dojo.provide("core.sandbox.layout.Messenger");
require([], function(){
    core.sandbox.layout.Messenger = function(){};
    dojo.declare("core.sandbox.layout.Messenger", null, {
        _delay: 2000,
        _timeout: 5000,
        _message: null,
        constructor: function(node){
            this._node = node;
        },
        send: function(args){
            if(null !== args && "object" == typeof args) {
                var that = this;
                this._node.innerHTML = this._getMessage(args.code);
                if(this._delay > 0) {
                    setTimeout(function(){
                        that.clear();
                    }, this._delay);
                }
            }
            var anim = dojo.fadeIn({node: this._node, duration: 500});
            anim.play();
            //dojo.removeClass(this._node, "hidden");
        },
        clear: function() {
            var self = this;
            var anim = dojo.fadeOut({node: self._node, duration: 500});
            anim.play();
            //setTimeout(function(){dojo.addClass(self._node, "hidden");}, self._delay);
        },
        _getMessage: function(code){
            var message = '';
/*            switch (code) {
                case '101':
            }*/
            return "<span>Данные успешно обновлены</span>";
        },
        _fillText: function(status){
            var text = '';
            switch(status){
                case 'ok':
                    text = "Операция выполнена успешно";
                    break;
                case 'failed':
                    text = "Во время выполнения произошла ошибка";
                    break;
                case 'disconnect':
                    text = "Отсутствует соединение с сервером";
                    break;
                default:
                    text = "Статус сообщения неопределен";
            }
            return text;
        }
    });
});