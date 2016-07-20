$(document).ready(function(){
    if(typeof(customHandlers) === 'function'){
        customHandlers();
    }
    $(document).on('click','#cancel',function(event){
        event.preventDefault();
        window.location = window.location.origin
            + window.location.pathname.split("/",3).join("/");
    });
    $(document).on('click','#save',function(event){
        event.preventDefault();
        $('.has-error').removeClass('has-error');
        $('.has-error .text-danger').addClass('hidden').text('');
        if($('#remove').prop('checked')){
            if(confirm('Sei sicuro di voler cancellare questo elemento?')){
                console.log("Allora cancello");
                $('form').submit();
            }else{
                $('#remove').prop('checked',false);
                return;
            }
        }
        if(typeof(validate) === 'function'){
            var valid = validate();
            if(!valid.status){
                $.each(valid.errors,function(key, value){
                    console.log(key +": "+value);
                    $("#"+key).parents('.form-group').addClass('has-error');
                    $("#"+key).siblings('.text-danger')
                        .text(value)
                        .removeClass('hidden');
                });
            }else{
                if(typeof(preSubmit) === 'function'){
                    preSubmit();
                }
                $('form').submit();
            }
        }else{
            if(typeof(preSubmit) === 'function'){
                preSubmit();
            }
            $('form').submit();
        }
    });
});

