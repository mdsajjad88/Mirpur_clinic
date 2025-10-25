@extends('clinic::layouts.app2')
@section('title', 'Meal Time')

@section('content')
    <!-- Main content -->
    <section class="content">
        @component('components.widget', ['class' => 'box-primary', 'title' => 'All Your Meal Time'])
            @slot('tool')
                @if (auth()->user()->can('nu.meal_time.create'))
                    <div class="box-tools">
                        <button type="button" class="btn btn-block btn-primary btn-modal"
                            data-href="{{ action([\Modules\Clinic\Http\Controllers\nutritionist\MealTimeController::class, 'create']) }}"
                            data-container=".add_new_meal_time">
                            <i class="fa fa-plus"></i> @lang('messages.add')
                        </button>
                    </div>
                @endif
            @endslot
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="meal_time_table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            {{-- <th>Start Time</th>
                            <th>End Time</th> --}}
                            <th>Status</th>
                            @if(auth()->user()->can('nu.meal_time.update') || auth()->user()->can('nu.meal_time.delete'))
                            <th>@lang('messages.action')</th>
                            @endif
                        </tr>
                    </thead>
                </table>
            </div>
        @endcomponent

        <div class="modal fade add_new_meal_time" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        </div>

    </section>
    <!-- /.content -->

@endsection
@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            var meal_time_table = $('#meal_time_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('meal-time.index') }}",
                },
                columns: [

                    {
                        data: 'name',
                        name: 'name'
                    },
                    // {
                    //     data: 'start_time',
                    //     name: 'start_time'
                    // },
                    // {
                    //     data: 'end_time',
                    //     name: 'end_time'
                    // },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    @if(auth()->user()->can('nu.meal_time.update') || auth()->user()->can('nu.meal_time.delete'))
                    {
                        data: 'action',
                        name: 'action'
                    }
                    @endif
                ]
            });

            $(document).on('click', '.edit_meal_time', function() {
                $('div.add_new_meal_time').load($(this).data('href'), function() {
                    $(this).modal('show');

                    $('form#dosage_update_form').submit(function(e) {
                        e.preventDefault();
                        var form = $(this);
                        var data = form.serialize();

                        $.ajax({
                            method: 'POST',
                            url: $(this).attr('action'),
                            dataType: 'json',
                            data: data,
                            beforeSend: function(xhr) {
                                __disable_submit_button(form.find(
                                    'button[type="submit"]'));
                            },
                            success: function(result) {
                                if (result.success == true) {
                                    $('div.add_new_meal_time').modal('hide');
                                    toastr.success(result.msg);
                                    meal_time_table.ajax.reload();
                                } else {
                                    toastr.error(result.msg);
                                }
                            },
                        });
                    });
                });
            });

            $(document).on('click', '.delete_meal_time', function(e) {
                e.preventDefault();
                swal({
                    title: LANG.sure,
                    text: 'Are you sure want to delete this Meal Time?',
                    icon: 'warning',
                    buttons: true,
                    dangerMode: true,
                }).then(willDelete => {
                    if (willDelete) {
                        var href = $(this).data('href');
                        var data = $(this).serialize();

                        $.ajax({
                            method: 'DELETE',
                            url: href,
                            dataType: 'json',
                            data: data,
                            success: function(result) {
                                if (result.success == true) {
                                    toastr.success(result.msg);
                                    meal_time_table.ajax.reload();
                                } else {
                                    toastr.error(result.msg);
                                }
                            },
                        });
                    }
                });
            });

        })
    </script>
@endsection
