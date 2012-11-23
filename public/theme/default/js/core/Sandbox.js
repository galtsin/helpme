// @deprecated
dojo.provide("core.Sandbox");
require(["core/sandbox/Base", "core/sandbox/Screen", "core/sandbox/Layout"], function(Base, Screen, Layout){
    core.Sandbox = function(){};
    dojo.declare("core.Sandbox", null, {
        constructor: function() {
            this.Base = new Base();
            this.Screen = new Screen();
            this.Layout = new Layout();
            this._stackManages = [];
            this._actions = {};
        },
        addStack: function(name){
            this._stackManages[name] = {
                _stack: [],
                push: function(value){
                    this._stack.push(value);
                },
                pop: function(){
                    return this._stack.pop();
                }
            };
        },
        getStack: function(name){
            return this._stackManages[name];
        },
        getAction: function(context, actionName) {
            return this._actions[context][actionName];
        },
        addAction: function(context, actionName, action){
            this._actions[context] = {};
            this._actions[context][actionName] = action;
        }
    });
});