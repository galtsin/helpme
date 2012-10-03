dojo.provide("app.Model");
require([], function(){
    app.Model = function(){};
    dojo.declare("app.Model", null, {
        constructor: function() {
            this.index = [];
            this.entities = [];
        },
        getEntity: function(id){
            for(var i = 0; i < this.index.length; i ++) {
                if(id === this.index[i]){
                    return this.entities[i];
                }
            }
            var entity = this._createEntity(id);
            this.index.push(id);
            this.entities.push(entity);
            return entity;
        },
        //Делегирование
        _createEntity: function(id){
            return undefined;
        }
    });
});