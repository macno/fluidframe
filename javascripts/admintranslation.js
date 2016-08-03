$(document).ready(function(){
    var table=$("#translation").DataTable({
        ajax:'/admin/api/translation/datatable',
        rowId: 'rowId',
        responsive: true,
        processing: true,
        serverSide: true,
        columns: datacolumnstruct,
        columnDefs: [{
            targets: 4,
            data: 'out',
            render: function(data, type, full, meta){
                // visualizzo solo l'inizio dell'output senza tag html
                var html=data;
                return $('<div>'+html+'</div>').text().substring(0,20);
            }
        }]
    });
    changeSearch();
    $(document).on('click','#translation tbody tr',function(){
        openEditor($(this).index());
    });
    $(document).on('change', '#code:visible', function(){
        changeCode($(this).val());
    });
    $(document).on('click','.save-translation',function(){
        convertIn2Out($(this).data('lang'),save);
    });
});
function save(lang){
    $('.form-'+lang+' .out').removeClass('hidden');
    $.post('/admin/api/translation/save',$('#modal-editor .form-'+lang+' .editor').serialize(),function(data){
        $('.form-'+lang+' .out').addClass('hidden');
        var row=$('#translation tbody tr:eq('+$('#modal-editor').data('index')+')');
        $(row).find('.tbt').text(data['data']['tbt']);
        $(row).find('.out').text(data['data']['out']);
        $(row).find('.html').text(data['data']['html']);
        $('#translation').DataTable().draw();
        // $('#modal-editor').modal('hide');
    });
}
function convertIn2Out(lang, callback){
    var actuallang =$('#code:visible').val();
    switch(actuallang){
        case 'html':
                    $('.form-'+lang+' .out').val($('.form-'+lang+' .in').val());
                    callback(lang);
                    break;

        case 'testo':
        case 'markdown':
                    $.post('/admin/api/conversion',{ conversion: actuallang+'2html', in: $('.form-'+lang+' .in').val() }, function(data){
                        $('.form-'+lang+' .out').val(data['out']);
                        callback(lang);
                    });
                    break;
        default:
                    $('.form-'+lang+' .out').val($('.form-'+lang+' .in').val());
                    callback(lang);
                    break;
    }
}
function changeCode(code){
    var actual=$('#code').val(),
        prev=$('#code').data('prev'),
        conversion='';
    if(actual != prev) {
        $('#code').data('prev',actual);
        conversion = prev+"2"+actual;
        $.post('/admin/api/conversion',{ conversion: conversion, in: $('#in').val() }, function(data){
            $('#in').val(data['out']);
        });
    }
    $("textarea.in").markItUpRemove();
    switch(code){
        case 'testo': $("textarea.in").addClass('markItUpEditor'); break;
        case 'html': $("textarea.in").markItUp(myHtmlSettings); break;
        case 'markdown': $("textarea.in").markItUp(myMarkdownSettings); break;
    }
}
function changeSearch(){
    $('#translation_filter').remove();
    // Setup - add a search to each header cell
    $('#translation thead th').each( function () {
        var title = $(this).text();
        $(this).prepend( '<div>'+ datacolumnstruct[$(this).index()].search +'</div>' );
    } );
    // Popolo le select per la ricerca
    $.each(langs,function(i,v){
        $('#search_lang').append('<option value="'+v+'">'+v+'</option>')
    });
    $.each(contexts,function(i,v){
        $('#search_context').append('<option value="'+v+'">'+v+'</option>')
    });
    $.each([true, false],function(i,v){
        $('#search_html, #search_tbt').append('<option value="'+v+'">'+v+'</option>')
    })

    // DataTable
    var table = $('#translation').DataTable();

    // Apply the search
    table.columns().every( function () {
        var that = this;

        $( 'input, select', this.header() ).on( 'click', function (event) {
            event.stopPropagation();
        });
        $( 'input, select', this.header() ).on( 'keyup change', function () {
            if ( that.search() !== this.value ) {
                that
                    .search( this.value )
                    .draw();
            }
        } );
    } );
}
function openEditor(index){
    var row = $('#translation').DataTable().ajax.json()['data'][index];
    // console.log(row);
    $.get('/api/v1/translations',{context: row.context, key: row.key},function(data){
        console.log(data);
        $('#modal-editor form.generic .editor[type!="checkbox"]:not("select")').each(function(){
            $(this).val(data[$(this).attr('name')]);
        });
        if(data['html'] === true){
            $('#code option[value='+ data['code'] +']').prop('selected',true);
            $('#code').data('prev',data['code']);
            $('#code-group').removeClass('hidden');
            changeCode($('#code').val());
        }else{
            $('#code-group').addClass('hidden');
        }

        $("textarea.in").markItUpRemove();
        $('textarea.in').addClass('markItUpEditor');
        $('#modal-editor').data('index',index);
        $('#modal-editor').modal();
        $.each(data['langs'], function(lang){
            $('#modal-editor .form-'+lang+' .editor[type!="checkbox"]:not("select")').each(function(){
                $(this).val(data['langs'][lang][$(this).attr('name')]);
            });
        });
        // $('#modal-editor form .editor[type="checkbox"]').each(function(){
        //     $(this).attr('checked',data[$(this).attr('name')]);
        // });
    });
}
