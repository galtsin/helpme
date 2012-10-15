dojo.provide("core.sandbox.layout.Loader");
require([], function(){
    core.sandbox.layout.Loader = function(){};
    dojo.declare("core.sandbox.layout.Loader", null, {
        constructor: function(node){
            this._node = node;
            this._delay = 400;
        },
        show: function(){
            this._resize();
            dojo.removeClass(this._node, "hidden");
        },
        hide: function() {
            var self = this;
            setTimeout(function(){dojo.addClass(self._node, "hidden");}, self._delay);
        },
        hasHide: function() {
            return dojo.hasClass(this._node, "hidden");
        },
        _resize: function(){
            dojo.style(this._node, 'width', dojo.window.getBox().w + 'px');
            // Для абсолютного позиционирования. Для статического - можно указать смещение.
            dojo.style(this._node, 'height', (dojo.window.getBox().h) + 'px');
        }
    });
});