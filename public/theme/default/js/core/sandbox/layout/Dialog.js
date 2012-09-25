dojo.provide("core.sandbox.layout.Dialog");
require(["dijit/Dialog"], function(){
    core.sandbox.layout.Dialog = function(){};
    dojo.declare("core.sandbox.layout.Dialog", [dijit.Dialog], {

        hideWithSuccess: function(){
            var msg = "Запрос выполнен успешно";
            this.hideWithMessage("<div class='alert alert-success' style='width: 400px; margin: 0'>" + msg + "</div>");
        },

        hideWithError: function(){
            var msg = "В результате запроса возникли ошибки!";
            this.hideWithMessage("<div class='alert alert-error' style='width: 400px; margin: 0'>" + msg + "</div>");
        },

        hideWithMessage: function(msg){
            this.set("content", msg);
            var self = this;
            setTimeout(function(){self.hide();}, 1500);
        }
    });
});