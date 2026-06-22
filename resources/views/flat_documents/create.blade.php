<div class="modal-header">
    <h5 class="modal-title text-white" id="addDocumentModalLabel">Upload Flat Documents</h5>
    <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
</div>
<form id="addDocumentForm" class="add-document-form" action="{{ route('flat-documents.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="modal-body">
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="block_id" class="form-label text-white">Block <span class="text-danger">*</span></label>
                <select name="block_id" id="block_id" class="form-select select2" required style="width: 100%;">
                    <option value="">Select Block</option>
                    @foreach ($blocks as $block)
                        <option value="{{ $block->id }}">{{ $block->block_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label for="flat_id" class="form-label text-white">Flat <span class="text-danger">*</span></label>
                <select name="flat_id" id="flat_id" class="form-select select2" required style="width: 100%;">
                    <option value="">Select Flat</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="user_id" class="form-label text-white">Resident <span class="text-danger">*</span></label>
                <select name="user_id" id="user_id" class="form-select select2" required style="width: 100%;">
                    <option value="">Select Resident</option>
                </select>
                <input type="hidden" name="resident_type" id="resident_type" value="">
            </div>
        </div>

        <div class="row mb-3 d-none" id="resident_info_container">
            <div class="col-md-6">
                <label class="form-label text-white small">Contact Number</label>
                <input type="text" class="form-control bg-white text-dark" id="resident_phone" readonly>
            </div>
            <div class="col-md-6">
                <label class="form-label text-white small">Email Address</label>
                <input type="text" class="form-control bg-white text-dark" id="resident_email" readonly>
            </div>
        </div>

        <hr>
        <h6 class="fw-bold mb-3 text-white">Required Documents</h6>
        <div id="dynamic_documents_container">
            <p class="text-light small">Please select a resident to view required documents.</p>
        </div>

    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary" id="submitBtn" disabled>Upload Documents</button>
    </div>
</form>

<script>
    // Pass settings to JS
    window.appSettings = @json($settings);
    window.documentRequirements = @json($documentRequirements);

    $(document).ready(function() {
        $('.select2').select2({
            dropdownParent: $('#addDocumentModal')
        });

        // 1. Block change -> Load Flats
        $('#block_id').on('change', function() {
            var blockId = $(this).val();
            $('#flat_id').html('<option value="">Select Flat</option>');
            $('#user_id').html('<option value="">Select Resident</option>');
            resetResidentInfo();

            if (blockId) {
                $.ajax({
                    url: '/api/flats-by-block/' + blockId,
                    type: 'GET',
                    success: function(data) {
                        $.each(data, function(key, flat) {
                            $('#flat_id').append('<option value="' + flat.id +
                                '">' + flat.flat_no + '</option>');
                        });
                    }
                });
            }
        });

        // 2. Flat change -> Load Residents (Users)
        $('#flat_id').on('change', function() {
            var flatId = $(this).val();
            $('#user_id').html('<option value="">Select Resident</option>');
            resetResidentInfo();

            if (flatId) {
                $.ajax({
                    url: '/api/flat-users/' + flatId,
                    type: 'GET',
                    success: function(data) {
                        $.each(data, function(key, user) {
                            var typeLabel = user.resident_type === 'owner' ?
                                'Owner' : 'Tenant';
                            $('#user_id').append('<option value="' + user.id +
                                '" data-phone="' + (user.phone || 'N/A') +
                                '" data-email="' + (user.email || 'N/A') +
                                '" data-type="' + user.resident_type + '">' +
                                user.name + ' (' + typeLabel + ')</option>');
                        });
                    }
                });
            }
        });

        // 3. User change -> Load Contact Info & Document Inputs
        $('#user_id').on('change', function() {
            var selected = $(this).find('option:selected');
            var userId = selected.val();

            if (userId) {
                var phone = selected.data('phone');
                var email = selected.data('email');
                var type = selected.data('type');

                $('#resident_phone').val(phone);
                $('#resident_email').val(email);
                $('#resident_type').val(type);
                $('#resident_info_container').removeClass('d-none');

                generateDocumentInputs(type);
                $('#submitBtn').prop('disabled', false);
            } else {
                resetResidentInfo();
            }
        });

        function resetResidentInfo() {
            $('#resident_info_container').addClass('d-none');
            $('#resident_phone').val('');
            $('#resident_email').val('');
            $('#resident_type').val('');
            $('#dynamic_documents_container').html(
                '<p class="text-muted small">Please select a resident to view required documents.</p>');
            $('#submitBtn').prop('disabled', true);
        }

        function generateDocumentInputs(residentType) {
            var container = $('#dynamic_documents_container');
            container.empty();

            var docs = window.documentRequirements[residentType] || {};

            var hasRequiredDocs = false;

            $.each(docs, function(key, label) {
                var settingKey = 'req_doc_' + residentType + '_' + key;

                // If setting is enabled
                if (window.appSettings && window.appSettings[settingKey] == '1') {
                    hasRequiredDocs = true;
                    var html = `
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-white">${label} <span class="text-danger">*</span></label>
                        <input type="file" class="form-control bg-white text-dark" name="${settingKey}" required accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                    </div>
                    `;
                    container.append(html);
                }
            });

            if (!hasRequiredDocs) {
                container.html(
                    '<p class="text-light small">No documents are required for this resident type based on global settings.</p>'
                );
                $('#submitBtn').prop('disabled', true);
            }
        }
    });
</script>
