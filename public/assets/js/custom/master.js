$(document).ready(function() {
    $.ajax({
        type: 'GET',
        url: APP_URL + '/get-notification',
        dataType: 'json',
        data: {

        },
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        success: function(results) {
            // localStorage.setItem('notif', results.data)
            if (results.message == 'ok') {
                var data = results.data
                var srisiko_korporasi = 0;
                var pengukuran_risiko = 0;
                var pengukuran_risiko_korporasi = 0;
                var mitigasi_korporasi = 0;
                var riskregister_divisi = 0;
                var hasil_mitigasi = 0;
                // var hasil_mitigasi_korporasi = 0;
                var deadline_mitigasi = 0;
                var mitigasi_risiko = 0;
                let element = '';
                for (let i = 0; i < data.length; i++) {
                    element += `<li>` +
                        `<a href='` + data[i].link + `'><p><i class="fa fa-circle-o me-3 font-info"></i>` + data[i].title + data[i].jumlah + `</p></a>` +
                        `</li>`;
                    if (data[i].title == 'Terdapat sumber risiko yang belum disetujui sebanyak ') {
                        srisiko_korporasi += data[i].jumlah;
                    }
                    if (data[i].title == 'Terdapat pengajuan mitigasi yang belum disetujui sebanyak ') {
                        mitigasi_korporasi += data[i].jumlah;
                    }
                    if (data[i].title == 'Terdapat risk register divisi yang belum disetujui sebanyak ') {
                        riskregister_divisi += data[i].jumlah;
                    }
                    if (data[i].title == 'Terdapat hasil mitigasi yang belum disetujui sebanyak ') {
                        hasil_mitigasi += data[i].jumlah;
                    }
                    // if (data[i].title == 'Terdapat mitigasi korporasi yang kurang dari 100% sebanyak ') {
                        // hasil_mitigasi_korporasi += data[i].jumlah;
                    // }
                    if (data[i].title == 'Terdapat risiko telah melewati tanggal jatuh tempo sebanyak ') {
                        deadline_mitigasi += data[i].jumlah;
                    }
                    if (data[i].title == 'Terdapat pengukuran risiko divisi sebanyak ') {
                        pengukuran_risiko += data[i].jumlah;
                    }
                    if (data[i].title == 'Terdapat pengukuran risiko korporasi sebanyak ') {
                        pengukuran_risiko_korporasi += data[i].jumlah;
                    }
                    if (data[i].title == 'Terdapat detail risiko yang belum dimitigasi sebanyak ') {
                        mitigasi_risiko += data[i].jumlah;
                    }

                }

                if (srisiko_korporasi > 0) {
                    $('.srisiko-korporasi-notif').html(srisiko_korporasi)
                } if (mitigasi_korporasi > 0) {
                    $('.mitigasi-korporasi-notif').html(mitigasi_korporasi)
                } if (riskregister_divisi > 0) {
                    $('.riskregister-divisi-notif').html(riskregister_divisi)
                } if (hasil_mitigasi > 0) {
                    $('.hasil-mitigasi-notif').html(hasil_mitigasi)
                } if (deadline_mitigasi > 0) {
                    $('.deadline-mitigasi-notif').html(deadline_mitigasi)
                } if (pengukuran_risiko > 0) {
                    $('.pengukuran-risiko-notif').html(pengukuran_risiko)
                } if (pengukuran_risiko_korporasi > 0) {
                    $('.pengukuran-risiko-korporasi-notif').html(pengukuran_risiko_korporasi)
                } if (mitigasi_risiko > 0) {
                    $('.mitigasi-risiko-notif').html(mitigasi_risiko)
                // } if (hasil_mitigasi_korporasi> 0) {
                    // $('.hasil-mitigasi-korporasi-notif').html(hasil_mitigasi_korporasi)
                }

                if (data.length > 0) {
                    $('.total-notif').html(data.length)
                    $('.body-notif').append(element)
                    $('.body-notif').append('<li></li>')
                }

            }
        },
        error: function(data) {
            console.log(data);
        }
    });

})
