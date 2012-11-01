define([], function(){
    /**
     * Помошник формы
     */
    return {
        getSelectedOptions: function(selectbox){
            var result = [];
            var option = null;
            for(var i = 0, len = selectbox.options.length; i < len; i ++) {
                option = selectbox.options[i];
                if(option.selected) {
                    result.push(option);
                }
            }
            return result;
        },
        moveSelectedOptions: function(selectboxSource, selectboxRecipient){
            var options = this.getSelectedOptions(selectboxSource);
            for(var i = 0, len = options.length; i < len; i ++ ) {
                selectboxRecipient.appendChild(selectboxSource.removeChild(options[i]));
            }
        },
        checkboxValue: function(checkbox){
            var value = false;
            if(checkbox.type == "checkbox") {
                if(checkbox.checked) {
                    value = true;
                }
            } else {
                throw new Error('Неверный тип');
            }
            return value;
        }
    };
});