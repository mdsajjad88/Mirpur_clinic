@section('javascript')
    <script>
        $(document).ready(function() {
            $('#doctorTab a:first').tab('show');
        });
        $(document).on('click', '.add_degree', function(e) {
            e.preventDefault();
            $('div.add_degree_form').load($(this).attr('href'), function() {
                $(this).modal('show');
            });
        });
        $(document).on('click', '.add_specilities', function(e) {
            e.preventDefault();
            $('div.add_specilities_form').load($(this).attr('href'), function() {
                $(this).modal('show');
            });
        });
        $(document).on('click', '.edit_profile', function(e) {
            e.preventDefault();
            $('div.edit_profile_form').load($(this).attr('href'), function() {
                $(this).modal('show');
            });
        });
        $(document).on('click', '.edit_degree_button', function(e) {
            e.preventDefault();
            $('div.edit_degree_modal').load($(this).attr('href'), function() {
                $(this).modal('show');
            });
        });
        $(document).on('click', '.edit_specilities_button', function(e) {
            e.preventDefault();
            $('div.edit_specilities_form').load($(this).attr('href'), function() {
                $(this).modal('show');
            });
        });
        $(document).on('click', '.edit_business_day', function(e) {
            e.preventDefault();
            $('div.edit_business_day_modal').load($(this).attr('href'), function() {
                $(this).modal('show');
            });
        });
        $(document).on('click', '.create_business_day', function(e) {
            e.preventDefault();
            $('div.create_business_day_modal').load($(this).attr('href'), function() {
                $(this).modal('show');
            });
        });

        $(document).on('click', '.monthly_sloot_generate_btn', function(e) {
            e.preventDefault();
            $('div.monthly_sloot_generate').load($(this).attr('href'), function() {
                $(this).modal('show');
            });
        });
        $(document).on('click', '.view_monthly_slot', function(e) {
            e.preventDefault();
            $('div.view_monthly_slot_data').load($(this).attr('href'), function() {
                $(this).modal('show');
            });
        });
        $(document).on('click', '.daily_sloot_generate', function(e) {
            e.preventDefault();

            // Load modal content dynamically
            $('div.view_daily_slot_data').load($(this).attr('href'), function() {

                // Show the modal
                $(this).modal('show');

                // Initialize Select2 inside modal AFTER it's visible
                $(this).on('shown.bs.modal', function() {
                    $('.select2').select2({
                        width: '100%',
                        dropdownParent: $(
                            '.view_daily_slot_data') // The actual modal container
                    });
                });
            });
        });
        $(document).on('click', '.daily_sloot_view_btn', function(e) {
            e.preventDefault();
            $('div.view_daily_slot_details').load($(this).attr('href'), function() {
                $(this).modal('show');
            });
        });


        $(document).on('click', '.delete_degree_button', function(e) {
            e.preventDefault();
            var href = $(this).attr('href'); // Get the href from the clicked button

            swal({
                title: LANG.sure, // Make sure LANG.sure is defined
                icon: 'warning',
                buttons: true,
                dangerMode: true,
            }).then((willDelete) => {
                if (willDelete) {
                    $.ajax({
                        method: 'DELETE',
                        url: href, // Use the href directly for the AJAX request
                        dataType: 'json',
                        success: function(result) {
                            if (result.success == true) {
                                toastr.success(result.msg); // Show success message
                                $("#degree_table").DataTable().ajax
                                    .reload(); // Reload the page or update the table dynamically
                            } else {
                                toastr.error(result.msg); // Show error message
                            }
                        },
                        error: function(xhr, status, error) {
                            toastr.error("An error occurred: " + xhr
                                .responseText); // Handle AJAX errors
                        }
                    });
                }
            });
        });
        $(document).on('click', '.delete_specilities_button', function(e) {
            e.preventDefault();
            var href = $(this).attr('href'); // Get the href from the clicked button

            swal({
                title: LANG.sure, // Make sure LANG.sure is defined
                icon: 'warning',
                buttons: true,
                dangerMode: true,
            }).then((willDelete) => {
                if (willDelete) {
                    $.ajax({
                        method: 'DELETE',
                        url: href, // Use the href directly for the AJAX request
                        dataType: 'json',
                        success: function(result) {
                            if (result.success == true) {
                                toastr.success(result.msg); // Show success message
                                $("#specilities_table").DataTable().ajax
                                    .reload(); // Reload the page or update the table dynamically
                            } else {
                                toastr.error(result.msg); // Show error message
                            }
                        },
                        error: function(xhr, status, error) {
                            toastr.error("An error occurred: " + xhr
                                .responseText); // Handle AJAX errors
                        }
                    });
                }
            });
        });
        $(document).on('shown.bs.modal', '.edit_degree_modal', function(e) {
            $('.certification-date-picker').datepicker({
                autoclose: true,
                endDate: 'today',
            });
        });
        var doctorId = {{ $doctor->id }}; // Assuming you have the doctor ID available
        var degree_table = $('#degree_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('get.doctor.degrees', ':id') }}'.replace(':id',
                    doctorId), // Use the named route here
                type: 'GET'
            },
            columns: [{
                    data: 'degree_name',
                    name: 'degree_name'
                }, // Adjust the column name according to your data
                {
                    data: 'degree_short_name',
                    name: 'degree_short_name'
                },
                {
                    data: 'certification_place',
                    name: 'certification_place'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                } // 'action' column
            ],
            paging: false,
            searching: false,
            info: true,
            ordering: false,
        });
        var degree_table = $('#specilities_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('get.doctor.specilities', ':id') }}'.replace(':id',
                    doctorId), // Use the named route here
                type: 'GET'
            },
            columns: [{
                    data: 'term_name',
                    name: 'term_name'
                },
                {
                    data: 'certifications',
                    name: 'certifications'
                },
                {
                    data: 'year_of_experience',
                    name: 'year_of_experience'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                } // 'action' column
            ],
            paging: false,
            searching: false,
            info: true,
            ordering: false,
        });
        var daily_slot_table = $('#daily_slot_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('doctors.getDailySlootData', ':id') }}'.replace(':id',
                    doctorId), // Use the named route here
                type: 'GET'
            },
            columns: [{
                    data: 'calendar_date',
                    name: 'calendar_date'
                },
                {
                    data: 'total_capacity',
                    name: 'total_capacity'
                },
                {
                    data: 'total_reserved',
                    name: 'total_reserved'
                },
                {
                    data: 'total_available',
                    name: 'total_available'
                },
                {
                    data: 'action',
                    name: 'action'
                },

            ],
            paging: false,
            searching: false,
            info: true,
            ordering: true,
        });
        var daily_slot_table = $('#monthly_slot_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('doctors.getMonthlySlootData', ':id') }}'.replace(':id',
                    doctorId), // Use the named route here
                type: 'GET'
            },
            columns: [{
                    data: 'month',
                    name: 'month'
                },
                {
                    data: 'total_slots',
                    name: 'total_slots'
                },
                {
                    data: 'capacity',
                    name: 'capacity'
                },
                {
                    data: 'reserved',
                    name: 'reserved'
                },
                {
                    data: 'available',
                    name: 'available'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                }
            ],
            paging: false,
            searching: false,
            info: true,
            ordering: true,
        });
        $(document).on('click', '.daily_sloot_delete', function(e) {
            e.preventDefault();
            var href = $(this).attr('href'); // Get the href from the clicked button

            swal({
                title: LANG.sure, // Make sure LANG.sure is defined
                icon: 'warning',
                buttons: true,
                dangerMode: true,
            }).then((willDelete) => {
                if (willDelete) {
                    $.ajax({
                        method: 'DELETE',
                        url: href, // Use the href directly for the AJAX request
                        dataType: 'json',
                        success: function(result) {
                            if (result.success == true) {
                                toastr.success(result.msg); // Show success message
                                $("#daily_slot_table").DataTable().ajax
                                    .reload(); // Reload the page or update the table dynamically
                            } else {
                                toastr.error(result.msg); // Show error message
                            }
                        },
                        error: function(xhr, status, error) {
                            toastr.error("An error occurred: " + xhr
                                .responseText); // Handle AJAX errors
                        }
                    });
                }
            });
        });
        $(document).on('click', '.delete_monthly_slot', function(e) {
            e.preventDefault();
            var href = $(this).attr('href'); // Get the href from the clicked button

            swal({
                title: LANG.sure, // Make sure LANG.sure is defined
                icon: 'warning',
                buttons: true,
                dangerMode: true,
            }).then((willDelete) => {
                if (willDelete) {
                    $.ajax({
                        method: 'DELETE',
                        url: href, // Use the href directly for the AJAX request
                        dataType: 'json',
                        success: function(result) {
                            if (result.success == true) {
                                toastr.success(result.msg); // Show success message
                                $("#monthly_slot_table").DataTable().ajax
                                    .reload(); // Reload the page or update the table dynamically
                            } else {
                                toastr.error(result.msg); // Show error message
                            }
                        },
                        error: function(xhr, status, error) {
                            toastr.error("An error occurred: " + xhr
                                .responseText); // Handle AJAX errors
                        }
                    });
                }
            });
        });
        $(document).on('click', '.delete_business_day', function(e) {
            e.preventDefault();
            var href = $(this).attr('href'); // Get the href from the clicked button
            var businessDayRow = $(this).closest('.row'); // Get the closest row containing the business day

            swal({
                title: LANG.sure, // Ensure LANG.sure is defined
                icon: 'warning',
                buttons: true,
                dangerMode: true,
            }).then((willDelete) => {
                if (willDelete) {
                    $.ajax({
                        method: 'DELETE',
                        url: href, // Use the href directly for the AJAX request
                        dataType: 'json',
                        success: function(result) {

                            if (result.success) {
                                toastr.success(result.msg); // Show success message
                                businessDayRow.remove(); // Remove the row from the DOM
                            } else {
                                toastr.error(result.msg); // Show error message
                            }
                        },
                        error: function(xhr) {
                            toastr.error("An error occurred: " + (xhr.responseJSON?.message ||
                                xhr.statusText)); // Handle AJAX errors
                        }
                    });
                }
            });
        });

        $(document).ready(function() {
            $('#business_days_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '/provider/' + doctorId + '/business-days',
                columns: [{
                        data: 'business_operating_hours',
                        name: 'business_operating_hours',
                        orderable: false
                    },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
                dom: 'lrtip'

            });
            $('.dataTables_length').hide();
            $('.dataTables_filter').hide();
            $('.dataTables_info').hide();
            $('.dataTables_paginate').hide();
            $('.dt-buttons').hide();

            $(document).on('submit', '#doctor_business_day_update_form', function(e) {
                e.preventDefault(); // Prevent traditional form submission

                let form = $(this);
                let url = form.attr('action');

                $.ajax({
                    type: "PUT",
                    url: url,
                    data: form.serialize(),
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.msg);
                            $('.edit_business_day_modal').modal('hide'); // Close modal

                            // Reload the business_days_table
                            $('#business_days_table').DataTable().ajax.reload(null,
                                false); // false to keep the current pagination
                        } else {
                            toastr.error(response.msg);
                        }
                    },
                    error: function(xhr) {
                        toastr.error('Something went wrong. Please try again.');
                    }
                });
            });


            // // here to start doctor duty time break
            $('#add_new_break').click(function() {
                let rowIndex = $('#doctors_duty_time_break_table tbody tr').length;
                const newRow = `
                    <tr>
                        <td><input type="text" name="breaks[${rowIndex}][break_type]" class="form-control" placeholder="Break Type"></td>
                        <td><input type="time" name="breaks[${rowIndex}][start_time]" class="form-control"></td>
                        <td><input type="time" name="breaks[${rowIndex}][end_time]" class="form-control"></td>
                        <td><button type="button" class="btn btn-danger remove-break-type-row"><i class="fa fa-trash"></i></button></td>
                    </tr>
                `;
                $('#doctors_duty_time_break_table tbody').append(newRow);
            });
            $(document).on('click', '.remove-break-type-row', function() {
                $(this).closest('tr').remove();
                reIndexRowsBreakType();
            });

            function reIndexRowsBreakType() {
                $('#doctors_duty_time_break_table tbody tr').each(function(index) {
                    $(this).find('input, select').each(function() {
                        let name = $(this).attr('name');
                        name = name.replace(/\[\d+\]/, `[${index}]`);
                        $(this).attr('name', name);
                    });
                });
            }


            // here to submit doctor duty time break
            $(document).on('submit', '#business_day_break_form', function(e) {
                e.preventDefault(); // Prevent traditional form submission

                let form = $(this);
                let url = form.attr('action');

                $.ajax({
                    type: "PUT",
                    url: url,
                    data: form.serialize(),
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.msg);
                            location.reload();
                        } else {
                            toastr.error(response.msg);
                        }
                    },
                    error: function(xhr) {
                        toastr.error('Something went wrong. Please try again.');
                    }
                });

            });
        });
        $(document).ready(function() {
            
            let doctorId = "{{ $doctor->id }}"; // Laravel blade variable
            let isAvailable = {{ $doctor->is_available }}; // 0 or 1
            let breakStart = "{{ $breakStart }}"; // Laravel blade variable
            let expectDuration = "{{ $expect_duration }}"; // Laravel blade variable
            initDoctorStatusToggle({
                doctorId: doctorId,
                isAvailable: isAvailable,
                breakStart: breakStart,
                expectDuration: expectDuration
            });
        });
    </script>
@endsection
