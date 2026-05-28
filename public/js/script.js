/**
 * App-wide JavaScript
 * This file handles all AJAX operations for User CRUD.
 * Loaded globally using layout.blade.php
 */

$(function () {

    // Get CSRF token from meta tag for secure Laravel requests
    const csrfToken = $('meta[name="csrf-token"]').attr('content');

    // Toast notification container
    const toastContainer = $('#users-toasts');

    // Modal element for add/edit user form
    const modalEl = document.getElementById('user-modal');

    // Detect CoreUI or Bootstrap modal class
    const ModalClass = window.coreui?.Modal || window.bootstrap?.Modal;

    // Create modal instance
    const modalInstance = modalEl && ModalClass
        ? ModalClass.getOrCreateInstance(modalEl)
        : null;

    // Users table selector
    const usersTable = $('#users-table');

    // Template for action buttons
    const actionsTemplate = $('#users-actions-template');

    // Toast source element
    const toastSource = $('#users-toast-source');

    // Show initial toast message if available
    if (toastSource.length && toastSource.data('message')) {
        showToast(
            String(toastSource.data('message')),
            String(toastSource.data('type') || 'success')
        );
    }



    /**
     * Build AJAX request headers
     * Adds JSON headers and CSRF token
     */
    function ajaxHeaders() {

        const headers = {
            'X-Requested-With': 'XMLHttpRequest',
            Accept: 'application/json',
        };

        // Add CSRF token if available
        if (csrfToken) {
            headers['X-CSRF-TOKEN'] = csrfToken;
        }

        return headers;
    }

    /**
     * Show Toast Notification
     * Used for success/error messages
     */
    function showToast(message, type = 'success') {

        // Return if toast container not found
        if (!toastContainer.length) {
            return;
        }

        // Set icon and title based on type
        const icon = type === 'success' ? '✓' : '!';
        const title = type === 'success' ? 'Success' : 'Error';

        // Create toast element
        const $toast = $(
            '<div class="users-toast ' + type + '" role="alert" aria-live="assertive"></div>'
        );

        // Add icon
        $toast.append('<span class="users-toast-icon">' + icon + '</span>');

        // Add content wrapper
        $toast.append(
            '<div class="users-toast-content">' +
                '<div class="users-toast-title"></div>' +
                '<p class="users-toast-message"></p>' +
            '</div>'
        );

        // Set title and message text
        $toast.find('.users-toast-title').text(title);
        $toast.find('.users-toast-message').text(message);

        // Append toast into container
        toastContainer.append($toast);

        // Show animation
        requestAnimationFrame(function () {
            $toast.addClass('show');
        });

        // Auto remove toast after 3.2 seconds
        setTimeout(function () {

            $toast.removeClass('show');

            setTimeout(function () {
                $toast.remove();
            }, 220);

        }, 3200);
    }

    /**
     * Show Validation Errors
     * Display errors inside modal alert box
     */
    function showFormErrors(errors) {

        const errorBox = $('#user-form-errors');

        // Return if error box not found
        if (!errorBox.length) {
            return;
        }

        // Clear previous errors
        errorBox.empty().addClass('d-none');

        // Return if no errors
        if (!errors || !Object.keys(errors).length) {
            return;
        }

        // Build error list
        const errorList = Object.values(errors)
            .map(function (value) {
                return '<li>' + value[0] + '</li>';
            })
            .join('');

        // Show errors
        errorBox
            .removeClass('d-none')
            .html('<ul class="mb-0">' + errorList + '</ul>');
    }

    /**
     * Remove inline field errors
     */
    function clearFieldErrors($form) {

        // Remove error messages
        $form.find('.field-error').remove();

        // Remove invalid class
        $form.find('.is-invalid').removeClass('is-invalid');
    }

    /**
     * Show Inline Field Errors
     * Display error below each field
     */
    function showFieldErrors($form, errors) {

        // Clear previous errors first
        clearFieldErrors($form);

        // Return if no errors
        if (!errors || !Object.keys(errors).length) {
            return;
        }

        // Loop through validation errors
        Object.keys(errors).forEach(function (fieldName) {

            // Get error message
            const message = Array.isArray(errors[fieldName])
                ? errors[fieldName][0]
                : errors[fieldName];

            // Find input field
            const $field = $form.find('[name="' + fieldName + '"]');

            // Skip if field not found
            if (!$field.length) {
                return;
            }

            // Add invalid class
            $field.addClass('is-invalid');

            // Insert error message below field
            $('<div class="invalid-feedback d-block field-error"></div>')
                .text(message)
                .insertAfter($field);
        });
    }

    /**
     * Open User Form in Modal
     * Used for Add/Edit user
     */
    function openUserForm(url, title) {

        $.ajax({

            // Request type
            type: 'GET',

            // URL for form
            url: url,

            // AJAX headers
            headers: ajaxHeaders(),

            // Success callback
            success: function (response) {

                // Load form HTML into modal
                $('#user-modal-content').html(response);

                // Get form
                const $form = $('#user-ajax-form');

                // Clear errors
                clearFieldErrors($form);
                showFormErrors({});

                // Set modal title
                $('#user-modal-content .modal-title').text(title);

                // Show modal
                modalInstance?.show();
            },

            // Error callback
            error: function () {
                showToast('Could not load form.', 'danger');
            },
        });
    }

    /**
     * Reload Yajra DataTable after create/update/delete.
     */
    function reloadUsersTable() {

        if ($.fn.DataTable.isDataTable(usersTable)) {
            usersTable.DataTable().ajax.reload(null, false);
        }
    }

    /**
     * Apply server-side filters for Role + Status (exact stored values)
     * DataTable columns order (0-based):
     * 0=id(computed), 1=name, 2=email, 3=phone, 4=role, 5=status, 6=created_at, 7=action
     */
    function applyRoleStatusFilters() {

        if (!$.fn.DataTable.isDataTable(usersTable)) {
            return;
        }

        const dt = usersTable.DataTable();

        const roleValue = String($('#users-filter-role').val() || '').trim().toLowerCase();
        const statusValue = String($('#users-filter-status').val() || '').trim().toLowerCase();

        dt.column(4).search(roleValue);
        dt.column(5).search(statusValue);

        dt.draw();
    }

    // Filter change handlers
    $(document).on('change', '#users-filter-role, #users-filter-status', function () {

        // Apply filters after DataTable is initialized
        if ($.fn.DataTable.isDataTable(usersTable)) {
            applyRoleStatusFilters();
            return;
        }

        setTimeout(applyRoleStatusFilters, 200);
    });

    $(document).on('click', '#users-filter-reset', function () {

        $('#users-filter-role').val('');
        $('#users-filter-status').val('');

        applyRoleStatusFilters();
    });

    /**
     * Close modal when dismiss button clicked
     */

    $(document).on(
        'click',
        '#user-modal [data-coreui-dismiss="modal"]',
        function () {
            modalInstance?.hide();
        }
    );

    /**
     * Open Add User Form
     */
    $(document).on('click', '#btn-add-user', function () {

        openUserForm(
            $(this).data('url'),
            $(this).data('title')
        );
    });

    /**
     * Open Edit User Form
     */
    $(document).on('click', '#users-table .btn-edit-user', function () {

        openUserForm(
            $(this).data('url'),
            $(this).data('title')
        );
    });

    /**
     * Submit Add/Edit User Form using AJAX
     */
    $(document).on('submit', '#user-ajax-form', function (e) {

        // Prevent normal form submit
        e.preventDefault();

        const $form = $(this);

        // Clear old errors
        clearFieldErrors($form);
        showFormErrors({});

        // Get form data
        const formData = new FormData(this);

        // Get request type
        const requestType = $(this).attr('method') || 'POST';

        $.ajax({

            // Form action URL
            url: $(this).attr('action'),

            // Request method
            type: requestType,

            // Form data
            data: formData,

            // Required for FormData
            processData: false,
            contentType: false,

            // Headers
            headers: ajaxHeaders(),

            // Success callback
            success: function (response) {

                // Clear errors
                clearFieldErrors($form);

                // Close modal
                modalInstance?.hide();

                // Reload DataTable
                reloadUsersTable();

                // Show success toast
                showToast(
                    response.message || 'Saved successfully.'
                );
            },

            // Error callback
            error: function (xhr) {

                // Handle validation errors
                if (
                    xhr.status === 422 &&
                    xhr.responseJSON?.errors
                ) {
                    showFieldErrors(
                        $form,
                        xhr.responseJSON.errors
                    );
                    return;
                }

                // Show generic error
                showToast(
                    xhr.responseJSON?.message ||
                    'Something went wrong.',
                    'danger'
                );
            },
        });
    });

    /**
     * Delete User
     */
    $(document).on(
        'click',
        '#users-table .btn-delete-user',
        function () {

            // Get delete URL
            const deleteUrl = $(this).data('url');

            // SweetAlert confirmation
            Swal.fire({

                title: 'Delete user?',
                text: 'This action cannot be undone.',
                icon: 'warning',

                showCancelButton: true,

                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',

                confirmButtonText: 'Yes, delete it',

                reverseButtons: true,

            }).then(function (result) {

                // Stop if cancelled
                if (!result.isConfirmed) {
                    return;
                }

                // Send AJAX delete request
                $.ajax({

                    url: deleteUrl,

                    method: 'DELETE',

                    headers: ajaxHeaders(),

                    // Success callback
                    success: function (response) {

                        // Reload DataTable
                        reloadUsersTable();

                        // Show success message
                        showToast(
                            response.message ||
                            'Deleted successfully.'
                        );
                    },

                    // Error callback
                    error: function (xhr) {

                        showToast(
                            xhr.responseJSON?.message ||
                            'Could not delete user.',
                            'danger'
                        );
                    },
                });
            });
        }
    );
});
