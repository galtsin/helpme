dojo.provide("core.sandbox.layout.Processing");
require(["dojo/_base/fx"], function(fx){
    core.sandbox.layout.Processing = function(){};
    dojo.declare("core.sandbox.layout.Processing", null, {
        constructor: function(options){
            var _options = options || {};
            this._node = _options.node;
            this._timeout = _options.timeout || 10000;
            if(!this._node) {
                throw new Error({message: 'Не указан узел'});
            }
            this._node.appendChild(this._fillText('обработка данных'));
        },
        // Показать процесс
        show: function(){
            var that = this;
            fx.fadeIn({node: this._node, duration: 500}).play();
            // Автономное отключение процесса
            return setTimeout(function(){
                that.hide();
            }, this._timeout);
        },
        // Скрыть процесс
        hide: function(){
            fx.fadeOut({node: this._node, duration: 500}).play();
        },
        // Описание процесса
        _fillText: function(text){
            var span = document.createElement('span');
            span.setAttribute('id', 'data-processing');
            span.innerHTML = text;
            return span;
        },
        // Управление процессом
        process: function(callback){
            // Открываем доступ к переменным текущего класса
            callback.call(this);
        }
    });
});