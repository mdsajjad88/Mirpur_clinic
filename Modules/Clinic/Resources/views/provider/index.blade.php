@extends('clinic::layouts.app2')
@section('title', __('Provider'))
@section('content')
    <section class="content">
        @component('components.widget', [
            'class' => 'box-primary',
            'title' => __('clinic::doctor.doctor_list'),
        ])
            @if (auth()->user()->can('clinic.provider.create'))
                @slot('tool')
                    <div class="box-tools">

                        <button type="button" class="btn btn-primary  add_new_doctor">
                            <i class="fa fa-plus"></i> @lang('messages.add')</button>           
                    </div>
                @endslot
            @endif

            <table class="table table-bordered table-striped" id="doctors_table" style="width:100%">
                <thead>
                    <tr>
                        <th>Action</th>
                        <th>Name</th>
                        <th>Mobile</th>
                        <th>Gender</th>
                        <th>Address</th>
                        <th>Fee</th>
                        <th>Is Cunsultant</th>
                        <th>Is Full Time</th>
                        <th>@lang('clinic::doctor.designation')</th>
                        <th>UserName</th>
                    </tr>
                </thead>
            </table>
        @endcomponent
        
        <div class="modal fade edit_doctor_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        </div>
        <div class="modal fade view_doctor_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        </div>
        <div class="modal fade doctor_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
            @include('clinic::provider.add_doctor')
        </div>
    </section>

@endsection
@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            var doctors_table = $('#doctors_table').DataTable({
                processing: true,
                serverSide: true,
                scrollY: "75vh",
                scrollX: true,
                scrollCollapse: true,
                ajax: {
                    url: "{{ route('provider.index') }}",
                },
                columns: [{
                        data: 'action',
                        searchable: false,
                        orderable: false
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'mobile',
                        name: 'mobile'
                    },
                    {
                        data: 'gender',
                        name: 'gender'
                    },
                    {
                        data: 'address',
                        name: 'address'
                    },
                    {
                        data: 'fee',
                        name: 'fee'
                    },
                    {
                        data: 'is_consultant',
                        name: 'is_consultant'
                    },
                    {
                        data: 'is_full_time',
                        name: 'is_full_time'
                    },
                    {
                        data: 'designation',
                        name: 'designation',
                    },
                    {
                        data: 'userName',
                        name: 'userName'
                    },
                    
                ],
            });

            doctors_table.ajax.reload();
        });

        $(document).on('click', '.more_btn', function() {
            $("div").find('.add_more_info_doctor').toggleClass('hide');
        });
        $(document).on('click', '.delete_doctor_button', function(e) {
            e.preventDefault();
            swal({
                title: LANG.sure,
                text: LANG.confirm_delete_doctor_profile,
                icon: 'warning',
                buttons: true,
                dangerMode: true,
            }).then(willDelete => {
                if (willDelete) {
                    var href = $(this).attr('href');
                    var data = $(this).serialize();
                    $.ajax({
                        method: 'DELETE',
                        url: href,
                        dataType: 'json',
                        data: data,
                        success: function(result) {
                            if (result.success == true) {
                                toastr.success(result.msg);
                                $('#doctors_table').DataTable().ajax.reload();
                            } else {
                                toastr.error(result.msg);
                            }
                        },
                    });
                }
            });
        });
       
        $(document).on('click', 'a.update_doctor_status', function(e) {
            e.preventDefault();
            var href = $(this).attr('href');
            $.ajax({
                url: href,
                dataType: 'json',
                success: function(data) {
                    if (data.success == true) {
                        toastr.success(data.msg);
                        $('#doctors_table').DataTable().ajax.reload();
                    } else {
                        toastr.error(data.msg);
                    }
                },
            });
        });
        $(document).on('click', '.edit_doctor_button', function(e) {
            e.preventDefault();
            $('div.edit_doctor_modal').load($(this).attr('href'), function() {
                $(this).modal('show');
            });
        });
        $(document).on('click', '.view_doctor_button', function(e) {
            e.preventDefault();
            $('div.view_doctor_modal').load($(this).attr('href'), function() {
                $(this).modal('show');
            });
        });
        $(document).on('click', '.add_new_doctor', function() {
                $('#customer_id').select2('close');
                var name = $(this).data('name');
                $('.doctor_modal').find('input#name').val(name);
                $('.doctor_modal')
                    .find('select#contact_type')
                    .val('customer')
                    .closest('div.contact_type_div')
                    .addClass('hide');
                $('.doctor_modal').modal('show');
            });
            
    </script>
@endsection
