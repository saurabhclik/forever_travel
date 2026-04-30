            <div class="footer">
                <div class="copyright">
                    <a> Project Evaluation</a>
                </div>
            </div>
            </div>
            <?php
            if (isset($_SESSION['alert'])) 
            {
                $alert = $_SESSION['alert'];
                echo "
                <script>
                Swal.fire({
                    title: '{$alert['title']}',
                    text: '{$alert['text']}',
                    icon: '{$alert['icon']}',
                    confirmButtonText: 'OK'
                });
                </script>
                ";
                unset($_SESSION['alert']); 
            }
        ?>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
            <script src="vendor/global/global.min.js"></script>
            <script src="vendor/chart.js/Chart.bundle.min.js"></script>
            <script src="vendor/jquery-nice-select/js/jquery.nice-select.min.js"></script>
            <script src="vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
            <script src="vendor/datepicker/js/bootstrap-datepicker.min.js"></script>
            <script src="vendor/owl-carousel/owl.carousel.js"></script>
            <!-- <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0/select2.min.css" rel="stylesheet" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0/select2.min.js"></script> -->
            <!-- <script src="vendor/datatables/js/jquery.dataTables.min.js"></script> -->
            <!-- <script src="js/plugins-init/datatables.init.js"></script> -->
            <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
            <script src="vendor/datatables/js/jquery.dataTables.min.js"></script>
            <script src="js/plugins-init/datatables.init.js"></script>
            <script src="vendor/swiper/js/swiper-bundle.min.js"></script>
            <script src="vendor/apexchart/apexchart.js"></script>
            <script src="vendor/peity/jquery.peity.min.js"></script>
            <script src="js/dashboard/dashboard-1.js"></script>
            <script src="js/custom.min.js"></script>
            <script src="js/deznav-init.js"></script>
            <!-- <script src="js/demo.js"></script>
            <script src="js/styleSwitcher.js"></script> -->
            <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet" />
            <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
            <script>
//old datatable function -----------------------------------------
// $(function() 
// {
//     $(".dataTable").DataTable({
//     "responsive": true,
//     "lengthChange": true,
//     "autoWidth": false,
//     "buttons": ["copy", "csv", "excel", "pdf", "colvis", {
//         extend: 'print',
//         exportOptions: {
//         columns: ':visible'
//         }
//     }, ]
//     }).buttons().container().appendTo('.col-md-6:eq(0)');
// });

(function() {
    'use strict'
    var forms = document.querySelectorAll('.needs-validation')
    Array.prototype.slice.call(forms)
        .forEach(function(form) {
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }
                form.classList.add('was-validated')
            }, false)
        })
})()

$("#state").on("change", function() {
    $.ajax({
        method: "POST",
        url: "ajax/get-city.php",
        data: {
            state: $(this).val()
        },
        success: function(data) {
            $("#city").html(data)
        }
    })
});
            </script>

            <div class="modal fade" id="show-image" tabindex="-1" aria-labelledby="exampleModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel" id="img-title"></h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <img src="" class="img-fluid" id="big-image">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>

            <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
            <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

            <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css" rel="stylesheet" />
            <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>

            
            <script>
toastr.options = {
    "closeButton": true,
    "debug": false,
    "newestOnTop": false,
    "progressBar": true,
    "positionClass": "toast-top-right",
    "preventDuplicates": false,
    "onclick": null,
    "showDuration": "300",
    "hideDuration": "1000",
    "timeOut": "5000",
    "extendedTimeOut": "1000",
    "showEasing": "swing",
    "hideEasing": "linear",
    "showMethod": "fadeIn",
    "hideMethod": "fadeOut"
}

$(document).ready(function() {
    $('.select2').select2();
});

$(document).ready(function() {
    $('#quoteForm').on('submit', function(e) {
        e.preventDefault();

        var quoteContent = $('#note').val();
        console.log(quoteContent);

        $.ajax({
            type: 'POST',
            url: 'ajax/addNote.php',
            data: {
                quote: quoteContent
            },
            dataType: 'json',
            success: function(response) {
                if (response.alert) {
                    Swal.fire({
                        title: response.alert.title,
                        text: response.alert.text,
                        icon: response.alert.icon,
                        confirmButtonText: 'OK'
                    });
                     window.location.reload(100);
                }
                if (response.status == 'success') {
                    $('#quoteForm')[0].reset();
                }
            },
            error: function() {
                Swal.fire({
                    title: 'Error',
                    text: 'Something went wrong.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        });
    });
});


$(document).on('click', '.deleteNote', function(e) {
    e.preventDefault();

    var noteId = $(this).data('id');
    var listItem = $(this).closest('li');

    $.ajax({
        type: 'POST',
        url: 'ajax/deleteNote.php',
        data: {
            id: noteId
        },
        dataType: 'json',
        success: function(response) {
            if (response.status == 'success') {
                listItem.fadeOut(300, function() {
                    $(this).remove();
                });
            } else {
                alert('Failed to delete note.');
            }
        },
        error: function() {
            alert('Something went wrong.');
        }
    });
});
            </script>


            <?php
            if (isset($_SESSION['message']) && !empty($_SESSION['message'])) 
            {
                $status = $_SESSION['status'];
                $message = $_SESSION['message'];
                switch ($status) 
                {
                    case '200': 
                        $toastType = 'success';
                        $title = 'Success';
                        break;
                    case '400':
                        $toastType = 'error';
                        $title = 'Error';
                        break;
                    case '300':
                        $toastType = 'warning';
                        $title = 'Warning';
                        break;
                    default:
                        $toastType = 'info';
                        $title = 'Info';
                        break;
                }

                echo '<script>
                    toastr.options = {
                        "closeButton": true,
                        "progressBar": true,
                        "positionClass": "toast-top-right",
                        "timeOut": "5000",
                        "extendedTimeOut": "1000"
                    };
                    toastr.' . $toastType . '("' . addslashes($message) . '", "' . addslashes($title) . '");
                </script>';
                unset($_SESSION['status'], $_SESSION['message'], $_SESSION['data']);
            }
        ?>

            <?php toaster(); ?>
            <script>
                    CKEDITOR.replace('description');

                    
            </script>

            </body>

            </html>