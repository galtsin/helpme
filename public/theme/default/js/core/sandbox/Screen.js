/* Менеджер Экранов */
dojo.provide("core.sandbox.Screen");
require(["dojo/hash", "dojo/store/Memory", "core/sandbox/screen/Breadcrumb"], function(Hash, Memory, Breadcrumb){
    core.sandbox.Screen = function(){};
    dojo.declare("core.sandbox.Screen", null, {
        constructor: function(){
            this._screen = new Memory({data:[]});
            this.Breadcrumb = new Breadcrumb(this);
            this._screensIndex = [];
            this._screens = [];
            return this;
        },
        _getScreenIndex: function(identity){
            for(var i = 0; i < this._screensIndex.length; i ++) {
                if(identity === this._screensIndex[i]){
                    return i;
                }
            }
            return undefined;
        },
        add: function(identity, func){
            if(this._getScreenIndex(identity) === undefined){
                this._screens.push(func);
                this._screensIndex.push(identity);
            } else {
                this._screens[this._getScreenIndex(identity)] = func;
            }
        },
        invoke: function(identity, hash, callback){
            //if(null !== hash){
                dojo.hash(hash);
            //}
            callback(this._screens[this._getScreenIndex(identity)]);
        },
        // #!/lineBoars/12
        parseHash: function(hash){
            // Удаление #
            var position = hash.indexOf('#');
            var parts = (position == -1) ? hash.split('/') : hash.slice(position + 1).split('/');
            return {
                prefix: parts.shift(),
                parts: parts
            }
        },
        hash: function(hash){

        }
    });
});