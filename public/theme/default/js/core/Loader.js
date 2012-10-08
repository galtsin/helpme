dojo.provide("core.Loader");
require([], function(){
    core.Loader = function(){};
    dojo.declare("core.Loader", null, {
        _delay: 400,
        _timeout: 5000,
        constructor: function(node){
            this._node = node;
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