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
    $(document).on('change', '#in', function(){
        saveOut();
    });
    $(document).on('click','#save-translation',function(){
        saveOut();
        $.post('/admin/api/translation/save',$('#modal-editor form .editor').serialize(),function(data){
            var row=$('#translation tbody tr:eq('+$('#modal-editor').data('index')+')');
            $(row).find('.tbt').text(data['data']['tbt']);
            $(row).find('.out').text(data['data']['out']);
            $(row).find('.html').text(data['data']['html']);
            $('#translation').DataTable().draw();
            $('#modal-editor').modal('hide');
        });
    });
});
function saveOut(){
    switch($('#code:visible').val()){
        case 'markdown': // TODO
                        break;
        case 'html':
        case 'testo':
        default:        $('#out').val($('#in').val());
                        break;
    }
}
function changeCode(code){
    $("#in").markItUpRemove();
    switch(code){
        case 'html': $("#in").markItUp(myHtmlSettings); break;
        case 'markdown': $("#in").markItUp(myMarkdownSettings); break;
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
    var elem = $('#translation').DataTable().ajax.json()['data'][index];
    // console.log(elem);
    $('#modal-editor').data('index',index);
    $('#modal-editor').modal();
    $('#modal-editor form .editor[type!="checkbox"]:not("select")').each(function(){
        $(this).val(elem[$(this).attr('id')]);
    });
    $('#modal-editor form .editor[type="checkbox"]').each(function(){
        $(this).attr('checked',elem[$(this).attr('id')]);
    });
    if(elem['html'] === true){
        // debugger;
        $('#code option[value='+ elem['code'] +']').prop('selected',true);
        $('#code-group').removeClass('hidden');
        changeCode($('#code').val());
    }else{
        $("#in").markItUpRemove();
        $('#code-group').addClass('hidden');
    }
}
