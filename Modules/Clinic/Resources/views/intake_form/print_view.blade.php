@extends('clinic::layouts.app2')

@section('title', 'Intake Form')

@section('content')
    <style>
        /* General Styles for Screen View */
        .form-header {
            text-align: center;
            margin-bottom: 10px;
        }

        .bg-color {
            background-color: #cadcdd;
        }

        .form-control {
            font-size: 20px;
            width: 100%;
            padding: 5px;
        }

        .input-dcheck {
            height: 10px;
            width: 10px;
            margin-right: 5px;

        }

        .mt-1 {
            margin-top: 5px !important;
        }

        .mt-3 {
            margin-top: 15px !important;
        }

        .p-1 {
            padding: 5px !important;
        }

        input,
        select,
        textarea {
            pointer-events: none;
            background-color: #f0f0f0;
        }

        /* Print Styles */
        @media print {
            body {
                width: 210mm;
                /* A4 width */
                height: 297mm;
                /* A4 height */
                margin: 0;
                padding: 0;
                font-size: 10px;
                /* Reduce base font size for printing */
            }

            .container-fluid {
                width: 100%;
                padding: 0;
                margin: 0;
            }

            .form-control {
                font-size: 10px;
                /* Reduce form control font size */
                width: 100% !important;
                height: auto;
                padding: 3px;
            }

            .btn {
                display: none;
                /* Hide buttons in print view */
            }

            .page-break {
                page-break-before: always;
            }

            .form-header h2 {
                font-size: 16px;
                /* Adjust header font size */
            }

            .form-header p {
                font-size: 12px;
                /* Adjust subheader font size */
            }

            /* Ensure columns maintain their layout in print view */
            .row {
                display: flex !important;
                flex-wrap: wrap !important;
                margin: 0;
            }

            .col-lg-6 {
                width: 50% !important;
                float: left !important;
                padding: 0 5px;
            }

            .col-lg-4 {
                width: 33.33% !important;
                float: left !important;
                padding: 0 5px;
            }

            .col-lg-8 {
                width: 66.66% !important;
                float: left !important;
                padding: 0 5px;
            }

            .col-lg-3 {
                width: 25% !important;
                float: left !important;
                padding: 0 5px;
            }

            .col-lg-2 {
                width: 16.66% !important;
                float: left !important;
                padding: 0 5px;
            }

            .form-group {
                margin-bottom: 5px;
                /* Reduce margin for form groups */
            }

            h4 {
                font-size: 12px !important;
                /* Adjust heading size for print */
            }

            p {
                font-size: 10px !important;
                /* Adjust paragraph size for print */
            }

            .bg-color {
                background-color: transparent !important;
                /* Remove background color for printing */
            }

            .form-control,
            .select2,
            .select2-selection {
                width: 100% !important;
                height: 23px !important;
            }

            .mt_print {
                margin-top: 2in !important;
            }

            .print-none {
                display: none !important;
            }

            .select2-selection__arrow {
                display: none !important;
            }

            @page {
                size: A4 portrait;
                /* Set page size and orientation */
                margin: 5mm;
                /* Set minimum margin */
            }
        }
    </style>

    <div class="container-fluid">
        <div class="row  print-none">
            <div class="col-md-2">
                <a href="{{ url('survey/intake-form/create') }}" class="btn btn-block btn-primary">
                    <i class="fa fa-plus"></i> Add New
                </a>

            </div>
            <div class="col-md-2">
                <a href="{{ url('survey/intake-form') }}" class="btn btn-block btn-info">
                    <i class="fa fa-eye"></i> Show All form
                </a>
            </div>
        </div>
        <div class="row mt_print">
            <div class="col-lg-12">
                <div class="form-header print-none">
                    <h2>স্বাস্থ্য সম্পর্কিত তথ্যাবলি</h2>
                    <p>বিগত ৮ বছরের মেডিকেল রিপোর্ট এবং ল্যাবরেটরি টেস্ট রিপোর্টগুলোর এই ফর্মের সাথে প্রদান করুন</p>
                </div>

                {!! Form::open(['url' => 'submit-form', 'method' => 'post']) !!}

                <!-- Personal Information Section -->
                <div class="row">
                    <div class="col-lg-4">
                        {!! Form::label('date', 'তারিখ:') !!}
                        {!! Form::date(
                            'date',
                            $report->last_visited_date ? \Carbon\Carbon::parse($report->last_visited_date)->format('Y-m-d') : null,
                            ['class' => 'form-control'],
                        ) !!}
                    </div>
                    <div class="col-lg-4">
                        {!! Form::label('name', 'নাম:') !!}
                        {!! Form::text('name', $report->first_name . ' ' . $report->last_name ?? '', ['class' => 'form-control']) !!}
                    </div>
                    <div class="col-lg-4">
                        {!! Form::label('age', 'বয়স:') !!}
                        {!! Form::text('age', $report->age, ['class' => 'form-control']) !!}
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-4">
                        {!! Form::label('gender', 'লিঙ্গ:') !!}
                        {!! Form::select('gender', ['male' => 'পুরুষ', 'female' => 'মহিলা', 'others' => 'Others'], $report->gender, [
                            'class' => 'form-control select2',
                        ]) !!}
                    </div>
                    <div class="col-lg-4">
                        {!! Form::label('profession', 'পেশাঃ') !!}
                        {!! Form::text('profession', $report->profession, ['class' => 'form-control']) !!}
                    </div>
                    <div class="col-lg-4">
                        {!! Form::label('blood_group', 'রক্তের গ্রুপ') !!}
                        {!! Form::select(
                            'blood_group',
                            [
                                'A+' => 'এ+',
                                'A-' => 'এ-',
                                'B+' => 'বি+',
                                'B-' => 'বি-',
                                'AB+' => 'এবি+',
                                'AB-' => 'এবি-',
                                'O+' => 'ও+',
                                'O-' => 'ও-',
                            ],
                            $report->blood_group,
                            ['class' => 'form-control select2', 'placeholder' => 'রক্তের গ্রুপ নির্ধারণ করুন', 'id' => 'blood_group'],
                        ) !!}
                        <span id="blood_group_print" style="display: none;"></span>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-3">
                        {!! Form::label('marital_status', 'বৈবাহিক অবস্থা:') !!}
                        {!! Form::select(
                            'marital_status',
                            ['married' => 'বিবাহিত', 'unmarried' => 'অবিবাহিত'],
                            $report->marital_status,
                            ['class' => 'form-control select2'],
                        ) !!}
                    </div>
                    
                    <div class="col-lg-3">
                        {!! Form::label('email', 'ই-মেইল:') !!}
                        {!! Form::text('email', $report->email, ['class' => 'form-control']) !!}
                    </div>
                    <div class="col-lg-3">
                        {!! Form::label('address', 'ঠিকানা:') !!}
                        {!! Form::text('address', $report->address, ['class' => 'form-control']) !!}
                    </div>
                </div>

                <!-- Health History Section -->
                <div class="col-lg-12 p-1 bg-color">
                    <div class="row">
                        <div class="col-lg-6">
                            <p><b>শৈশব/কৈশোরের ইতিহাসঃ</b> ছোটবেলায় আপনার স্বাস্থ্য কেমন ছিল?</p>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-group">
                                <div>
                                    {!! Form::checkbox('childhood_fitness_good', 1, $report->childhood_fitness_good == 1 ? true : false, [
                                        'class' => 'input-dcheck',
                                    ]) !!} চমৎকার ভালো (সাধারন সুস্থতা)
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-group">
                                <div>
                                    {!! Form::checkbox('childhood_fitness_good', 0, $report->childhood_fitness_good == 0 ? true : false, [
                                        'class' => 'input-dcheck',
                                    ]) !!} দীর্ঘস্থায়ী অসুস্থতা
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-12">
                    <div class="row">
                        <div class="col-lg-4"></div>
                        <div class="col-lg-4">
                            <h4>আপনি কোনটি দ্বারা ভুগছেন?</h4>
                        </div>
                        <div class="col-lg-4"></div>
                    </div>
                </div>

                <div class="col-lg-12">
                    <div class="form-group">
                        <div class="row">
                            @php
                                $problemIds = $select_problems->pluck('problem_id')->toArray();
                            @endphp

                            @foreach ($problems as $problem)
                                <div class="col-lg-2">
                                    {!! Form::checkbox('history[]', $problem->id, in_array($problem->id, $problemIds), ['class' => 'input-dcheck']) !!}
                                    {{$problem->name }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Main Problem Section -->
                <div class="col-lg-12 mt-1">
                    {!! Form::label('main_disease', 'আপনার স্বাস্থ্যের মূল সমস্যাগুলি লিখুনঃ') !!}
                    {!! Form::textarea('main_disease', $report->main_disease, ['class' => 'form-control', 'rows' => '3']) !!}
                </div>

                <div class="col-lg-12 mt-1">
                    <div class="row">
                        <div class="col-lg-4">
                            {!! Form::label('main_disease_duration_day', 'মূল সমস্যাটিতে কতদিন যাবত ভুগছেনঃ') !!}
                        </div>
                        <div class="col-lg-8">
                            {!! Form::text('main_disease_duration_day', $report->main_disease_duration_day, ['class' => 'form-control']) !!}
                        </div>
                    </div>
                </div>

                <!-- Family History Section -->
                <div class="col-lg-12 mt-1">
                    <div class="row">
                        <div class="col-lg-4">
                            {!! Form::label('family_history_disease', 'পারিবারিক ইতিহাসজনিত রোগঃ ') !!}
                        </div>
                        <div class="col-lg-8">
                            <div class="row">
                                @php
                                    $familyHistoryDiseases = is_string($report->family_history_disease)
                                        ? json_decode($report->family_history_disease, true)
                                        : $report->family_history_disease;
                                    if (!is_array($familyHistoryDiseases)) {
                                        $familyHistoryDiseases = explode(',', $report->family_history_disease);
                                    }
                                @endphp

                                @foreach ($family_problems as $disease)
                                    <div class="col-lg-4">
                                        {!! Form::checkbox('family_history_disease[]', $disease->id, in_array($disease->id, $familyHistoryDiseases), [
                                            'class' => 'input-dcheck',
                                        ]) !!}
                                        {{ $disease->name  }}
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Medication Section -->
                <div class="col-lg-12 mt-1">
                    <table class="p-1" style="width:100%">
                        <thead style="border: 1px solid rgb(235, 232, 232)">
                            <tr>
                                <th colspan="3" class="text-center" style="padding: 5px;">
                                    প্রেসক্রিপশন এবং তাঁর বাইরের যা যা আপনি বর্তমানে গ্রহন করছেন (১ মাসের বেশী) <br>
                                    <b>নিচের তালিকায় ঔষধের নামগুলো লিপিবদ্ধ করুন</b>
                                </th>
                            </tr>
                        </thead>



                        <tbody>
                            @php
                                $prescribed_diseases = is_string($report->old_prescribed_disease)
                                    ? json_decode($report->old_prescribed_disease, true) // Decode if it's a string
    : $report->old_prescribed_disease;

if (!is_array($prescribed_diseases)) {
    Log::info('Array is empty ');
    $prescribed_diseases = array_map(
        'trim',
        explode(',', $report->old_prescribed_disease),
                                    );
                                }

                                $chunks = array_chunk($prescribed_diseases, 3);
                            @endphp

                            @foreach ($chunks as $chunk)
                                <tr>
                                    @foreach ($chunk as $index => $disease)
                                        <td>
                                            {{ Form::text('old_prescribed_disease[]', $disease, ['class' => 'form-control']) }}
                                        </td>
                                    @endforeach
                                    @for ($i = count($chunk); $i < 3; $i++)
                                        <td>
                                            {{ Form::text('old_prescribed_disease[]', null, ['class' => 'form-control']) }}
                                        </td>
                                    @endfor
                                </tr>
                            @endforeach
                            @for ($i = count($chunks); $i < 3; $i++)
                                <tr>
                                    <td>{{ Form::text('old_prescribed_disease[]', null, ['class' => 'form-control']) }}</td>
                                    <td>{{ Form::text('old_prescribed_disease[]', null, ['class' => 'form-control']) }}</td>
                                    <td>{{ Form::text('old_prescribed_disease[]', null, ['class' => 'form-control']) }}</td>
                                </tr>
                            @endfor
                        </tbody>


                        {{-- end Chatgpt --}}

                    </table>
                </div>

                <!-- Sleep and Exercise Section -->
                <div class="col-lg-12" style="margin-top: 5px !important;">
                    <div class="row">
                        <div class="col-lg-3">{!! Form::label('daily_sleeping_hourse', 'প্রতিরাতে কতক্ষণ ঘুমান (ঘণ্টা) ঃ  ') !!} </div>
                        <div class="col-lg-3"> {!! Form::text('daily_sleeping_hourse', $report->daily_sleeping_hourse, ['class' => 'form-control']) !!} </div>
                        <div class="col-lg-3">{!! Form::label('daily_exercize_minute', 'প্রতিদিন কতক্ষণ ব্যায়াম করেন (মিনিট)  ঃ') !!}</div>
                        <div class="col-lg-3">{!! Form::text('daily_exercize_minute', $report->daily_exercize_minute, ['class' => 'form-control']) !!}</div>
                    </div>
                </div>

                <div class="col-lg-12 mt-1">
                    <div class="row">
                        <div class="col-lg-3">{!! Form::label('is_sleeping_problem', 'রাতে ঘুমাতে সমস্যা হয়  ঃ') !!}</div>
                        <div class="col-lg-3">
                            <div class="row">
                                <div class="col-lg-4">
                                    {!! Form::checkbox('is_sleeping_problem', 1, $report->is_sleeping_problem == 1 ? true : false, [
                                        'class' => 'input-dcheck',
                                    ]) !!} হ্যাঁ
                                </div>
                                <div class="col-lg-4">
                                    {!! Form::checkbox('is_sleeping_problem', 0, $report->is_sleeping_problem == 0 ? true : false, [
                                        'class' => 'input-dcheck',
                                    ]) !!} না
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            {!! Form::label('reason_for_less_sleeping', 'যদি না হয়, তবে কেন আপনি রাতে কম ঘুমান?') !!}
                        </div>
                        <div class="col-lg-3">
                            {!! Form::textarea('reason_for_less_sleeping', $report->reason_for_less_sleeping, [
                                'class' => 'form-control',
                                'rows' => 2,
                            ]) !!}
                        </div>
                    </div>
                </div>

                <!-- Diet Section -->
                <div class="col-lg-12 mt-1">
                    <table style="width: 100%">
                        <thead style="border: 1px solid rgb(235, 232, 232);">
                            <tr>
                                <th colspan="2" class="text-center" style="padding:5px"> <b> ডায়েট সম্পর্কিত আলোচনা</b>
                                    <br> গত দিনের খাওয়া
                                    সবগুলো খাবার এবং পানিয় তালিকাভুক্ত করুন
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{{ Form::label('breakfast', 'সকালের খাবার') }}</td>
                                <td>{{ Form::text('breakfast', $report->breakfast, ['class' => 'form-control']) }}</td>
                            </tr>
                            <tr>
                                <td>{{ Form::label('lunch', 'দুপুরের খাবার') }}</td>
                                <td>{{ Form::text('lunch', $report->lunch, ['class' => 'form-control']) }}</td>
                            </tr>
                            <tr>
                                <td>{{ Form::label('afternoon_snaks', 'বিকেলের খাবার') }}</td>
                                <td>{{ Form::text('afternoon_snaks', $report->afternoon_snaks, ['class' => 'form-control']) }}
                                </td>
                            </tr>
                            <tr>
                                <td>{{ Form::label('dinner', 'রাতের খাবার') }}</td>
                                <td>{{ Form::text('dinner', $report->dinner, ['class' => 'form-control']) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {!! Form::close() !!}
                <div class="col mt-1">
                    <div class="form-group text-center">
                        <button type="button" class="btn btn-success" onclick="printForm()">Print Report</button>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

@section('javascript')
    <script type="text/javascript">
        function printForm() {
            $('input, textarea, select').attr('readonly', true);
            window.print();
            setTimeout(function() {
                $('input, textarea, select').attr('readonly', false);
            }, 1000);
        }
        $(document).ready(function() {
            $('input, textarea, select').attr('readonly', true);
            printForm();
        });
    </script>
@endsection
