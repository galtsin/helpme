dojo.provide("agreement.EditableSubscription");
require(["dijit/Template"], function(Tooltip){
    agreement.EditableSubscription = function(){};
    dojo.declare("agreement.EditableSubscription", [dijit._Widget, dijit._Templated], {
        constructor: function(formNode){
            this._formNode = formNode;
            this._responseMessages = {};
        },
        refresh: function(){},
        addToSubscription: function(args){},
        addUser: function(){},
        addInvitedGuest: function(){},
        deleteFromSubscription: function(){},
        save: function(){}
    });
});
