// Подсветка
dojo.provide("core.sandbox.helpers.Validate");
require(["dijit/Tooltip"], function(Tooltip){
    core.sandbox.helpers.Validate = function(){};
    dojo.declare("core.sandbox.helpers.Validate", null, {
        _highlightClass: "form-element-error",
        _systemErrorClass: "system-error",
        constructor: function(formNode){
            this._formNode = formNode;
            this._responseMessages = null;
            this._anchors = [];
            this._tooltip = new Tooltip();
        },
        clear: function(){
            // Очищаем подсветку ошибок
            for(var i = 0; i < this._formNode.elements.length; i ++) {
                this.highlightRemove(this._formNode.elements[i]);
            }
            this._removeAnchors();
            this._responseMessages = null;
        },
        setMessages: function(messages){
            this._responseMessages = messages;
            return this;
        },
        display: function(messages){
            if(messages !== undefined) {
                this.setMessages(messages);
            }
            if(null !== this._responseMessages){
                for(var i = 0; i < this._formNode.elements.length; i++){
                    if(this._responseMessages.hasOwnProperty(this._formNode.elements[i]['name'])) {
                        this.highlight(this._formNode.elements[i]);
                        var anchor =  this._addAnchor(this._formNode.elements[i]);
                        if(anchor !== undefined) {
                            this._addHandleTooltip(anchor);
                        }

                    }
                }
            }
        },
        highlight: function(el){
            if(!dojo.hasClass(el, this._highlightClass)) {
                dojo.addClass(el, this._highlightClass);
            }
        },
        highlightRemove: function(el) {
            if(dojo.hasClass(el, this._highlightClass)) {
                dojo.removeClass(el, this._highlightClass);
            }
            return this;
        },
        _addAnchor: function(el){
            var label = dojo.query("label[for=" + el.getAttribute('id') + "]", this._formNode)[0];
            if(label !== undefined) {
                var anchor = document.createElement("span");
                anchor.setAttribute("class", "error-anchor label label-important");
                var html = '';
                for(var errType in this._responseMessages[el['name']]) {
                    if (this._responseMessages[el['name']].hasOwnProperty(errType)) {
                        html += this._responseMessages[el['name']][errType];
                    }
                }
                anchor.innerHTML = '<span data-label="' + html + '">ошибка /?</span>';
                label.appendChild(anchor);
                this._anchors.push(anchor);
                return anchor;
            }
            return undefined;
        },
        _removeAnchors: function() {
            if(this._anchors.length > 0){
                for(var i = 0; i <= this._anchors.length; i ++) {
                    var anchor = this._anchors.pop();
                    anchor.parentNode.removeChild(anchor);
                }
            }
        },
        _addHandleTooltip: function(node){
            var that = this;
            dojo.connect(node, 'onclick', function(){
                that._tooltip.set('label', node.childNodes[0].getAttribute("data-label"));
                that._tooltip.open(node);
                dojo.connect(node, 'onmouseout', function(){
                    that._tooltip.close();
                })
            });
        }
    });
});