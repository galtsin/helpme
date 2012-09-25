dojo.provide("agreement.subscription.Invites");
require(["dijit/Template"], function(Tooltip){
    agreement.subscription.Invites = function(){};
    dojo.declare("agreement.subscription.Invites", [dijit._Widget, dijit._Templated], {
        constructor: function(){
            this.domNode = '';
        },
        refresh: function(){

        },
        addToSubscription: function(args){},
        addUser: function(){},
        addInvitedGuest: function(){},
        deleteFromSubscription: function(){},
        save: function(){}
    });
});