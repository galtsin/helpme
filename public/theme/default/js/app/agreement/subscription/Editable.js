dojo.provide("agreement.subscription.Editable");
require(["dijit/Template"], function(Tooltip){
    agreement.subscription.Editable = function(){};
    dojo.declare("agreement.subscription.Editable", [dijit._Widget, dijit._Templated], {
        constructor: function(){},
        refresh: function(){},
        addToSubscription: function(args){},
        addUser: function(){},
        addInvitedGuest: function(){},
        deleteFromSubscription: function(){},
        save: function(){}
    });
});