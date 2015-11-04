$(document).ready(function(){
    $("#translation").DataTable({
        ajax:'/admin/api/translation/datatable',
        processing: true,
        serverSide: true,
        columns: datacolumnstruct
    });
    changeSearch();
});
function changeSearch(){
    $('#translation_filter').remove();
    // Setup - add a search to each header cell
    $('#translation thead th').each( function () {
        var title = $(this).text();
        $(this).prepend( '<div>'+ datacolumnstruct[$(this).index()].search +'</div>' ).on('click',function(e){e.stopImmediatePropagation();});
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

        $( 'input, select', this.header() ).on( 'keyup change', function () {
            if ( that.search() !== this.value ) {
                that
                    .search( this.value )
                    .draw();
            }
        } );
    } );
}

