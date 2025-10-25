@extends('clinic::layouts.app2')
@section('title', 'BD Drug List')
@section('style')
    <style>
        .name_th {
            min-width: 400px !important;
        }
    </style>
@endsection
@section('content')
    <div class="container-fluid">
        <div class="row">
            @component('components.filters', ['title' => 'Filters', 'class' => 'box-primary'])
                <div class="col-md-3">
                    {!! Form::label('indications', 'Indication:') !!}
                    {!! Form::select('indications', ['' => 'All'] + $indications, null, [
                        'class' => 'form-control select2',
                        'style' => 'width:100%;',
                        'id' => 'filter_with_indication',
                    ]) !!}
                </div>
                <div class="col-md-3">
                    {!! Form::label('manufacturers', 'Manufacturer:') !!}
                    {!! Form::select('manufacturers', ['' => 'All'] + $manufacturers, null, [
                        'class' => 'form-control select2',
                        'style' => 'width:100%;',
                        'id' => 'filter_with_manufacturer',
                    ]) !!}
                </div>
                <div class="col-md-3">
                    {!! Form::label('drug_classes', 'Drug Class:') !!}
                    {!! Form::select('drug_classes', ['' => 'All'] + $drug_classes, null, [
                        'class' => 'form-control select2',
                        'style' => 'width:100%;',
                        'id' => 'filter_with_drug_class',
                    ]) !!}
                </div>
                <div class="col-md-3">
                    {!! Form::label('generics', 'Generic:') !!}
                    {!! Form::select('generics', ['' => 'All'] + $generics, null, [
                        'class' => 'form-control select2',
                        'style' => 'width:100%;',
                        'id' => 'filter_with_generic',
                    ]) !!}
                </div>
                <div class="col-md-3">
                    {!! Form::label('dosage_forms', 'Dosage Forms:') !!}
                    {!! Form::select('dosage_forms', ['' => 'All'] + $dosage_forms, null, [
                        'class' => 'form-control select2',
                        'style' => 'width:100%;',
                        'id' => 'filter_with_dosage_form',
                    ]) !!}
                </div>
            @endcomponent
        </div>

        <div class="row">
            <div class="col">
                @component('components.widget', ['title' => 'BD Drug List', 'class' => 'box-primary'])
                    <table class="table table-striped" id="api_drug_list_table" style="width: 100%">
                        <thead>
                            <tr>
                                <th style="min-width: 15% !important;">Name</th>
                                <th>Generic</th>
                                <th>Category</th>
                                <th>Dosage Form</th>
                                <th>Manufacturer</th>
                                <th>Drug Class</th>
                                <th>Indication</th>

                            </tr>
                        </thead>
                    </table>
                @endcomponent

            </div>
        </div>
    </div>
    <div class="modal fade drug_data_show_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>
@endsection
@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            var api_drug_list_table = $('#api_drug_list_table').DataTable({
                processing: true,
                serverSide: true,
                aaSorting: [
                    [0, 'asc']
                ],
                "ajax": {
                    "url": "/drug/show/in/doctor",
                    "data": function(d) {
                        d.indication = $('#filter_with_indication').val();
                        d.manufacturer = $('#filter_with_manufacturer').val();
                        d.drug_class = $('#filter_with_drug_class').val();
                        d.generic = $('#filter_with_generic').val();
                        d.dosage_form = $('#filter_with_dosage_form').val();
                    }
                },
                scrollY: "75vh",
                scrollX: true,
                scrollCollapse: true,
                dom: 'frtip',
                columns: [{
                        data: 'name',
                        name: 'name',
                        className: 'text-center'
                    },
                    {
                        data: 'generic',
                        name: 'generic',
                        searchable: false
                    },

                    {
                        data: 'category',
                        name: 'category',
                        visible: false,
                    },
                    {
                        data: 'dosage_form',
                        name: 'dosage_form',
                        searchable: false
                    },

                    {
                        data: 'manufacturer',
                        name: 'manufacturer',
                        searchable: false
                    },
                    {
                        data: 'drug_class',
                        name: 'drug_class',
                        searchable: false
                    },
                    {
                        data: 'indication',
                        name: 'indication',
                        searchable: false
                    },


                ],
                language: {
                    searchPlaceholder: "Search by Medicine Name..." // Custom placeholder text
                }
            });
            $(document).on('change',
                '#filter_with_indication, #filter_with_manufacturer, #filter_with_drug_class, #filter_with_generic, #filter_with_dosage_form',
                function() {
                    api_drug_list_table.ajax.reload();
                })
            // $('#api_drug_list_table').on('click', '.clickable-row', function() {
            //     const url = $(this).data('href');
            //     if (url) {
            //         // Open in modal or redirect as needed
            //         $('.view_modal').modal('show');
            //         $('.view_modal .modal-content').load(url);
            //     }
            // });
        });
    </script>
@endsection
