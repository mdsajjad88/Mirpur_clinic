@extends('clinic::layouts.app2')

@section('title', __('crm::lang.sms_send'))

@section('content')
    @include('crm::layouts.nav')

    <section class="content-header no-print">
        <h1>@lang('crm::lang.sms_send')</h1>
    </section>

    <section class="content no-print">
        @component('components.widget', ['class' => 'box-solid'])
            {!! Form::open(['url' => route('crm.sms.send'), 'method' => 'post', 'id' => 'sms_form']) !!}

            <div class="row">
                <!-- Left Column - Contact Selection -->
                <div class="col-md-6">
                    <div class="form-group manual-block">
                        {!! Form::label('contact_ids', __('contact.contacts') . ':') !!}
                        {!! Form::select('contact_ids[]', [], null, [
                            'class' => 'form-control select2',
                            'id' => 'contact_ids',
                            'multiple' => 'multiple',
                            'data-placeholder' => __('crm::lang.select_contacts'),
                            'style' => 'width: 100%;',
                        ]) !!}
                    </div>

                    <div class="form-group">
                        <button type="button" class="btn btn-info" id="upload_csv">
                            <i class="fa fa-upload"></i> @lang('crm::lang.upload_csv')
                        </button>
                        <button type="button" class="btn btn-warning" id="reset_selection">
                            <i class="fa fa-refresh"></i> @lang('crm::lang.reset_selection')
                        </button>
                    </div>

                    <div class="form-group" id="csv_upload_section" style="display: none;">
                        {!! Form::label('csv_file', __('crm::lang.csv_file') . ':') !!}
                        {!! Form::file('csv_file', [
                            'class' => 'form-control',
                            'id' => 'csv_file',
                            'accept' => '.csv',
                        ]) !!}

                        <small class="help-block" style="color:#555;">
                            <strong>CSV Format:</strong> File must contain exactly 2 columns with headers:<br>
                            <code>name,phone</code><br>
                            Example:<br>
                            <code>
                                John Doe,1234567890<br>
                                Jane Smith,9876543210
                            </code>
                        </small>

                        <button type="button" class="btn btn-success mt-2" id="process_csv">
                            <i class="fa fa-upload"></i> @lang('crm::lang.process_csv')
                        </button>
                    </div>

                    <div id="csv_results" style="display: none; margin-top: 20px;">
                        <h4>@lang('crm::lang.csv_import_results')</h4>
                        <div id="csv_contacts_list" class="well"></div>
                    </div>
                </div>

                <!-- Right Column - SMS Composition -->
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('sms_body', __('crm::lang.sms_body') . ':') !!}
                        {!! Form::textarea('sms_body', null, [
                            'class' => 'form-control',
                            'id' => 'sms_body',
                            'rows' => 8,
                            'placeholder' => __('crm::lang.sms_body_placeholder'),
                        ]) !!}

                        <div class="row mt-2">
                            <div class="col-md-6">
                                <span id="char_count">0</span> @lang('crm::lang.characters')
                            </div>
                            <div class="col-md-6 text-right">
                                <span id="sms_count">0</span> @lang('crm::lang.sms')
                            </div>
                        </div>
                    </div>

                    <div class="form-group text-right">
                        <button type="submit" class="btn btn-primary" id="send_sms">
                            <i class="fa fa-paper-plane"></i> @lang('crm::lang.send_sms')
                        </button>
                        <button type="button" class="btn btn-default" id="reset_sms">
                            <i class="fa fa-eraser"></i> @lang('crm::lang.reset_sms')
                        </button>
                    </div>
                </div>
            </div>

            {!! Form::close() !!}

            <!-- Results Modal -->
            <div class="modal fade" id="sms_results_modal" tabindex="-1" role="dialog">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <h4 class="modal-title">@lang('crm::lang.sms_send_results')</h4>
                        </div>
                        <div class="modal-body">
                            <div id="sms_results_content"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
                        </div>
                    </div>
                </div>
            </div>
        @endcomponent
    </section>
@endsection

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {

            // Initialize select2 with AJAX search
            $('#contact_ids').select2({
                ajax: {
                    url: '/get/clinic/customer',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term,
                            page: params.page
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: $.map(data, function(item) {
                                return {
                                    text: item.text,
                                    id: item.id
                                }
                            })
                        };
                    },
                    cache: true
                },
                minimumInputLength: 1
            });

            // Character and SMS count
            $('#sms_body').on('input', function() {
                var text = $(this).val();
                var charCount = text.length;
                var smsCount = calculateSmsCount(text);

                $('#char_count').text(charCount);
                $('#sms_count').text(smsCount);
            });

            function calculateSmsCount(text) {
                // Detect Unicode (if text contains any non-GSM characters)
                const gsmRegex = /^[\x00-\x7F]*$/; // simple check for ASCII
                const isUnicode = !gsmRegex.test(text);

                let limit, concatLimit;
                if (isUnicode) {
                    limit = 70;
                    concatLimit = 67;
                } else {
                    limit = 160;
                    concatLimit = 153;
                }

                const length = text.length;

                if (length <= limit) {
                    return 1;
                } else {
                    return Math.ceil(length / concatLimit);
                }
            }

            // Toggle CSV upload section
            $('#upload_csv').click(function() {
                $('#csv_upload_section').toggle();
            });

            // Reset selection button
            $('#reset_selection').click(function() {
                $('#contact_ids').val(null).trigger('change');
                $('#csv_upload_section').hide();
                $('#csv_results').hide();
                $('#csv_contacts_list').html('');
                $('#csv_file').val('');
            });

            // Reset SMS button
            $('#reset_sms').click(function() {
                $('#sms_body').val('');
                $('#char_count').text('0');
                $('#sms_count').text('0');
            });

            // Process CSV
            $('#process_csv').click(function() {
                var fileInput = document.getElementById('csv_file');
                if (!fileInput.files.length) {
                    toastr.error('Please select a CSV file');
                    return;
                }

                var formData = new FormData();
                formData.append('csv_file', fileInput.files[0]);
                formData.append('_token', '{{ csrf_token() }}');

                $.ajax({
                    url: '{{ route('crm.sms.process_csv') }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            // Display CSV results
                            var html = '';
                            response.contacts.forEach(function(contact) {
                                html += '<div>' + contact.name + ' - ' + contact.mobile;
                                if (contact.is_new) {
                                    html +=
                                        ' <span class="label label-success">New</span>';
                                }
                                html += '</div>';
                            });

                            $('#csv_contacts_list').html(html);
                            $('#csv_results').show();

                            // Add contacts to selection
                            var contactIds = response.contacts.map(function(c) {
                                return c.id;
                            });
                            $('#contact_ids').val(contactIds).trigger('change');

                            if (response.errors.length > 0) {
                                response.errors.forEach(function(error) {
                                    toastr.warning(error);
                                });
                            }
                        } else {
                            swal({
                                title: response.msg,
                                icon: "warning",
                                dangerMode: true,
                            });
                        }
                    },
                    error: function() {
                        toastr.error('Something went wrong');
                    }
                });
            });

            // Send SMS form submission
            $('#sms_form').submit(function(e) {
                e.preventDefault();
                
                var contactIds = $('#contact_ids').val();
                var smsBody = $('#sms_body').val();

                if (!contactIds || contactIds.length === 0) {
                    toastr.error('Please select at least one contact');
                    return;
                }

                if (!smsBody) {
                    toastr.error('Please enter SMS message');
                    return;
                }

                var formData = {
                    sms_body: smsBody,
                    _token: '{{ csrf_token() }}'
                };

                formData.contact_ids = contactIds;

                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            // Display results
                            var html = '<div class="alert alert-info">' + response.msg + '</div>';
                            html += '<table class="table table-bordered">';
                            html += '<thead><tr><th>Contact</th><th>Mobile</th><th>Status</th><th>Message</th></tr></thead>';
                            html += '<tbody>';

                            response.results.forEach(function(result) {
                                var statusClass = result.status === 'success' ? 'success' : 'danger';
                                html += '<tr class="' + statusClass + '">';
                                html += '<td>' + result.contact + '</td>';
                                html += '<td>' + result.mobile + '</td>';
                                html += '<td>' + result.status + '</td>';
                                html += '<td>' + result.message + '</td>';
                                html += '</tr>';
                            });

                            html += '</tbody></table>';

                            $('#sms_results_content').html(html);
                            $('#sms_results_modal').modal('show');

                            // Reset the form
                            $('#sms_body').val('');
                            $('#char_count').text('0');
                            $('#sms_count').text('0');
                        } else {
                            toastr.error(response.msg);
                        }
                    },
                    error: function() {
                        toastr.error('Something went wrong');
                    }
                });
            });
        });
    </script>
@endsection