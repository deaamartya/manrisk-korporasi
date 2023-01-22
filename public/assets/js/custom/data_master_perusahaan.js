$(document).ready(function(){
    $('#tambah_divisi').on('click', function(){
        $('#judul_modal').html('Tambah Divisi')
        $('#formDivisi').attr('action', APP_URL+'/admin/divisi/store')
        var elements = document.getElementsByTagName("input");
        for (var i=0; i < elements.length; i++) {
            if (elements[i].type == "text") {
                elements[i].value = "";
            }
        }

        $('#tambahDivisi').modal('show')
    })

    $('#basic-1 tbody').on('click', '.edit', function(){
        let id = $(this).attr('id').slice(5)
        $('#judul_modal').html('Edit Divisi')
        $('#formDivisi').attr('action', APP_URL+'/admin/divisi/store/'+id)
        var elements = document.getElementsByTagName("input");
        for (var i=0; i < elements.length; i++) {
            if (elements[i].type == "text") {
                elements[i].value = "";
            }
        }
        $.ajax({
            type: 'GET',
            url: APP_URL+'/admin/divisi/get-divisi/'+id,
            dataType: 'json',
            data: {},
            success: function (results) {
                $('#formDivisiCode').val(results.divisi_code)
                $('#formInstansi').val(results.instansi)

            },
            error:function(results){
                console.log(results);
            }
        });

        $('#tambahDivisi').modal('show')
    })

    $('#basic-1 tbody').on('click', '.delete', function(){
        let id = $(this).attr('id').slice(7)

        $.ajax({
            type: 'GET',
            url: APP_URL+'/admin/divisi/get-divisi/'+id,
            dataType: 'json',
            data: {},
            success: function (results) {
                $('#id_divisi').val(results.divisi_id)
                $('#nama_divisi').html(results.instansi)

                $('#deleteDivisi').modal('show')
            },
            error:function(results){
                console.log(results);
            }
        });
    })
})
