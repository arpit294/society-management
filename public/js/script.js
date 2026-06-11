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
                .column("flat_type_id:name")
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
            dt.column("flat_type_id:name").search("");
            dt.column("status:name").search("");
            dt.draw();
            toggleFlatResetBtn();
        });

    function toggleResidentResetBtn() {
        if ($("#residents-filter-block").val()) {
            $("#residents-filter-reset-col").removeClass("d-none");
        } else {
            $("#residents-filter-reset-col").addClass("d-none");
        }
    }

    // Resident Block Filter Change
    $(document)
        .off("change", "#residents-filter-block")
        .on("change", "#residents-filter-block", function () {
            if (
                window.LaravelDataTables &&
                window.LaravelDataTables["residents-table"]
            ) {
                window.LaravelDataTables["residents-table"].ajax.reload();
            } else {
                $("#residents-table").DataTable().ajax.reload();
            }
            toggleResidentResetBtn();
        });

    // Resident Filter Reset
    $(document)
        .off("click", "#residents-filter-reset")
        .on("click", "#residents-filter-reset", function () {
            $("#residents-filter-block").val("");

            if (
                window.LaravelDataTables &&
                window.LaravelDataTables["residents-table"]
            ) {
                window.LaravelDataTables["residents-table"].ajax.reload();
            } else {
                $("#residents-table").DataTable().ajax.reload();
            }
            toggleResidentResetBtn();
        });

    function toggleMaintenanceBillsResetBtn() {
        if ($("#maintenance-bills-filter-method").val()) {
            $("#maintenance-bills-filter-reset-col").removeClass("d-none");
        } else {
            $("#maintenance-bills-filter-reset-col").addClass("d-none");
        }
    }

    $(document)
        .off("change", "#maintenance-bills-filter-method")
        .on("change", "#maintenance-bills-filter-method", function () {
            let methodValue = $(this).val();
            $("#maintenance-bills-table")
                .DataTable()
                .column("payment_method:name")
                .search(methodValue)
                .draw();
            toggleMaintenanceBillsResetBtn();
        });

    $(document)
        .off("click", "#maintenance-bills-filter-reset")
        .on("click", "#maintenance-bills-filter-reset", function () {
            $("#maintenance-bills-filter-method").val("");

            let dt = $("#maintenance-bills-table").DataTable();
            dt.column("payment_method:name").search("");
            dt.draw();
            toggleMaintenanceBillsResetBtn();
        });

    function toggleExpensesResetBtn() {
        if ($("#expenses-filter-category").val()) {
            $("#expenses-filter-reset-col").removeClass("d-none");
        } else {
            $("#expenses-filter-reset-col").addClass("d-none");
        }
    }

    $(document)
        .off("change", "#expenses-filter-category")
        .on("change", "#expenses-filter-category", function () {
            let categoryValue = $(this).val();
            $("#expenses-table")
                .DataTable()
                .column("expense_categories.title:name")
                .search(categoryValue)
                .draw();
            toggleExpensesResetBtn();
        });

    $(document)
        .off("click", "#expenses-filter-reset")
        .on("click", "#expenses-filter-reset", function () {
            $("#expenses-filter-category").val("");

            let dt = $("#expenses-table").DataTable();
            dt.column("expense_categories.title:name").search("");
            dt.draw();
            toggleExpensesResetBtn();
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

                    setTimeout(function () {
                        window.location.reload();
                    }, 800);
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

                                setTimeout(function () {
                                    window.location.reload();
                                }, 800);
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
            expenseModalInstance?.hide();
            expenseCategoryModalInstance?.hide();
            flatTypeModalInstance?.hide();
            maintenanceBillModalInstance?.hide();
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
    const residentModalInstance =
        residentModalEl && ResidentModalClass
            ? ResidentModalClass.getOrCreateInstance(residentModalEl)
            : null;

    // Edit Resident Form Open
    $(document)
        .off("click", "#residents-table .btn-edit-resident")
        .on("click", "#residents-table .btn-edit-resident", function () {
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

                error: function () {
                    toastr.error("Could not load form.");
                },
            });
        });

    // Add Resident Form Open
    $(document)
        .off("click", "#btn-add-resident")
        .on("click", "#btn-add-resident", function () {
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

                error: function () {
                    toastr.error("Could not load form.");
                },
            });
        });

    // Filter flats based on selected block in Resident form
    $(document).on("change", "#resident-ajax-form select[name='block_id']", function () {
        let blockId = $(this).val();
        let flatSelect = $("#resident-ajax-form select[name='flat_id']");

        if (!blockId) {
            flatSelect.html('<option value="">Select Flat</option>');
            return;
        }

        flatSelect.html('<option value="">Loading...</option>');

        $.ajax({
            url: `/api/flats-by-block/${blockId}`,
            type: "GET",
            success: function (data) {
                let html = '<option value="">Select Flat</option>';
                data.forEach(function (flat) {
                    html += `<option value="${flat.id}">${flat.flat_no}</option>`;
                });
                flatSelect.html(html);
            },
            error: function () {
                flatSelect.html('<option value="">Error loading flats</option>');
            }
        });
    });

    // Add/Edit Resident Form Submit
    $(document)
        .off("submit", "#resident-ajax-form")
        .on("submit", "#resident-ajax-form", function (e) {
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

                    residentModalInstance?.hide();

                    if ($.fn.DataTable.isDataTable("#residents-table")) {
                        $("#residents-table").DataTable().ajax.reload();
                    } else if (
                        window.LaravelDataTables &&
                        window.LaravelDataTables["residents-table"]
                    ) {
                        window.LaravelDataTables[
                            "residents-table"
                        ].ajax.reload();
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
    // Delete Single Resident
    $(document)
        .off("click", "#residents-table .btn-delete-resident")
        .on("click", "#residents-table .btn-delete-resident", function () {
            let url = $(this).data("url");

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
                                toastr.success(
                                    response.message || "Deleted successfully.",
                                );

                                if (
                                    $.fn.DataTable.isDataTable(
                                        "#residents-table",
                                    )
                                ) {
                                    $("#residents-table")
                                        .DataTable()
                                        .ajax.reload();
                                } else if (
                                    window.LaravelDataTables &&
                                    window.LaravelDataTables["residents-table"]
                                ) {
                                    window.LaravelDataTables[
                                        "residents-table"
                                    ].ajax.reload();
                                }
                            },

                            error: function (xhr) {
                                toastr.error(
                                    xhr.responseJSON?.message ||
                                        "Could not delete resident.",
                                );
                            },
                        });
                    }
                });
        });

    // Expense Modal Variables
    const expenseModalEl = document.getElementById("expense-modal");
    const ExpenseModalClass = window.coreui?.Modal || window.bootstrap?.Modal;
    const expenseModalInstance =
        expenseModalEl && ExpenseModalClass
            ? ExpenseModalClass.getOrCreateInstance(expenseModalEl)
            : null;

    // Invoice Image Preview handler
    $(document)
        .off("change", "#expense-modal-content #invoice")
        .on("change", "#expense-modal-content #invoice", function (event) {
            const file = event.target.files[0];
            const previewContainer = $("#invoice-preview-container");
            const previewImg = $("#invoice-preview-img");

            if (file) {
                // Check if file is an image
                if (file.type.match("image.*")) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        previewImg.attr("src", e.target.result);
                        previewContainer.removeClass("d-none");
                    };
                    reader.readAsDataURL(file);
                } else {
                    // Hide preview if not an image (e.g. PDF)
                    previewImg.attr("src", "");
                    previewContainer.addClass("d-none");
                }
            } else {
                previewImg.attr("src", "");
                previewContainer.addClass("d-none");
            }
        });

    // Add Expense Form Open
    $(document)
        .off("click", "#btn-add-expense")
        .on("click", "#btn-add-expense", function () {
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

                error: function () {
                    toastr.error("Could not load form.");
                },
            });
        });

    // Edit Expense Form Open
    $(document)
        .off("click", "#expenses-table .btn-edit-expense")
        .on("click", "#expenses-table .btn-edit-expense", function () {
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

                error: function () {
                    toastr.error("Could not load form.");
                },
            });
        });

    // Add/Edit Expense Form Submit
    $(document)
        .off("submit", "#expense-ajax-form")
        .on("submit", "#expense-ajax-form", function (e) {
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

                    expenseModalInstance?.hide();

                    if ($.fn.DataTable.isDataTable("#expenses-table")) {
                        $("#expenses-table").DataTable().ajax.reload();
                    } else if (
                        window.LaravelDataTables &&
                        window.LaravelDataTables["expenses-table"]
                    ) {
                        window.LaravelDataTables[
                            "expenses-table"
                        ].ajax.reload();
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

    // Delete Single Expense
    $(document)
        .off("click", "#expenses-table .btn-delete-expense")
        .on("click", "#expenses-table .btn-delete-expense", function () {
            let url = $(this).data("url");

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
                                toastr.success(
                                    response.message || "Deleted successfully.",
                                );

                                if (
                                    $.fn.DataTable.isDataTable(
                                        "#expenses-table",
                                    )
                                ) {
                                    $("#expenses-table")
                                        .DataTable()
                                        .ajax.reload();
                                } else if (
                                    window.LaravelDataTables &&
                                    window.LaravelDataTables["expenses-table"]
                                ) {
                                    window.LaravelDataTables[
                                        "expenses-table"
                                    ].ajax.reload();
                                }
                            },

                            error: function (xhr) {
                                toastr.error(
                                    xhr.responseJSON?.message ||
                                        "Could not delete expense.",
                                );
                            },
                        });
                    }
                });
        });
    // Expense Category Modal Variables
    const expenseCategoryModalEl = document.getElementById(
        "expense-category-modal",
    );
    const ExpenseCategoryModalClass =
        window.coreui?.Modal || window.bootstrap?.Modal;
    const expenseCategoryModalInstance =
        expenseCategoryModalEl && ExpenseCategoryModalClass
            ? ExpenseCategoryModalClass.getOrCreateInstance(
                  expenseCategoryModalEl,
              )
            : null;

    // Add Expense Category Form Open
    $(document)
        .off("click", "#btn-add-expense-category")
        .on("click", "#btn-add-expense-category", function () {
            let url = $(this).data("url");
            let title = $(this).data("title");

            $.ajax({
                type: "GET",
                url: url,

                success: function (response) {
                    $("#expense-category-modal-content").html(response);
                    $("#expense-category-modal-content .modal-title").text(
                        title,
                    );
                    expenseCategoryModalInstance?.show();
                },

                error: function () {
                    toastr.error("Could not load form.");
                },
            });
        });

    // Edit Expense Category Form Open
    $(document)
        .off("click", "#expense-categories-table .btn-edit-expense-category")
        .on(
            "click",
            "#expense-categories-table .btn-edit-expense-category",
            function () {
                let url = $(this).data("url");
                let title = $(this).data("title");

                $.ajax({
                    type: "GET",
                    url: url,

                    success: function (response) {
                        $("#expense-category-modal-content").html(response);
                        $("#expense-category-modal-content .modal-title").text(
                            title,
                        );
                        expenseCategoryModalInstance?.show();
                    },

                    error: function () {
                        toastr.error("Could not load form.");
                    },
                });
            },
        );

    // Add/Edit Expense Category Form Submit
    $(document)
        .off("submit", "#expense-category-ajax-form")
        .on("submit", "#expense-category-ajax-form", function (e) {
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

                    expenseCategoryModalInstance?.hide();

                    if (
                        $.fn.DataTable.isDataTable("#expense-categories-table")
                    ) {
                        $("#expense-categories-table")
                            .DataTable()
                            .ajax.reload();
                    } else if (
                        window.LaravelDataTables &&
                        window.LaravelDataTables["expense-categories-table"]
                    ) {
                        window.LaravelDataTables[
                            "expense-categories-table"
                        ].ajax.reload();
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

    // Delete Single Expense Category
    $(document)
        .off("click", "#expense-categories-table .btn-delete-expense-category")
        .on(
            "click",
            "#expense-categories-table .btn-delete-expense-category",
            function () {
                let url = $(this).data("url");

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
                                    toastr.success(
                                        response.message ||
                                            "Deleted successfully.",
                                    );

                                    if (
                                        $.fn.DataTable.isDataTable(
                                            "#expense-categories-table",
                                        )
                                    ) {
                                        $("#expense-categories-table")
                                            .DataTable()
                                            .ajax.reload();
                                    } else if (
                                        window.LaravelDataTables &&
                                        window.LaravelDataTables[
                                            "expense-categories-table"
                                        ]
                                    ) {
                                        window.LaravelDataTables[
                                            "expense-categories-table"
                                        ].ajax.reload();
                                    }
                                },

                                error: function (xhr) {
                                    toastr.error(
                                        xhr.responseJSON?.message ||
                                            "Could not delete category.",
                                    );
                                },
                            });
                        }
                    });
            },
        );

    // Flat Type Modal Variables
    const flatTypeModalEl = document.getElementById("flat-type-modal");
    const FlatTypeModalClass = window.coreui?.Modal || window.bootstrap?.Modal;
    const flatTypeModalInstance =
        flatTypeModalEl && FlatTypeModalClass
            ? FlatTypeModalClass.getOrCreateInstance(flatTypeModalEl)
            : null;

    // Add Flat Type Form Open
    $(document)
        .off("click", "#btn-add-flat-type")
        .on("click", "#btn-add-flat-type", function () {
            let url = $(this).data("url");
            let title = $(this).data("title");

            $.ajax({
                type: "GET",
                url: url,

                success: function (response) {
                    $("#flat-type-modal-content").html(response);
                    $("#flat-type-modal-content .modal-title").text(title);
                    flatTypeModalInstance?.show();
                },

                error: function () {
                    toastr.error("Could not load form.");
                },
            });
        });

    // Edit Flat Type Form Open
    $(document)
        .off("click", "#flat-types-table .btn-edit-flat-type")
        .on("click", "#flat-types-table .btn-edit-flat-type", function () {
            let url = $(this).data("url");
            let title = $(this).data("title");

            $.ajax({
                type: "GET",
                url: url,

                success: function (response) {
                    $("#flat-type-modal-content").html(response);
                    $("#flat-type-modal-content .modal-title").text(title);
                    flatTypeModalInstance?.show();
                },

                error: function () {
                    toastr.error("Could not load form.");
                },
            });
        });

    // Add/Edit Flat Type Form Submit
    $(document)
        .off("submit", "#flat-type-ajax-form")
        .on("submit", "#flat-type-ajax-form", function (e) {
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

                    flatTypeModalInstance?.hide();

                    if ($.fn.DataTable.isDataTable("#flat-types-table")) {
                        $("#flat-types-table").DataTable().ajax.reload();
                    } else if (
                        window.LaravelDataTables &&
                        window.LaravelDataTables["flat-types-table"]
                    ) {
                        window.LaravelDataTables[
                            "flat-types-table"
                        ].ajax.reload();
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

    // Delete Single Flat Type
    $(document)
        .off("click", "#flat-types-table .btn-delete-flat-type")
        .on("click", "#flat-types-table .btn-delete-flat-type", function () {
            let url = $(this).data("url");

            swalWithBootstrapButtons
                .fire({
                    title: "Are you sure?",
                    text: "This flat type will be deleted permanently!",
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
                                        "#flat-types-table",
                                    )
                                ) {
                                    $("#flat-types-table")
                                        .DataTable()
                                        .ajax.reload();
                                } else if (
                                    window.LaravelDataTables &&
                                    window.LaravelDataTables["flat-types-table"]
                                ) {
                                    window.LaravelDataTables[
                                        "flat-types-table"
                                    ].ajax.reload();
                                }
                            },

                            error: function (xhr) {
                                toastr.error(
                                    xhr.responseJSON?.message ||
                                        "Could not delete flat type.",
                                );
                            },
                        });
                    }
                });
        });

    // Maintenance Bill Modal Variables
    const maintenanceBillModalEl = document.getElementById(
        "maintenance-bill-modal",
    );
    const MaintenanceBillModalClass =
        window.coreui?.Modal || window.bootstrap?.Modal;
    const maintenanceBillModalInstance =
        maintenanceBillModalEl && MaintenanceBillModalClass
            ? MaintenanceBillModalClass.getOrCreateInstance(
                  maintenanceBillModalEl,
              )
            : null;

    // Add Maintenance Bill Form Open
    $(document)
        .off("click", "#btn-add-maintenance-bill")
        .on("click", "#btn-add-maintenance-bill", function () {
            let url = $(this).data("url");
            let title = $(this).data("title");

            $.ajax({
                type: "GET",
                url: url,

                success: function (response) {
                    $("#maintenance-bill-modal-content").html(response);
                    $("#maintenance-bill-modal-content .modal-title").text(
                        title,
                    );
                    maintenanceBillModalInstance?.show();
                },

                error: function () {
                    toastr.error("Could not load form.");
                },
            });
        });

    // Edit Maintenance Bill Form Open
    $(document)
        .off("click", ".btn-edit-maintenance-bill")
        .on(
            "click",
            ".btn-edit-maintenance-bill",
            function () {
                let url = $(this).data("url");
                let title = $(this).data("title");

                $.ajax({
                    type: "GET",
                    url: url,

                    success: function (response) {
                        $("#maintenance-bill-modal-content").html(response);
                        $("#maintenance-bill-modal-content .modal-title").text(
                            title,
                        );
                        maintenanceBillModalInstance?.show();
                    },

                    error: function () {
                        toastr.error("Could not load form.");
                    },
                });
            },
        );

    // Auto-fill maintenance amount when flat is selected
    $(document).on(
        "change",
        "#maintenance-bill-ajax-form #flat_id",
        function () {
            let selectedOption = $(this).find("option:selected");
            let fee = selectedOption.data("maintenance-fee");

            if (fee !== undefined && fee !== "") {
                $("#maintenance-bill-ajax-form #amount").val(
                    parseFloat(fee).toFixed(2),
                );
            }
        },
    );

    // Filter flats based on selected block
    $(document).on(
        "change",
        "#maintenance-bill-ajax-form #block_id",
        function () {
            let blockId = $(this).val();
            let flatSelect = $("#maintenance-bill-ajax-form #flat_id");

            // Show/hide options based on data-block-id
            flatSelect.find("option").each(function () {
                let optionBlockId = $(this).data("block-id");
                if (!optionBlockId || optionBlockId == blockId) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });

            // Reset flat selection if current selection is hidden
            if (flatSelect.find("option:selected").css("display") === "none") {
                flatSelect.val("");
                $("#maintenance-bill-ajax-form #amount").val("");
            }
        },
    );

    // Initialize flat filtering if block is already selected (e.g. in Edit mode)
    $(document).on(
        "shown.coreui.modal",
        "#maintenance-bill-modal",
        function () {
            $("#maintenance-bill-ajax-form #block_id").trigger("change");
        },
    );

    // Add/Edit Maintenance Bill Form Submit
    $(document)
        .off("submit", "#maintenance-bill-ajax-form")
        .on("submit", "#maintenance-bill-ajax-form", function (e) {
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

                    maintenanceBillModalInstance?.hide();

                    if (
                        $.fn.DataTable.isDataTable("#maintenance-bills-table")
                    ) {
                        $("#maintenance-bills-table").DataTable().ajax.reload();
                    } else if (
                        window.LaravelDataTables &&
                        window.LaravelDataTables["maintenance-bills-table"]
                    ) {
                        window.LaravelDataTables[
                            "maintenance-bills-table"
                        ].ajax.reload();
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

    // Delete Single Maintenance Bill
    $(document)
        .off("click", "#maintenance-bills-table .btn-delete-maintenance-bill")
        .on(
            "click",
            "#maintenance-bills-table .btn-delete-maintenance-bill",
            function () {
                let url = $(this).data("url");

                swalWithBootstrapButtons
                    .fire({
                        title: "Are you sure?",
                        text: "This bill will be deleted permanently!",
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
                                        response.message ||
                                            "Deleted successfully.",
                                    );

                                    if (
                                        $.fn.DataTable.isDataTable(
                                            "#maintenance-bills-table",
                                        )
                                    ) {
                                        $("#maintenance-bills-table")
                                            .DataTable()
                                            .ajax.reload();
                                    } else if (
                                        window.LaravelDataTables &&
                                        window.LaravelDataTables[
                                            "maintenance-bills-table"
                                        ]
                                    ) {
                                        window.LaravelDataTables[
                                            "maintenance-bills-table"
                                        ].ajax.reload();
                                    }
                                },

                                error: function (xhr) {
                                    toastr.error(
                                        xhr.responseJSON?.message ||
                                            "Could not delete bill.",
                                    );
                                },
                            });
                        }
                    });
            },
        );

    // Delete Individual Maintenance Bill
    $(document)
        .off("click", ".btn-delete-individual-bill")
        .on("click", ".btn-delete-individual-bill", function () {
            let url = $(this).data("url");

            swalWithBootstrapButtons
                .fire({
                    title: "Are you sure?",
                    text: "This resident's bill will be deleted permanently!",
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
                            data: {
                                _token: $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function (response) {
                                toastr.success(response.message || "Deleted successfully.");
                                if (window.LaravelDataTables && window.LaravelDataTables["maintenancedetails-table"]) {
                                    window.LaravelDataTables["maintenancedetails-table"].ajax.reload(null, false);
                                }
                            },
                            error: function (xhr) {
                                toastr.error(xhr.responseJSON?.message || "Could not delete bill.");
                            },
                        });
                    }
                });
        });

    // ==========================================
    // EXTRACTED FROM BLADE TEMPLATES
    // ==========================================

    // --- Dashboard Chart ---
    if (
        document.getElementById("mainChart") &&
        document.getElementById("dashboard-chart-data")
    ) {
        const chartDataEl = document.getElementById("dashboard-chart-data");
        const months = JSON.parse(chartDataEl.getAttribute("data-months"));
        const revenueData = JSON.parse(
            chartDataEl.getAttribute("data-revenue"),
        );
        const expenseData = JSON.parse(
            chartDataEl.getAttribute("data-expenses"),
        );

        Chart.defaults.color =
            getComputedStyle(document.documentElement).getPropertyValue(
                "--cui-body-color",
            ) || "#8a93a2";
        Chart.defaults.scale.grid.color =
            getComputedStyle(document.documentElement).getPropertyValue(
                "--cui-border-color-translucent",
            ) || "rgba(0,0,0,0.1)";

        const mainChartCtx = document
            .getElementById("mainChart")
            .getContext("2d");
            
        let gradientRevenue = mainChartCtx.createLinearGradient(0, 0, 0, 400);
        gradientRevenue.addColorStop(0, 'rgba(99, 102, 241, 0.5)'); // Indigo
        gradientRevenue.addColorStop(1, 'rgba(99, 102, 241, 0.0)');

        let gradientExpense = mainChartCtx.createLinearGradient(0, 0, 0, 400);
        gradientExpense.addColorStop(0, 'rgba(239, 68, 68, 0.5)'); // Red
        gradientExpense.addColorStop(1, 'rgba(239, 68, 68, 0.0)');

        let mainChart = new Chart(mainChartCtx, {
            type: "line",
            data: {
                labels: months,
                datasets: [
                    {
                        label: "Revenue (Paid Bills)",
                        backgroundColor: gradientRevenue,
                        borderColor: "#6366f1",
                        borderWidth: 3,
                        pointBackgroundColor: "#6366f1",
                        pointBorderColor: "#fff",
                        pointHoverBackgroundColor: "#fff",
                        pointHoverBorderColor: "#6366f1",
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        fill: true,
                        tension: 0.4,
                        data: revenueData,
                    },
                    {
                        label: "Society Expenses",
                        backgroundColor: gradientExpense,
                        borderColor: "#ef4444",
                        borderWidth: 3,
                        pointBackgroundColor: "#ef4444",
                        pointBorderColor: "#fff",
                        pointHoverBackgroundColor: "#fff",
                        pointHoverBorderColor: "#ef4444",
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        fill: true,
                        tension: 0.4,
                        data: expenseData,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: { 
                        position: "top",
                        labels: {
                            usePointStyle: true,
                            pointStyle: 'circle',
                            padding: 20
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.9)',
                        titlePadding: 10,
                        bodyPadding: 10,
                        cornerRadius: 8,
                        callbacks: {
                            label: function (context) {
                                let label = context.dataset.label || "";
                                if (label) {
                                    label += ": ";
                                }
                                if (context.parsed.y !== null) {
                                    label += new Intl.NumberFormat("en-IN", {
                                        style: "currency",
                                        currency: "INR",
                                    }).format(context.parsed.y);
                                }
                                return label;
                            },
                        },
                    },
                },
                scales: {
                    y: { 
                        beginAtZero: true,
                        grid: { borderDash: [4, 4] }
                    },
                    x: {
                        grid: { display: false }
                    }
                },
            },
        });

        let statusChart = null;
        if (document.getElementById("statusChart")) {
            const statusChartCtx = document
                .getElementById("statusChart")
                .getContext("2d");
            const statusData = JSON.parse(
                chartDataEl.getAttribute("data-status"),
            );
            statusChart = new Chart(statusChartCtx, {
                type: "doughnut",
                data: {
                    labels: ["Paid", "Pending", "Due"],
                    datasets: [
                        {
                            data: [
                                statusData.paid,
                                statusData.pending,
                                statusData.due,
                            ],
                            backgroundColor: [
                                "#10b981", // Emerald (Paid)
                                "#f59e0b", // Amber (Pending)
                                "#ef4444", // Red (Due)
                            ],
                            borderWidth: 0,
                            hoverOffset: 10,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { 
                            position: "bottom",
                            labels: {
                                padding: 20,
                                usePointStyle: true,
                                pointStyle: 'circle'
                            }
                        },
                    },
                    cutout: "75%",
                    layout: {
                        padding: 10
                    }
                },
            });
        }

        document.documentElement.addEventListener("ColorSchemeChange", () => {
            Chart.defaults.color =
                getComputedStyle(document.documentElement).getPropertyValue(
                    "--cui-body-color",
                ) || "#8a93a2";
            Chart.defaults.scale.grid.color =
                getComputedStyle(document.documentElement).getPropertyValue(
                    "--cui-border-color-translucent",
                ) || "rgba(0,0,0,0.1)";

            if (mainChart) {
                mainChart.update();
            }
            if (statusChart) {
                statusChart.update();
            }
        });
    }

    // --- Resident Type Dropdown Filtering ---
    $(document).on("change", "#resident-type-select", function () {
        const type = $(this).val();
        const userSelect = $("#resident-user-select");
        if (userSelect.length === 0) return;

        userSelect.val("");

        userSelect.find("option").each(function () {
            const role = $(this).attr("data-role");
            if (!role) {
                $(this).show().prop("disabled", false).prop("hidden", false);
                return;
            }

            if (type === "owner" && role === "owner") {
                $(this).show().prop("disabled", false).prop("hidden", false);
            } else if (type === "rental" && role === "rental") {
                $(this).show().prop("disabled", false).prop("hidden", false);
            } else {
                $(this).hide().prop("disabled", true).prop("hidden", true);
            }
        });
    });

    // --- Maintenance Bill User Selection Auto-Fill ---
    $(document).on(
        "change",
        "#maintenance-bill-ajax-form #user_id",
        function () {
            const userId = $(this).val();
            if (!userId) {
                $("#maintenance-bill-ajax-form #block_id").val("");
                $("#maintenance-bill-ajax-form #flat_id").val("");
                $("#maintenance-bill-ajax-form #amount").val("");
                return;
            }

            fetch(`/maintenance-bills/resident-info/${userId}`)
                .then((response) => response.json())
                .then((data) => {
                    if (data.success) {
                        $("#maintenance-bill-ajax-form #block_id").val(
                            data.block_id,
                        );
                        $("#maintenance-bill-ajax-form #flat_id").val(
                            data.flat_id,
                        );
                        $("#maintenance-bill-ajax-form #amount").val(
                            data.amount,
                        );
                    } else {
                        $("#maintenance-bill-ajax-form #block_id").val("");
                        $("#maintenance-bill-ajax-form #flat_id").val("");
                        $("#maintenance-bill-ajax-form #amount").val("");
                    }
                })
                .catch((error) =>
                    console.error("Error fetching resident info:", error),
                );
        },
    );

    // --- Maintenance Bill Status Update Form ---
    $(document).on("change", "#flat-type-filter, #status-filter", function () {
        if (
            window.LaravelDataTables &&
            window.LaravelDataTables["maintenancedetails-table"]
        ) {
            window.LaravelDataTables["maintenancedetails-table"].ajax.reload();
        }
    });

    $(document).on("submit", ".ajax-status-form", function (e) {
        e.preventDefault();
        var form = $(this);
        var url = form.attr("action");
        var formData = form.serialize();
        var submitBtn = form.find('button[type="submit"]');
        var originalText = submitBtn.html();

        submitBtn
            .html('<i class="fa-solid fa-spinner fa-spin"></i>')
            .prop("disabled", true);

        $.ajax({
            type: "POST",
            url: url,
            data: formData,
            success: function (response) {
                if (response.success) {
                    if (
                        window.LaravelDataTables &&
                        window.LaravelDataTables["maintenancedetails-table"]
                    ) {
                        window.LaravelDataTables[
                            "maintenancedetails-table"
                        ].ajax.reload(null, false);
                    }

                    if (
                        response.paidCount !== undefined &&
                        response.totalCount !== undefined
                    ) {
                        $("#paid-count-display").text(
                            response.paidCount + "/" + response.totalCount,
                        );
                    }
                    if (response.totalAmountExpected !== undefined) {
                        $("#total-amount-display").text(
                            "$" + response.totalAmountExpected,
                        );
                    }

                    if (typeof toastr !== "undefined") {
                        toastr.success(response.message);
                    }
                } else {
                    if (typeof toastr !== "undefined") {
                        toastr.error(
                            response.message || "Error updating status",
                        );
                    }
                }
            },
            error: function (xhr) {
                console.error("Error updating status", xhr);
                if (typeof toastr !== "undefined") {
                    toastr.error("Error updating status");
                }
            },
            complete: function () {
                submitBtn.html(originalText).prop("disabled", false);
            },
        });
    });

    // --- Auth Forms Validation ---
    const authToastSource = document.getElementById("users-toast-source");
    if (authToastSource) {
        const message = authToastSource.getAttribute("data-message");
        const type = authToastSource.getAttribute("data-type") || "success";
        if (
            message &&
            window.jQuery &&
            typeof window.showToast === "function"
        ) {
            window.showToast(message, type);
        }
    }

    $(document).on("click", ".toggle-password-btn", function () {
        const input = $(this).closest(".input-group").find("input")[0];
        if (input.type === "password") {
            input.type = "text";
            $(this)
                .attr("aria-label", "Hide password")
                .attr("data-coreui-original-title", "Hide password");
        } else {
            input.type = "password";
            $(this)
                .attr("aria-label", "Show password")
                .attr("data-coreui-original-title", "Show password");
        }
    });

    $(document).on("submit", "#resetPasswordForm", function (e) {
        const emailInput = document.getElementById("email");
        const passwordInput = document.getElementById("new-password");
        const confirmPasswordInput =
            document.getElementById("confirm-password");

        const emailError = document.getElementById("js-email-error");
        const passwordError = document.getElementById("js-password-error");
        const confirmPasswordError = document.getElementById(
            "js-confirm-password-error",
        );

        let isValid = true;

        [emailInput, passwordInput, confirmPasswordInput].forEach((input) => {
            if (!input) return;
            input.classList.remove("is-invalid");
            const bladeError = input.parentElement.querySelector(
                '.invalid-feedback:not([id^="js-"])',
            );
            if (bladeError) bladeError.style.display = "none";
        });

        [emailError, passwordError, confirmPasswordError].forEach((err) => {
            if (err) {
                err.style.display = "none";
                err.textContent = "";
            }
        });

        if (emailInput && !emailInput.value.trim()) {
            isValid = false;
            emailInput.classList.add("is-invalid");
            if (emailError) {
                emailError.textContent = "The email field is required.";
                emailError.style.display = "block";
            }
        } else if (
            emailInput &&
            !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailInput.value)
        ) {
            isValid = false;
            emailInput.classList.add("is-invalid");
            if (emailError) {
                emailError.textContent = "Please enter a valid email address.";
                emailError.style.display = "block";
            }
        }

        if (passwordInput && !passwordInput.value) {
            isValid = false;
            passwordInput.classList.add("is-invalid");
            if (passwordError) {
                passwordError.textContent = "The password field is required.";
                passwordError.style.display = "block";
            }
        } else if (passwordInput && passwordInput.value.length < 5) {
            isValid = false;
            passwordInput.classList.add("is-invalid");
            if (passwordError) {
                passwordError.textContent =
                    "The password must be at least 5 characters.";
                passwordError.style.display = "block";
            }
        }

        if (
            passwordInput &&
            passwordInput.value &&
            passwordInput.value.length >= 5 &&
            confirmPasswordInput
        ) {
            if (!confirmPasswordInput.value) {
                isValid = false;
                confirmPasswordInput.classList.add("is-invalid");
                if (confirmPasswordError) {
                    confirmPasswordError.textContent =
                        "Please confirm your password.";
                    confirmPasswordError.style.display = "block";
                }
            } else if (passwordInput.value !== confirmPasswordInput.value) {
                isValid = false;
                confirmPasswordInput.classList.add("is-invalid");
                if (confirmPasswordError) {
                    confirmPasswordError.textContent =
                        "The password confirmation does not match.";
                    confirmPasswordError.style.display = "block";
                }
            }
        }

        if (!isValid) {
            e.preventDefault();
        }
    });

    // ==========================================
    // Prepayment / Payment Form Logic
    // ==========================================
    if ($('#prepayment-form').length > 0) {
        
        if ($('#resident_id').length > 0 && typeof $.fn.select2 !== 'undefined') {
            $('#resident_id').select2({
                theme: 'bootstrap-5',
                placeholder: "Select Resident",
                width: '100%'
            });
        }

        if (typeof flatpickr !== 'undefined') {
            flatpickr("#start_date", {
                dateFormat: "Y-m-d",
                onChange: function(selectedDates, dateStr, instance) {
                    $('#start_date').val(dateStr);
                    calculatePaymentTotals();
                }
            });
            flatpickr("#end_date", {
                dateFormat: "Y-m-d",
                onChange: function(selectedDates, dateStr, instance) {
                    $('#end_date').val(dateStr);
                    calculatePaymentTotals();
                }
            });
        }

        let currentMonthlyFee = 0;

        function toggleUpiDetails() {
            const paymentMethod = $('#payment_method').val();
            const upiDetails = $('#upi-details');
            const paymentSlip = $('#payment_slip');
            
            if (paymentMethod === 'upi') {
                upiDetails.removeClass('d-none');
                paymentSlip.attr('required', 'required');
            } else {
                upiDetails.addClass('d-none');
                paymentSlip.removeAttr('required');
            }
        }

        $(document).on('change', '#payment_method', toggleUpiDetails);
        toggleUpiDetails();

        function calculatePaymentTotals() {
            const startDateVal = $('#start_date').val();
            const endDateVal = $('#end_date').val();
            
            let months = 0;
            
            if (startDateVal && endDateVal) {
                const start = new Date(startDateVal);
                const end = new Date(endDateVal);
                
                if (end >= start) {
                    const diffTime = Math.abs(end - start);
                    const diffDays = diffTime / (1000 * 60 * 60 * 24);
                    months = Math.round(diffDays / 30.44); // 30.44 is average days in a month
                    if (months < 1) months = 1;
                    
                    const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
                    $('#hidden_start_month').val(monthNames[start.getMonth()]);
                    $('#hidden_start_year').val(start.getFullYear());
                } else {
                    months = 0;
                }
            }
            
            $('#calculated_duration').val(`${months} Month(s)`);
            $('#hidden_months').val(months);

            if (months > 0 && currentMonthlyFee > 0 && window.discountSettings) {
                const subtotal = currentMonthlyFee * months;
                const startDate = startDateVal;
                
                // Calculate split of months
                let pastMonthsCount = 0;
                let futureMonthsCount = 0;
                
                if (startDate) {
                    const start = new Date(startDate);
                    const now = new Date();
                    pastMonthsCount = (now.getFullYear() - start.getFullYear()) * 12 + (now.getMonth() - start.getMonth());
                    
                    if (pastMonthsCount < 0) pastMonthsCount = 0;
                    if (pastMonthsCount > months) pastMonthsCount = months;
                    
                    futureMonthsCount = months - pastMonthsCount;
                } else {
                    futureMonthsCount = months;
                }

                let arrearsAmount = pastMonthsCount * currentMonthlyFee;
                let advanceAmount = futureMonthsCount * currentMonthlyFee;

                // Penalty Calculation (on past months)
                let penaltyValue = 0;
                if (window.penaltySettings && window.penaltySettings.apply_penalty == '1' && pastMonthsCount > 0) {
                    if (pastMonthsCount >= 12 && window.penaltySettings.yearly_value > 0) {
                        penaltyValue = window.penaltySettings.yearly_value;
                    } else if (pastMonthsCount >= 6 && window.penaltySettings.half_yearly_value > 0) {
                        penaltyValue = window.penaltySettings.half_yearly_value;
                    } else if (pastMonthsCount >= 3 && window.penaltySettings.quarterly_value > 0) {
                        penaltyValue = window.penaltySettings.quarterly_value;
                    }
                }

                let penaltyAmount = 0;
                if (penaltyValue > 0) {
                    if (window.penaltySettings.type === 'fixed') {
                        penaltyAmount = parseFloat(penaltyValue);
                    } else {
                        penaltyAmount = arrearsAmount * (parseFloat(penaltyValue) / 100);
                    }
                }

                // Discount Calculation (on future months)
                let discountValue = 0;
                const applyDiscount = window.discountSettings ? window.discountSettings.apply_discount : '0';
                if ((applyDiscount === '1' || applyDiscount === 'true' || applyDiscount === 'on') && futureMonthsCount > 0) {
                    if (futureMonthsCount >= 12 && window.discountSettings.yearly_value > 0) {
                        discountValue = window.discountSettings.yearly_value;
                    } else if (futureMonthsCount >= 6 && window.discountSettings.half_yearly_value > 0) {
                        discountValue = window.discountSettings.half_yearly_value;
                    } else if (futureMonthsCount >= 3 && window.discountSettings.quarterly_value > 0) {
                        discountValue = window.discountSettings.quarterly_value;
                    }
                }
                
                let discountAmount = 0;
                if (discountValue > 0) {
                    if (window.discountSettings.type === 'fixed') {
                        discountAmount = parseFloat(discountValue);
                    } else {
                        discountAmount = advanceAmount * (parseFloat(discountValue) / 100);
                    }
                }

                $('#penalty_amount').val(penaltyAmount.toFixed(2));
                $('#discount_applied').val(discountAmount.toFixed(2));

                let totalAmount = subtotal + penaltyAmount - discountAmount;
                totalAmount = Math.max(0, totalAmount);

                $('#subtotal').val(subtotal.toFixed(2));
                $('#discount_applied').val(discountAmount.toFixed(2));
                $('#total_amount').val(totalAmount.toFixed(2));
                
                if (months > 12) {
                    $('#submit-btn').prop('disabled', true);
                    alert('You can pay for a maximum of 12 months.');
                } else {
                    $('#submit-btn').prop('disabled', false);
                }
            } else {
                $('#subtotal').val('0.00');
                $('#discount_applied').val('0.00');
                
                let manualPenalty = parseFloat($('#penalty_amount').val()) || 0;
                $('#total_amount').val(Math.max(0, manualPenalty).toFixed(2));
                
                // Only disable if no fee is selected or invalid months
                if (months <= 0 || currentMonthlyFee <= 0) {
                    $('#submit-btn').prop('disabled', true);
                } else {
                    $('#submit-btn').prop('disabled', false);
                }
            }
        }

        $(document).on('input', '#discount_applied, #penalty_amount', function() {
            const subtotal = parseFloat($('#subtotal').val()) || 0;
            const discount = parseFloat($('#discount_applied').val()) || 0;
            const penalty = parseFloat($('#penalty_amount').val()) || 0;
            let total = subtotal + penalty - discount;
            total = Math.max(0, total);
            $('#total_amount').val(total.toFixed(2));
        });

        $(document).on('change', '#resident_id', function() {
            const resId = $(this).val();
            
            if (resId && window.residentFees && typeof window.residentFees[resId] !== 'undefined') {
                currentMonthlyFee = parseFloat(window.residentFees[resId]) || 0;
                $('#display_monthly_fee').text(currentMonthlyFee.toFixed(2));
                $('#display_monthly_total').text(currentMonthlyFee.toFixed(2));
                $('#maintenance-fees-section').slideDown();
            } else {
                currentMonthlyFee = 0;
                $('#maintenance-fees-section').slideUp();
            }
            calculatePaymentTotals();
        });

        $(document).on('change input blur', '#start_date, #end_date', calculatePaymentTotals);

        // Trigger on load for pre-filled values
        setTimeout(function() {
            if ($('#resident_id').val()) {
                $('#resident_id').trigger('change');
            } else {
                calculatePaymentTotals();
            }
        }, 100);
    }
});
