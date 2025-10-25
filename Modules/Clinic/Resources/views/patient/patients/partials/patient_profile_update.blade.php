<div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">Update Profile Details</h4>
        </div>
        @php
            $url = action([\Modules\Clinic\Http\Controllers\PatientController::class, 'update'], [$patient->id]);
        @endphp
        <div class="modal-body">
            <div class="container-fluid">
                <div class="row gap-2">
                    {!! Form::open(['url' => $url, 'method' => 'PUT', 'class' => 'form-horizontal', 'id' => 'updateProfileForm']) !!}
                    <div class="row">
                        <div class="col-md-3">
                            {!! Form::label('first_name', 'First Name:*') !!}
                            {!! Form::text('first_name', $patient->first_name, ['class' => 'form-control', 'id' => 'first_name']) !!}
                        </div>
                        <div class="col-md-3">
                            {!! Form::label('last_name', 'Last Name') !!}
                            {!! Form::text('last_name', $patient->last_name, ['class' => 'form-control', 'id' => 'last_name']) !!}
                        </div>
                        <div class="col-md-3">
                            {!! Form::label('nick_name', 'Nick Name') !!}
                            {!! Form::text('nick_name', $patient->nick_name, ['class' => 'form-control', 'id' => 'nick_name']) !!}
                        </div>
                        <div class="col-md-3">
                            {!! Form::label('mobile', 'Mobile No:*') !!}
                            {!! Form::text('mobile', $patient->mobile, ['class' => 'form-control', 'id' => 'mobile']) !!}
                        </div>
                        <div class="col-md-3">
                            {!! Form::label('email', 'Email Address') !!}
                            {!! Form::text('email', $patient->email, ['class' => 'form-control', 'id' => 'email']) !!}
                        </div>
                        <div class="col-md-3">
                            {!! Form::label('gender', 'Gender:*') !!}
                            {!! Form::select('gender', ['' => 'Select Gender', 'male' => 'Male', 'female' => 'Female'], $patient->gender, [
                                'class' => 'form-control',
                                'id' => 'gender',
                            ]) !!}
                        </div>
                        <div class="col-md-3">
                            {!! Form::label('date_of_birth', 'Date Of Birth') !!}
                            {!! Form::date('date_of_birth', $patient->date_of_birth, ['class' => 'form-control', 'id' => 'date_of_birth']) !!}
                        </div>
                        <div class="col-md-3">
                            {!! Form::label('nid', 'NID No.') !!}
                            {!! Form::text('nid', $patient->nid, ['class' => 'form-control', 'id' => 'nid']) !!}
                        </div>
                        <div class="col-md-3">
                            {!! Form::label('age', 'Age:*') !!}
                            {!! Form::text('age', $patient->age, ['class' => 'form-control', 'id' => 'age']) !!}
                        </div>
                        <div class="col-md-3">
                            {!! Form::label('blood_group', 'Blood Group') !!}
                            {!! Form::text('blood_group', $patient->blood_group, ['class' => 'form-control', 'id' => 'blood_group']) !!}
                        </div>
                        <div class="col-md-3">
                            {!! Form::label('address', 'Address') !!}
                            {!! Form::text('address', $patient->address, ['class' => 'form-control', 'id' => 'address']) !!}
                        </div>
                        <div class="col-md-3">
                            {!! Form::label('marital_status', 'Marital Status') !!}
                            {!! Form::select(
                                'marital_status',
                                [
                                    '' => 'Select Marrital Status',
                                    'married' => 'Married',
                                    'unmarried' => 'Unmarried',
                                    'divorced' => 'Divorced',
                                ],
                                $patient->marital_status,
                                [
                                    'class' => 'form-control',
                                    'id' => 'marital_status',
                                ],
                            ) !!}
                        </div>

                        <div class="col-md-3">
                            {!! Form::label('height_cm', 'Height (cm)') !!}
                            {!! Form::text('height_cm', $patient->height_cm, ['class' => 'form-control', 'id' => 'height_cm']) !!}
                        </div>
                        <div class="col-md-3">
                            {!! Form::label('weight_kg', 'Weight (kg)') !!}
                            {!! Form::text('weight_kg', $patient->weight_kg, ['class' => 'form-control', 'id' => 'weight_kg']) !!}
                        </div>
                        <div class="col-md-3">
                            {!! Form::label('body_fat_percentage', 'Body Fat (%)') !!}
                            {!! Form::text('body_fat_percentage', $patient->body_fat_percentage, [
                                'class' => 'form-control',
                                'id' => 'body_fat_percentage',
                            ]) !!}
                        </div>
                        <div class="col-md-3">
                            {!! Form::label('work_phone', 'Work Phone') !!}
                            {!! Form::text('work_phone', $patient->work_phone, ['class' => 'form-control', 'id' => 'work_phone']) !!}
                        </div>
                        <div class="col-md-3">
                            {!! Form::label('city', 'City') !!}
                            {!! Form::text('city', $patient->city, ['class' => 'form-control', 'id' => 'city']) !!}
                        </div>
                        <div class="col-md-3">
                            {!! Form::label('state', 'State') !!}
                            {!! Form::text('state', $patient->state, ['class' => 'form-control', 'id' => 'state']) !!}
                        </div>
                        <div class="col-md-3">
                            {!! Form::label('post_code', 'Post Code') !!}
                            {!! Form::text('post_code', $patient->post_code, ['class' => 'form-control', 'id' => 'post_code']) !!}
                        </div>
                        <div class="col-md-3">
                            {!! Form::label('country', 'Country') !!}
                            {!! Form::text('country', $patient->country, ['class' => 'form-control', 'id' => 'country']) !!}
                        </div>
                        <div class="col-md-3">
                            {!! Form::label('emergency_contact_person', 'Emergency Contact Person') !!}
                            {!! Form::text('emergency_contact_person', $patient->emergency_contact_person, [
                                'class' => 'form-control',
                                'id' => 'emergency_contact_person',
                            ]) !!}
                        </div>
                        <div class="col-md-3">
                            {!! Form::label('emergency_phone', 'Emergency Contact Phone') !!}
                            {!! Form::text('emergency_phone', $patient->emergency_phone, [
                                'class' => 'form-control',
                                'id' => 'emergency_phone',
                            ]) !!}
                        </div>
                        <div class="col-md-6">
                            {!! Form::label('disease', 'Health Concerns:*') !!}
                            {!! Form::select('disease[]', $allDiseases->pluck('name', 'id')->toArray(), $patientDiseases, [
                                'class' => 'form-control select2',
                                'multiple' => 'multiple',
                                'required',
                                'style' => 'width: 100%;',
                                'id' => 'disease',
                            ]) !!}

                        </div>

                    </div>
                    <div class="col-md-12 mt-1">
                        {!! Form::submit('Save', ['class' => 'btn btn-primary']) !!}
                        {!! Form::reset('Reset', ['class' => 'btn btn-secondary']) !!}
                    </div>
                    {!! Form::close() !!}

                </div>
            </div>


        </div>

    </div>
</div>
<script>
    $(document).ready(function() {
        // Form validation
        $('#updateProfileForm').validate({
            rules: {
                first_name: {
                    required: true,
                },
                mobile: {
                    required: true,
                },
                age: {
                    required: true,
                },
                gender: {
                    required: true,
                },
            },
            messages: {
                first_name: {
                    required: 'Please enter your first name',
                },
                mobile: {
                    required: 'Please enter your mobile number',
                },
                age: {
                    required: 'Please enter your age',
                },
                gender: {
                    required: 'Please select your gender',
                },
            },
            submitHandler: function(form) {
                // AJAX request when form is valid
                let formData = $(form).serialize();

                $.ajax({
                    url: $(form).attr('action'),
                    type: 'PUT',
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message);
                            $('.modal').modal('hide'); // Hide the modal on success
                            // Update the dropdown with the edited patient details
                            let updatedPatient = $('<option>', {
                                value: response.data.id,
                                text: response.data.first_name + ' ' + (response.data.last_name),
                                selected: true,
                            });

                            // Remove the old patient option and append the updated one
                            let patientDropdown = $('#customer_id_clinic');
                            patientDropdown.find(`option[value="${response.data.id}"]`).remove();
                            patientDropdown.append(updatedPatient);

                            // Trigger change to refresh select2 dropdown
                            patientDropdown.trigger('change');

                            patients_table.ajax.reload();
                        } else {
                            toastr.error(response.message || 'An error occurred.');
                        }
                    },
                    error: function(xhr) {
                        let errors = xhr.responseJSON ? xhr.responseJSON.errors : null;
                        let errorMessage = 'An error occurred:\n';

                        if (errors) {
                            for (let field in errors) {
                                errorMessage += `${errors[field]}\n`;
                            }
                        } else {
                            errorMessage += xhr.responseJSON ?
                                xhr.responseJSON.message :
                                'Unexpected error occurred.';
                        }

                        toastr.error(errorMessage);
                    }
                });
            }
        });
    });
    $('#disease').select2({
        placeholder: 'Select Health Concerns',
        allowClear: true,
    });
</script>
