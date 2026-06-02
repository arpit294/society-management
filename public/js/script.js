$(document).ready(function () {
    // SweetAlert Configuration
    const swalWithBootstrapButtons = Swal.mixin({
        customClass: {
            confirmButton: "btn btn-danger mx-2",
            cancelButton: "btn btn-secondary",
        },
        buttonsStyling: false,
    });

    // Toastr Configuration
    toastr.options = {
        closeButton: true,
        progressBar: true,
        positionClass: "toast-top-right",
        timeOut: 3000,
    };

    // CSRF TOKEN
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            "X-Requested-With": "XMLHttpRequest",
            Accept: "application/json",
        },
    });

    // Block Modal Variables
    const blockModalEl = document.getElementById("block-modal");
    const BlockModalClass = window.coreui?.Modal || window.bootstrap?.Modal;
    const blockModalInstance =
        blockModalEl && BlockModalClass
            ? BlockModalClass.getOrCreateInstance(blockModalEl)
            : null;

    // User Modal Variables
    const userModalEl = document.getElementById("user-modal");
    const UserModalClass = window.coreui?.Modal || window.bootstrap?.Modal;
    const userModalInstance =
        userModalEl && UserModalClass
            ? UserModalClass.getOrCreateInstance(userModalEl)
            : null;

    // Toast Source Check
    let toastSource = $("#users-toast-source");

    if (toastSource.length && toastSource.data("message")) {
        let type = toastSource.data("type") || "success";

        if (type === "success") {
            toastr.success(toastSource.data("message"));
        } else {
            toastr.error(toastSource.data("message"));
        }
    }

    // Toggle User Reset Button Visibility
    function toggleUserResetBtn() {
        if ($("#users-filter-role").val() || $("#users-filter-status").val()) {
            $("#users-filter-reset-col").removeClass("d-none");
        } else {
            $("#users-filter-reset-col").addClass("d-none");
        }
    }

    // user Role Filter Change
    $(document)
        .off("change", "#users-filter-role")
        .on("change", "#users-filter-role", function () {
            let roleValue = $(this).val();

            $("#users-table").DataTable().column(4).search(roleValue).draw();
            toggleUserResetBtn();
        });


    // $(document)
    // .off("change","usaers-filter-role")
    // .on("change", " #users-filter-role", function () {
    //     let rolevalue = $(this).val();

    //     $("#users-table").DataTable().column(4).search(rolevalue).draw();0
    //     toggleUserResetBtn();
    // });

    // user Status Filter Change
    $(document)
        .off("change", "#users-filter-status")
        .on("change", "#users-filter-status", function () {
            let statusValue = $(this).val();

            $("#users-table").DataTable().column(5).search(statusValue).draw();
            toggleUserResetBtn();
        });

    // user Filter Reset
    $(document)
        .off("click", "#users-filter-reset")
        .on("click", "#users-filter-reset", function () {
            $("#users-filter-role").val("");
            $("#users-filter-status").val("");

            let dt = $("#users-table").DataTable();

            dt.column(4).search("");
            dt.column(5).search("");
            dt.draw();
            toggleUserResetBtn();
        });

    // Toggle Flat Reset Button Visibility
    function toggleFlatResetBtn() {
        if ($("#flats-filter-type").val() || $("#flats-filter-status").val()) {
            $("#flats-filter-reset-col").removeClass("d-none");
        } else {
            $("#flats-filter-reset-col").addClass("d-none");
        }
    }

    // Flat Type Filter Change
    $(document)
        .off("change", "#flats-filter-type")
        .on("change", "#flats-filter-type", function () {
            let typeValue = $(this).val();
            $("#flats-table")
                .DataTable()
                .column("flat_type:name")
                .search(typeValue)
                .draw();
            toggleFlatResetBtn();
        });

    // Flat Status Filter Change
    $(document)
        .off("change", "#flats-filter-status")
        .on("change", "#flats-filter-status", function () {
            let statusValue = $(this).val();
            $("#flats-table")
                .DataTable()
                .column("status:name")
                .search(statusValue)
                .draw();
            toggleFlatResetBtn();
        });

    // $(document)
    //     .off("change", "#flats-filter-status")
    //     .on("change", "#flats-filter-status" , function () {
    //         let statusValue = $(this).val();
    //         $("#falts-table")
    //         .DataTable()
    //         .column("status:name")
    //         .draw();
    //         toggleFlatResetBtn();
    //     });

    // Flat Filter Reset
    $(document)
        .off("click", "#flats-filter-reset")
        .on("click", "#flats-filter-reset", function () {
            $("#flats-filter-type").val("");
            $("#flats-filter-status").val("");

            let dt = $("#flats-table").DataTable();
            dt.column("flat_type:name").search("");
            dt.column("status:name").search("");
            dt.draw();
            toggleFlatResetBtn();
        });

    // Add User Form Open
    $(document)
        .off("click", "#btn-add-user")
        .on("click", "#btn-add-user", function () {
            let url = $(this).data("url");
            let title = $(this).data("title");

            $.ajax({
                type: "GET",
                url: url,

                success: function (response) {
                    $("#user-modal-content").html(response);

                    $("#user-modal-content .modal-title").text(title);

                    userModalInstance?.show();
                },

                error: function () {
                    toastr.error("Could not load form.");
                },
            });
        });

        // $(document)
        // .off("click","#btn-add-user")
        // .on("click","#btn-add-user" , function () {
        //     let url = $(this).data("url");
        //     let title = $(this).data("title");

        //     $.ajax({
        //         type:"GET",
        //         url : url,

        //         success: function (response){
        //             $("#user-model-content").html(responce);

        //             $("#user-modol-content . model-title").text(title);

        //             userModalInstance?.show();
        //         },

        //         error: function() {
        //             toastr.error("could not load form");
        //         }
        //     })
        // })

    // Edit User Form Open
    $(document)
        .off("click", "#users-table .btn-edit-user")
        .on("click", "#users-table .btn-edit-user", function () {
            let url = $(this).data("url");
            let title = $(this).data("title");

            $.ajax({
                type: "GET",
                url: url,

                success: function (response) {
                    $("#user-modal-content").html(response);

                    $("#user-modal-content .modal-title").text(title);

                    userModalInstance?.show();
                },

                error: function () {
                    toastr.error("Could not load form.");
                },
            });
        });

    // Add/Edit User Form Submit
    $(document)
        .off("submit", "#user-ajax-form")
        .on("submit", "#user-ajax-form", function (e) {
            e.preventDefault();

            let formData = new FormData(this);
            let requestType = $(this).attr("method") || "POST";
            let formAction = $(this).attr("action");

            let $btn = $(this).find('button[type="submit"]');
            $btn.prop("disabled", true);

            $(".field-error").remove();
            $(".is-invalid").removeClass("is-invalid");

            $.ajax({
                url: formAction,
                method: requestType,
                data: formData,
                processData: false,
                contentType: false,

                success: function (response) {
                    $btn.prop("disabled", false);
                    toastr.success(response.message || "Saved successfully.");

                    userModalInstance?.hide();

                    $("#users-table").DataTable().ajax.reload();
                },

                error: function (xhr) {
                    $btn.prop("disabled", false);
                    $(".field-error").remove();
                    $(".is-invalid").removeClass("is-invalid");

                    let errors = xhr.responseJSON?.errors;

                    if (errors) {
                        $.each(errors, function (key, value) {
                            let field = $('[name="' + key + '"]');

                            field.addClass("is-invalid");

                            let target = field;
                            if (field.parent().hasClass("input-group")) {
                                target = field.parent();
                            }

                            $(
                                '<div class="invalid-feedback d-block field-error text-danger"></div>',
                            )
                                .text(value[0])
                                .insertAfter(target);
                        });
                    } else {
                        toastr.error(
                            xhr.responseJSON?.message ||
                                "Something went wrong.",
                        );
                    }
                },
            });
        });

    // Delete Single User
    $(document)
        .off("click", "#users-table .btn-delete-user")
        .on("click", "#users-table .btn-delete-user", function () {
            let url = $(this).data("url");

            swalWithBootstrapButtons
                .fire({
                    title: "Are you sure?",
                    text: "This user will be deleted permanently!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Yes, delete!",
                    cancelButtonText: "Cancel",
                    reverseButtons: true,
                })
                .then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: url,
                            type: "DELETE",

                            success: function (response) {
                                toastr.success(
                                    response.message || "Deleted successfully.",
                                );

                                $("#users-table").DataTable().ajax.reload();
                            },

                            error: function (xhr) {
                                toastr.error(
                                    xhr.responseJSON?.message ||
                                        "Could not delete user.",
                                );
                            },
                        });
                    }
                });
        });

    // Add Block Form Open
    $(document)
        .off("click", "#btn-add-block")
        .on("click", "#btn-add-block", function () {
            let url = $(this).data("url");
            let title = $(this).data("title");

            $.ajax({
                type: "GET",
                url: url,

                success: function (response) {
                    $("#block-modal-content").html(response);

                    $("#block-modal-content .modal-title").text(title);

                    blockModalInstance?.show();
                },

                error: function () {
                    toastr.error("Could not load form.");
                },
            });
        });


      

    // Edit Block Form Open
    $(document)
        .off("click", "#blocks-table .btn-edit-block")
        .on("click", "#blocks-table .btn-edit-block", function () {
            let url = $(this).data("url");
            let title = $(this).data("title");

            $.ajax({
                type: "GET",
                url: url,

                success: function (response) {
                    $("#block-modal-content").html(response);

                    $("#block-modal-content .modal-title").text(title);

                    blockModalInstance?.show();
                },

                error: function () {
                    toastr.error("Could not load form.");
                },
            });
        });

    // Add/Edit Block Form Submit
    $(document)
        .off("submit", "#block-ajax-form")
        .on("submit", "#block-ajax-form", function (e) {
            e.preventDefault();

            let formData = new FormData(this);
            let requestType = $(this).attr("method") || "POST";
            let formAction = $(this).attr("action");

            let $btn = $(this).find('button[type="submit"]');
            $btn.prop("disabled", true);

            $(".field-error").remove();
            $(".is-invalid").removeClass("is-invalid");

            $.ajax({
                url: formAction,
                method: requestType,
                data: formData,
                processData: false,
                contentType: false,

                success: function (response) {
                    $btn.prop("disabled", false);
                    toastr.success(response.message || "Saved successfully.");

                    blockModalInstance?.hide();

                    if ($.fn.DataTable.isDataTable("#blocks-table")) {
                        $("#blocks-table").DataTable().ajax.reload();
                    }
                },

                error: function (xhr) {
                    $btn.prop("disabled", false);
                    $(".field-error").remove();
                    $(".is-invalid").removeClass("is-invalid");

                    let errors = xhr.responseJSON?.errors;

                    if (errors) {
                        $.each(errors, function (key, value) {
                            let field = $('[name="' + key + '"]');
                            field.addClass("is-invalid");

                            let target = field;
                            if (field.parent().hasClass("input-group")) {
                                target = field.parent();
                            }

                            $(
                                '<div class="invalid-feedback d-block field-error text-danger"></div>',
                            )
                                .text(value[0])
                                .insertAfter(target);
                        });
                    } else {
                        toastr.error(
                            xhr.responseJSON?.message ||
                                "Something went wrong.",
                        );
                    }
                },
            });
        });

    // Delete Single Block
    $(document)
        .off("click", "#blocks-table .btn-delete-block")
        .on("click", "#blocks-table .btn-delete-block", function () {
            let url = $(this).data("url");

            swalWithBootstrapButtons
                .fire({
                    title: "Are you sure?",
                    text: "This block will be deleted permanently!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Yes, delete!",
                    cancelButtonText: "Cancel",
                    reverseButtons: true,
                })
                .then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: url,
                            type: "DELETE",

                            success: function (response) {
                                toastr.success(
                                    response.message || "Deleted successfully.",
                                );

                                if (
                                    $.fn.DataTable.isDataTable("#blocks-table")
                                ) {
                                    $("#blocks-table")
                                        .DataTable()
                                        .ajax.reload();
                                }
                            },

                            error: function (xhr) {
                                toastr.error(
                                    xhr.responseJSON?.message ||
                                        "Could not delete block.",
                                );
                            },
                        });
                    }
                });
        });

    // Flat Modal Variables
    const flatModalEl = document.getElementById("flat-modal");
    const FlatModalClass = window.coreui?.Modal || window.bootstrap?.Modal;
    const flatModalInstance =
        flatModalEl && FlatModalClass
            ? FlatModalClass.getOrCreateInstance(flatModalEl)
            : null;

    // Flat Form Block Selection Change
    $(document)
        .off("change", '#flat-ajax-form select[name="block_id"]')
        .on("change", '#flat-ajax-form select[name="block_id"]', function () {
            const selectedOption = $(this).find("option:selected");
            const maxFloor = selectedOption.data("total-floor");
            const floorInput = $("#floor_no");
            const floorHelp = $("#floor-help");

            if (maxFloor) {
                floorInput.attr("max", maxFloor);
                floorHelp.find("span").text(maxFloor);
                floorHelp.removeClass("d-none");

                if (parseInt(floorInput.val()) > parseInt(maxFloor)) {
                    floorInput.val(maxFloor);
                }
            } else {
                floorInput.removeAttr("max");
                floorHelp.addClass("d-none");
            }
        });

    // Add Flat Form Open
    $(document)
        .off("click", "#btn-add-flat")
        .on("click", "#btn-add-flat", function () {
            let url = $(this).data("url");
            let title = $(this).data("title");

            $.ajax({
                type: "GET",
                url: url,

                success: function (response) {
                    $("#flat-modal-content").html(response);
                    $("#flat-modal-content .modal-title").text(title);
                    flatModalInstance?.show();

                    // Trigger block change to set max floors if a block is already selected
                    $('#flat-ajax-form select[name="block_id"]').trigger(
                        "change",
                    );
                },

                error: function () {
                    toastr.error("Could not load form.");
                },
            });
        });

    // Edit Flat Form Open
    $(document)
        .off("click", "#flats-table .btn-edit-flat")
        .on("click", "#flats-table .btn-edit-flat", function () {
            let url = $(this).data("url");
            let title = $(this).data("title");

            $.ajax({
                type: "GET",
                url: url,

                success: function (response) {
                    $("#flat-modal-content").html(response);
                    $("#flat-modal-content .modal-title").text(title);
                    flatModalInstance?.show();

                    // Trigger block change to set max floors if a block is already selected
                    $('#flat-ajax-form select[name="block_id"]').trigger(
                        "change",
                    );
                },

                error: function () {
                    toastr.error("Could not load form.");
                },
            });
        });

    // Add/Edit Flat Form Submit
    $(document)
        .off("submit", "#flat-ajax-form")
        .on("submit", "#flat-ajax-form", function (e) {
            e.preventDefault();

            let formData = new FormData(this);
            let requestType = $(this).attr("method") || "POST";
            let formAction = $(this).attr("action");

            // Handle Laravel method spoofing for PUT/PATCH
            let spoofedMethod = $(this).find('input[name="_method"]').val();
            if (spoofedMethod) {
                requestType = spoofedMethod;
            }

            let $btn = $(this).find('button[type="submit"]');
            $btn.prop("disabled", true);

            $(".field-error").remove();
            $(".is-invalid").removeClass("is-invalid");

            $.ajax({
                url: formAction,
                method: "POST", // Always POST for form data with files, spoofing via _method
                data: formData,
                processData: false,
                contentType: false,

                success: function (response) {
                    $btn.prop("disabled", false);
                    toastr.success(response.message || "Saved successfully.");

                    flatModalInstance?.hide();

                    if ($.fn.DataTable.isDataTable("#flats-table")) {
                        $("#flats-table").DataTable().ajax.reload();
                    }
                },

                error: function (xhr) {
                    $btn.prop("disabled", false);
                    $(".field-error").remove();
                    $(".is-invalid").removeClass("is-invalid");

                    let errors = xhr.responseJSON?.errors;

                    if (errors) {
                        $.each(errors, function (key, value) {
                            let field = $('[name="' + key + '"]');
                            field.addClass("is-invalid");
                            let target = field;
                            if (field.parent().hasClass("input-group")) {
                                target = field.parent();
                            }
                            $(
                                '<div class="invalid-feedback d-block field-error text-danger"></div>',
                            )
                                .text(value[0])
                                .insertAfter(target);
                        });
                    } else {
                        toastr.error(
                            xhr.responseJSON?.message ||
                                "Something went wrong.",
                        );
                    }
                },
            });
        });

    // Delete Single Flat
    $(document)
        .off("click", "#flats-table .btn-delete-flat")
        .on("click", "#flats-table .btn-delete-flat", function () {
            let url = $(this).data("url");

            swalWithBootstrapButtons
                .fire({
                    title: "Are you sure?",
                    text: "This flat will be deleted permanently!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Yes, delete!",
                    cancelButtonText: "Cancel",
                    reverseButtons: true,
                })
                .then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: url,
                            type: "DELETE",

                            success: function (response) {
                                toastr.success(
                                    response.message || "Deleted successfully.",
                                );

                                if (
                                    $.fn.DataTable.isDataTable("#flats-table")
                                ) {
                                    $("#flats-table").DataTable().ajax.reload();
                                }
                            },

                            error: function (xhr) {
                                toastr.error(
                                    xhr.responseJSON?.message ||
                                        "Could not delete flat.",
                                );
                            },
                        });
                    }
                });
        });

    // Modal Close Cleanup
    $(document)
        .off("click", '[data-coreui-dismiss="modal"]')
        .on("click", '[data-coreui-dismiss="modal"]', function () {
            userModalInstance?.hide();
            blockModalInstance?.hide();
            flatModalInstance?.hide();
            complainModalInstance?.hide();
        });

    // Complain Modal Variables
    const complainModalEl = document.getElementById("complain-modal");
    const ComplainModalClass = window.coreui?.Modal || window.bootstrap?.Modal;
    const complainModalInstance =
        complainModalEl && ComplainModalClass
            ? ComplainModalClass.getOrCreateInstance(complainModalEl)
            : null;

    // Toggle Complain Reset Button Visibility
    function toggleComplainResetBtn() {
        if ($("#complains-filter-category").val()) {
            $("#complains-filter-reset-col").removeClass("d-none");
        } else {
            $("#complains-filter-reset-col").addClass("d-none");
        }
    }

    // Complain Category Filter Change
    $(document)
        .off("change", "#complains-filter-category")
        .on("change", "#complains-filter-category", function () {
            let categoryValue = $(this).val();
            $("#complains-table")
                .DataTable()
                .column("complains.category:name")
                .search(categoryValue)
                .draw();
            toggleComplainResetBtn();
        });

    // Complain Filter Reset
    $(document)
        .off("click", "#complains-filter-reset")
        .on("click", "#complains-filter-reset", function () {
            $("#complains-filter-category").val("");

            let dt = $("#complains-table").DataTable();
            dt.column("complains.category:name").search("");
            dt.draw();
            toggleComplainResetBtn();
        });

    // Add Complain Form Open
    $(document)
        .off("click", "#btn-add-complain")
        .on("click", "#btn-add-complain", function () {
            let url = $(this).data("url");
            let title = $(this).data("title");

            $.ajax({
                type: "GET",
                url: url,

                success: function (response) {
                    $("#complain-modal-content").html(response);
                    $("#complain-modal-content .modal-title").text(title);
                    complainModalInstance?.show();
                },

                error: function () {
                    toastr.error("Could not load form.");
                },
            });
        });

    // Edit Complain Form Open
    $(document)
        .off("click", "#complains-table .btn-edit-complain")
        .on("click", "#complains-table .btn-edit-complain", function () {
            let url = $(this).data("url");
            let title = $(this).data("title");

            $.ajax({
                type: "GET",
                url: url,

                success: function (response) {
                    $("#complain-modal-content").html(response);
                    $("#complain-modal-content .modal-title").text(title);
                    complainModalInstance?.show();
                },

                error: function () {
                    toastr.error("Could not load form.");
                },
            });
        });

    // Add/Edit Complain Form Submit
    $(document)
        .off("submit", "#complain-ajax-form")
        .on("submit", "#complain-ajax-form", function (e) {
            e.preventDefault();

            let formData = new FormData(this);
            let requestType = $(this).attr("method") || "POST";
            let formAction = $(this).attr("action");

            let spoofedMethod = $(this).find('input[name="_method"]').val();
            if (spoofedMethod) {
                requestType = spoofedMethod;
            }

            let $btn = $(this).find('button[type="submit"]');
            $btn.prop("disabled", true);

            $(".field-error").remove();
            $(".is-invalid").removeClass("is-invalid");

            $.ajax({
                url: formAction,
                method: "POST", // Handle via spoofing
                data: formData,
                processData: false,
                contentType: false,

                success: function (response) {
                    $btn.prop("disabled", false);
                    toastr.success(response.message || "Saved successfully.");

                    complainModalInstance?.hide();

                    if ($.fn.DataTable.isDataTable("#complains-table")) {
                        $("#complains-table").DataTable().ajax.reload();
                    }
                },

                error: function (xhr) {
                    $btn.prop("disabled", false);
                    $(".field-error").remove();
                    $(".is-invalid").removeClass("is-invalid");

                    let errors = xhr.responseJSON?.errors;

                    if (errors) {
                        $.each(errors, function (key, value) {
                            let field = $('[name="' + key + '"]');
                            field.addClass("is-invalid");
                            let target = field;
                            if (field.parent().hasClass("input-group")) {
                                target = field.parent();
                            }
                            $(
                                '<div class="invalid-feedback d-block field-error text-danger"></div>',
                            )
                                .text(value[0])
                                .insertAfter(target);
                        });
                    } else {
                        toastr.error(
                            xhr.responseJSON?.message ||
                                "Something went wrong.",
                        );
                    }
                },
            });
        });

    // Delete Single Complain
    $(document)
        .off("click", "#complains-table .btn-delete-complain")
        .on("click", "#complains-table .btn-delete-complain", function () {
            let url = $(this).data("url");

            swalWithBootstrapButtons
                .fire({
                    title: "Are you sure?",
                    text: "This complaint will be deleted permanently!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Yes, delete!",
                    cancelButtonText: "Cancel",
                    reverseButtons: true,
                })
                .then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: url,
                            type: "DELETE",

                            success: function (response) {
                                toastr.success(
                                    response.message || "Deleted successfully.",
                                );

                                if (
                                    $.fn.DataTable.isDataTable(
                                        "#complains-table",
                                    )
                                ) {
                                    $("#complains-table")
                                        .DataTable()
                                        .ajax.reload();
                                }
                            },

                            error: function (xhr) {
                                toastr.error(
                                    xhr.responseJSON?.message ||
                                        "Could not delete complaint.",
                                );
                            },
                        });
                    }
                });
        });

    // Resident Modal Variables
    const residentModalEl = document.getElementById("resident-modal");
    const ResidentModalClass = window.coreui?.Modal || window.bootstrap?.Modal;
    const residentModalInstance = residentModalEl && ResidentModalClass ? ResidentModalClass.getOrCreateInstance(residentModalEl) : null;

    // Add/Edit Resident Form Open
    $(document)
        .off("click", "#btn-add-resident, #residents-table .btn-edit-resident")
        .on("click", "#btn-add-resident, #residents-table .btn-edit-resident", function () {
            let url = $(this).data("url");
            let title = $(this).data("title");
            $.ajax({
                type: "GET",
                url: url,
                success: function (response) {
                    $("#resident-modal-content").html(response);
                    $("#resident-modal-content .modal-title").text(title);
                    residentModalInstance?.show();
                },
                error: function (xhr) {
                    toastr.error(xhr.responseJSON?.message || "Could not load form.");
                },
            });
        });

    // Resident Block Change (Load Flats)
    $(document).on('change', '#block_id', function() {
        var blockId = $(this).val();
        if(blockId) {
            $.ajax({
                url: '/api/flats-by-block/' + blockId,
                type: "GET",
                dataType: "json",
                success:function(data) {
                    $('#flat_id').empty();
                    $('#flat_id').append('<option value="">Select Flat</option>');
                    $.each(data, function(key, value) {
                        $('#flat_id').append('<option value="'+ value.id +'">'+ value.flat_no +'</option>');
                    });
                }
            });
        } else {
            $('#flat_id').empty();
            $('#flat_id').append('<option value="">Select Block First</option>');
        }
    });

    // Add/Edit Resident Form Submit
    $(document)
        .off("submit", "#resident-ajax-form")
        .on("submit", "#resident-ajax-form", function(e) {
            e.preventDefault();
            let formData = new FormData(this);
            
            // Check if this is an update request (it has a hidden _method=PUT)
            if($(this).find('input[name="_method"]').length > 0) {
                formData.append('_method', $(this).find('input[name="_method"]').val());
            }

            let formAction = $(this).attr("action");
            let $btn = $(this).find('button[type="submit"]');
            $btn.prop("disabled", true);
            $(".field-error").remove();
            $(".is-invalid").removeClass("is-invalid");

            $.ajax({
                url: formAction,
                method: "POST", // Form data and files require POST, spoof PUT if needed
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    $btn.prop("disabled", false);
                    toastr.success(response.message || "Saved successfully.");
                    residentModalInstance?.hide();

                    if ($.fn.DataTable.isDataTable("#residents-table")) {
                        $("#residents-table").DataTable().ajax.reload();
                    }
                },
                error: function (xhr) {
                    $btn.prop("disabled", false);
                    let errors = xhr.responseJSON?.errors;
                    if (errors) {
                        $.each(errors, function (key, value) {
                            let field = $('[name="' + key + '"]');
                            field.addClass("is-invalid");
                            $('<div class="invalid-feedback d-block field-error text-danger"></div>')
                                .text(value[0])
                                .insertAfter(field);
                        });
                    } else {
                        toastr.error(xhr.responseJSON?.message || "Something went wrong.");
                    }
                },
            });
        });

    // Delete Resident
    $(document)
        .off("click", "#residents-table .btn-delete-resident")
        .on("click", "#residents-table .btn-delete-resident", function () {
            let url = $(this).data("url");
            if (swalWithBootstrapButtons) {
                swalWithBootstrapButtons
                    .fire({
                        title: "Are you sure?",
                        text: "This resident will be deleted permanently!",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonText: "Yes, delete!",
                        cancelButtonText: "Cancel",
                        reverseButtons: true,
                    })
                    .then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: url,
                                type: "DELETE",
                                success: function (response) {
                                    toastr.success(response.message || "Deleted successfully.");
                                    if ($.fn.DataTable.isDataTable("#residents-table")) {
                                        $("#residents-table").DataTable().ajax.reload();
                                    }
                                },
                                error: function (xhr) {
                                    toastr.error(xhr.responseJSON?.message || "Could not delete resident.");
                                },
                            });
                        }
                    });
            }
        });

    // Expense Category Modal Variables
    const expenseCategoryModalEl = document.getElementById("expense-category-modal");
    const ExpenseCategoryModalClass = window.coreui?.Modal || window.bootstrap?.Modal;
    const expenseCategoryModalInstance = expenseCategoryModalEl && ExpenseCategoryModalClass ? ExpenseCategoryModalClass.getOrCreateInstance(expenseCategoryModalEl) : null;

    // Add/Edit Expense Category Form Open
    $(document)
        .off("click", "#btn-add-expense-category, #expensecategories-table .btn-edit-category")
        .on("click", "#btn-add-expense-category, #expensecategories-table .btn-edit-category", function () {
            let url = $(this).data("url");
            let title = $(this).data("title");
            $.ajax({
                type: "GET",
                url: url,
                success: function (response) {
                    $("#expense-category-modal-content").html(response);
                    $("#expense-category-modal-content .modal-title").text(title);
                    expenseCategoryModalInstance?.show();
                },
                error: function (xhr) {
                    toastr.error(xhr.responseJSON?.message || "Could not load form.");
                },
            });
        });

    // Add/Edit Expense Category Form Submit
    $(document)
        .off("submit", "#expense-category-ajax-form")
        .on("submit", "#expense-category-ajax-form", function(e) {
            e.preventDefault();
            let formData = new FormData(this);
            
            // Check if this is an update request (it has a hidden _method=PUT)
            if($(this).find('input[name="_method"]').length > 0) {
                formData.append('_method', $(this).find('input[name="_method"]').val());
            }

            let formAction = $(this).attr("action");
            let $btn = $(this).find('button[type="submit"]');
            $btn.prop("disabled", true);
            $(".field-error").remove();
            $(".is-invalid").removeClass("is-invalid");

            $.ajax({
                url: formAction,
                method: "POST", // Form data and files require POST, spoof PUT if needed
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    $btn.prop("disabled", false);
                    toastr.success(response.message || "Saved successfully.");
                    expenseCategoryModalInstance?.hide();

                    if ($.fn.DataTable.isDataTable("#expensecategories-table")) {
                        $("#expensecategories-table").DataTable().ajax.reload();
                    }
                },
                error: function (xhr) {
                    $btn.prop("disabled", false);
                    let errors = xhr.responseJSON?.errors;
                    if (errors) {
                        $.each(errors, function (key, value) {
                            let field = $('[name="' + key + '"]');
                            field.addClass("is-invalid");
                            $('<div class="invalid-feedback d-block field-error text-danger"></div>')
                                .text(value[0])
                                .insertAfter(field);
                        });
                    } else {
                        toastr.error(xhr.responseJSON?.message || "Something went wrong.");
                    }
                },
            });
        });

    // Delete Expense Category
    $(document)
        .off("click", "#expensecategories-table .btn-delete-category")
        .on("click", "#expensecategories-table .btn-delete-category", function () {
            let url = $(this).data("url");
            if (swalWithBootstrapButtons) {
                swalWithBootstrapButtons
                    .fire({
                        title: "Are you sure?",
                        text: "This category will be deleted permanently!",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonText: "Yes, delete!",
                        cancelButtonText: "Cancel",
                        reverseButtons: true,
                    })
                    .then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: url,
                                type: "DELETE",
                                success: function (response) {
                                    toastr.success(response.message || "Deleted successfully.");
                                    if ($.fn.DataTable.isDataTable("#expensecategories-table")) {
                                        $("#expensecategories-table").DataTable().ajax.reload();
                                    }
                                },
                                error: function (xhr) {
                                    toastr.error(xhr.responseJSON?.message || "Could not delete category.");
                                },
                            });
                        }
                    });
            }
        });

    // Expenses Modal Variables
    const expenseModalEl = document.getElementById("expense-modal");
    const ExpenseModalClass = window.coreui?.Modal || window.bootstrap?.Modal;
    const expenseModalInstance = expenseModalEl && ExpenseModalClass ? ExpenseModalClass.getOrCreateInstance(expenseModalEl) : null;

    // Add/Edit Expense Form Open
    $(document)
        .off("click", "#btn-add-expense, #expenses-table .btn-edit-expense")
        .on("click", "#btn-add-expense, #expenses-table .btn-edit-expense", function () {
            let url = $(this).data("url");
            let title = $(this).data("title");
            $.ajax({
                type: "GET",
                url: url,
                success: function (response) {
                    $("#expense-modal-content").html(response);
                    $("#expense-modal-content .modal-title").text(title);
                    expenseModalInstance?.show();
                },
                error: function (xhr) {
                    toastr.error(xhr.responseJSON?.message || "Could not load form.");
                },
            });
        });

    // Add/Edit Expense Form Submit
    $(document)
        .off("submit", "#expense-ajax-form")
        .on("submit", "#expense-ajax-form", function(e) {
            e.preventDefault();
            let formData = new FormData(this);
            
            // Check if this is an update request (it has a hidden _method=PUT)
            if($(this).find('input[name="_method"]').length > 0) {
                formData.append('_method', $(this).find('input[name="_method"]').val());
            }

            let formAction = $(this).attr("action");
            let $btn = $(this).find('button[type="submit"]');
            $btn.prop("disabled", true);
            $(".field-error").remove();
            $(".is-invalid").removeClass("is-invalid");

            $.ajax({
                url: formAction,
                method: "POST", // Form data and files require POST
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    $btn.prop("disabled", false);
                    toastr.success(response.message || "Saved successfully.");
                    expenseModalInstance?.hide();

                    if ($.fn.DataTable.isDataTable("#expenses-table")) {
                        $("#expenses-table").DataTable().ajax.reload();
                    }
                },
                error: function (xhr) {
                    $btn.prop("disabled", false);
                    let errors = xhr.responseJSON?.errors;
                    if (errors) {
                        $.each(errors, function (key, value) {
                            let field = $('[name="' + key + '"]');
                            field.addClass("is-invalid");
                            $('<div class="invalid-feedback d-block field-error text-danger"></div>')
                                .text(value[0])
                                .insertAfter(field);
                        });
                    } else {
                        toastr.error(xhr.responseJSON?.message || "Something went wrong.");
                    }
                },
            });
        });

    // Delete Expense
    $(document)
        .off("click", "#expenses-table .btn-delete-expense")
        .on("click", "#expenses-table .btn-delete-expense", function () {
            let url = $(this).data("url");
            if (swalWithBootstrapButtons) {
                swalWithBootstrapButtons
                    .fire({
                        title: "Are you sure?",
                        text: "This expense will be deleted permanently!",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonText: "Yes, delete!",
                        cancelButtonText: "Cancel",
                        reverseButtons: true,
                    })
                    .then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: url,
                                type: "DELETE",
                                success: function (response) {
                                    toastr.success(response.message || "Deleted successfully.");
                                    if ($.fn.DataTable.isDataTable("#expenses-table")) {
                                        $("#expenses-table").DataTable().ajax.reload();
                                    }
                                },
                                error: function (xhr) {
                                    toastr.error(xhr.responseJSON?.message || "Could not delete expense.");
                                },
                            });
                        }
                    });
            }
        });

    // Dashboard Charts
    if (document.getElementById('occupancyChart')) {
        const occCanvas = document.getElementById('occupancyChart');
        const occupied = occCanvas.dataset.occupied;
        const empty = occCanvas.dataset.empty;

        new Chart(occCanvas, {
            type: 'doughnut',
            data: {
                labels: ['Occupied', 'Empty'],
                datasets: [{
                    data: [occupied, empty],
                    backgroundColor: ['#2eb85c', '#e55353'], // CoreUI success and danger colors
                    hoverBackgroundColor: ['#2eb85c', '#e55353']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
            }
        });
    }

    if (document.getElementById('complaintsChart')) {
        const compCanvas = document.getElementById('complaintsChart');
        const labels = JSON.parse(compCanvas.dataset.labels);
        const counts = JSON.parse(compCanvas.dataset.counts);

        new Chart(compCanvas, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Complaints',
                    backgroundColor: '#3399ff', // CoreUI primary color
                    data: counts
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }
});
