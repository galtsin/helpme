// Подсветка
dojo.provide("core.sandbox.Validate");
require(["dijit/Tooltip"], function(Tooltip){
    core.sandbox.Validate = function(){};
    dojo.declare("core.sandbox.Validate", null, {
        _elements: [],
        _handles: [],
        _hightlightClass: "form-element-error",
        _systemErrorClass: "system-error",
        constructor: function(formNode){
            this._formNode = formNode;
            this._responseMessages = {};
        },
        /* Очистить предыдущие сообщения об ощибках */
        clear: function(){
            // Очищаем подсветку ошибок
            for(var i = 0; i < this._formNode.elements.length; i ++) {
                this._hightlightRemove(this._formNode.elements[i]);
            }
            // Очищаем якоря
            dojo.query(".error-anchor").orphan();
        },
        setMessages: function(messages){
            this._responseMessages = messages;
            return this;
        },
        displayErrors: function(){
            this.clear();
            this._setMessagesForElements();
        },
        _getElementByName: function(elementName) {
            // TODO: В IE не работает.
            /*            if(this._formNode.propertyIsEnumerable(elementName)) {
             return this._formNode[elementName];
             }
             return undefined;*/
            // Старая версия
            return this._formNode[elementName];
        },
        _setMessagesForElements: function(){
            for(var elementName in this._responseMessages) {
                this._setMessagesForElement(this._getElementByName(elementName));
            }
        },
        _setMessagesForElement: function(el){
            if(el && el.type !== "hidden") {
                this._hightlight(el);
                this._helpAnchor(el);
            }
        },
        _setMessagesForTooltip: function(el){
            var template = '<ul>';
            for(var stringCodeMsg in this._responseMessages[el.name]) {
                template += '<li>' + this._responseMessages[el.name][stringCodeMsg] + '</li>';
            }
            return template;
        },
        _helpAnchor: function(el){
            var label = dojo.query("label[for=" + dojo.attr(el, "id") + "]", this._formNode)[0];
            if(label !== undefined) {
                var anchor = document.createElement("span");
                anchor.setAttribute("class", "error-anchor label label-important");
                anchor.innerHTML = "<span>ошибка /?</span>";
                label.appendChild(anchor);
                this._handleTooltip(anchor, this._setMessagesForTooltip(el));
            }
        },
        _hightlight: function(el){
            if(!dojo.hasClass(el, this._hightlightClass)) {
                dojo.addClass(el, this._hightlightClass);
            }
            return this;
        },
        _hightlightRemove: function(el) {
            if(dojo.hasClass(el, this._hightlightClass)) {
                dojo.removeClass(el, this._hightlightClass);
            }
            return this;
        },
        _handleTooltip: function(el, content){
            new Tooltip({
                connectId: el,
                label: content,
                showDelay: 0
            });
        }
    });
});