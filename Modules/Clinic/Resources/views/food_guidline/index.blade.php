@extends('clinic::layouts.app2')

@section('title', __('clinic::lang.food_guidline'))

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col">
                @component('components.widget', ['class' => 'box-primary', 'title' => __('clinic::lang.food_guidline')])
                    @if (auth()->user()->can('food_guidline.create'))
                        @slot('tool')
                            <div class="box-tools">
                                <a href="{{ action([\Modules\Clinic\Http\Controllers\FoodGuidlineController::class, 'create']) }}"
                                    class="btn btn-block btn-primary">@lang('messages.add')</a>
                            </div>
                        @endslot
                    @endif
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="food_guideline_table">
                            <thead>
                                <tr>
                                    <th>@lang('lang_v1.name')</th>
                                    <th>@lang('lang_v1.description')</th>
                                    <th>@lang('clinic::lang.created_by')</th>
                                    <th>@lang('clinic::lang.foods')</th>
                                    <th>@lang('clinic::lang.lifestyles')</th>
                                    <th style="min-width: 100px;">@lang('messages.action')</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                @endcomponent
            </div>
        </div>
        <div class="modal fade food_guideline_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        </div>
    </div>

@endsection
@section('javascript')

    <script type="text/javascript">
        $(document).ready(function() {
            var table = $('#food_guideline_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '/food-guidline',
                columns: [{
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'description',
                        name: 'description'
                    },
                    {
                        data: 'created_by',
                        name: 'created_by'
                    },
                    {
                        data: 'foods',
                        name: 'foods',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'lifestyles',
                        name: 'lifestyles',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ]
            });


            $(document).on('click', '.delete_food_guidline', function(e) {
                e.preventDefault();
                var url = $(this).data('href');

                swal({
                    title: "Are you sure you want to delete this guideline?",
                    icon: 'warning',
                    buttons: true,
                    dangerMode: true,
                }).then(willDelete => {
                    if (willDelete) {
                        $.ajax({
                            url: url,
                            type: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(result) {
                                if (result.success) {
                                    toastr.success(result.msg);

                                    // যদি DataTable use করো, তাহলে reload করো
                                    if (typeof $('#food_guideline_table').DataTable ===
                                        'function') {
                                        $('#food_guideline_table').DataTable().ajax
                                            .reload();
                                    } else {
                                        location.reload();
                                    }
                                } else {
                                    toastr.error(result.msg);
                                }
                            },
                            error: function(xhr) {
                                toastr.error("Something went wrong!");
                            }
                        });
                    }
                });


            })
        })
    </script>

@endsection
