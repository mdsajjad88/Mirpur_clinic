@extends('clinic::layouts.app2')

@section('title', $contact_filter->name)

@section('content')
    @include('crm::layouts.nav')
    <section class="content">
        @component('components.widget', ['class' => 'box-primary', 'title' => $contact_filter->name . ' Applied Filters'])
            <div class="row">
                @foreach ($filterData as $key => $value)
                    <div class="col-md-3">
                        <div class="form-group">
                            <strong>{{ ucfirst($key) }}: </strong>
                            @if (is_array($value))
                                {{ implode(', ', array_map(fn($m) => date('F', mktime(0, 0, 0, $m, 1)), $value)) }}
                            @else
                                {{ $value ? ucfirst($value) : 'N/A' }}
                            @endif


                        </div>
                    </div>
                @endforeach
            </div>
        @endcomponent

        @component('components.widget', ['class' => 'box-primary', 'title' => __('Contacts')])
            <table class="table table-bordered table-striped" id="filter_contact_table">
                <thead>
                    <tr>
                        <th>@lang('Name')</th>
                        <th>@lang('Mobile')</th>
                        <th>@lang('Contact ID')</th>
                    </tr>
                </thead>

            </table>
        @endcomponent
    </section>
@endsection
@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            $('#filter_contact_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ action([\Modules\Crm\Http\Controllers\ContactFilterController::class, 'show'], [$contact_filter->id]) }}",
                columns: [{
                        data: 'name',
                        name: 'contacts.name'
                    },
                    {
                        data: 'mobile',
                        name: 'contacts.mobile'
                    },
                    {
                        data: 'contact_id',
                        name: 'contacts.contact_id'
                    },
                ]
            });
        });
    </script>
@endsection
