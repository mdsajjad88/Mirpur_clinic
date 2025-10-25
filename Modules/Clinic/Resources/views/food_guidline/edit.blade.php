@extends('clinic::layouts.app2')

@section('title', __('clinic::lang.edit_food_guidline'))

@section('content')
    <div class="container-fluid">
        {!! Form::model($guidline, [
            'url' => action([\Modules\Clinic\Http\Controllers\FoodGuidlineController::class, 'update'], $guidline->id),
            'method' => 'put',
            'id' => 'food_guidline_update_form',
        ]) !!}
        <div class="row">
            <div class="col">
                @component('components.widget', ['class' => 'box-primary', 'title' => __('clinic::lang.edit_food_guidline')])
                    <div class="form-group">
                        {!! Form::label('guidline_name', __('clinic::lang.guidline_name') . ':') !!}
                        {!! Form::text('guidline_name', $guidline->name, ['class' => 'form-control', 'id' => 'guidline_name']) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label('guidline_description', __('clinic::lang.guidline_description') . ':') !!}
                        {!! Form::textarea('guidline_description', $guidline->description, ['class' => 'form-control']) !!}
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('products', __('clinic::lang.products') . ':') !!}
                                {!! Form::select('product_ids[]', $products, $guidline->products->pluck('product_id')->toArray(), [
                                    'class' => 'form-control select2',
                                    'multiple',
                                ]) !!}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('lifestyle_products', __('clinic::lang.lifestyle_products') . ':') !!}
                                {!! Form::select('lifestyle_product_ids[]', $lifeStyles, $selectedLifeStyles, [
                                    'class' => 'form-control select2',
                                    'multiple',
                                ]) !!}
                            </div>
                        </div>
                    </div>
                @endcomponent
            </div>
        </div>

        <div>
            <button type="submit" class="btn btn-primary">@lang('messages.update')</button>
        </div>
        {!! Form::close() !!}
    </div>
@endsection

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            tinymce.init({
                selector: 'textarea#guidline_description',
                height: 450
            });

            $('#food_guidline_update_form').on('submit', function(e) {
                e.preventDefault();
                var form = $(this);
                var data = form.serialize();
                var url = form.attr('action');
                $.ajax({
                    url: url,
                    type: 'PUT',
                    data: data,
                    success: function(result) {
                        if (result.success) {
                            toastr.success(result.msg);
                            window.location.href =
                                '{{ action([\Modules\Clinic\Http\Controllers\FoodGuidlineController::class, 'index']) }}';
                        } else {
                            toastr.error(result.msg);
                        }
                    },
                    error: function(xhr) {
                        var response = xhr.responseJSON;
                        if (response && response.errors) {
                            var errors = response.errors;
                            for (var key in errors) {
                                if (errors.hasOwnProperty(key)) {
                                    toastr.error(errors[key][0]);
                                }
                            }
                        }
                    }
                });
            });
        });
    </script>
@endsection
