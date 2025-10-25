<div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title">View Profile Details</h4>
        </div>

        <div class="modal-body">
            <div class="container-fluid">
                <div class="row gap-2">
                    <div class="col-md-4">
                        <p><strong>First Name:</strong> <span>{{ $patient->first_name??'' }}</span></p>
                    </div>
                    <div class="col-md-4">
                        <p><strong>Last Name:</strong> <span>{{ $patient->last_name??'' }}</span></p>
                    </div>
                    <div class="col-md-4">
                        <p><strong>Nick Name:</strong> <span>{{ $patient->nick_name??'' }}</span></p>
                    </div>
                    <div class="col-md-4">
                        <p><strong>Email Address:</strong> <span>{{ $patient->email??'' }}</span></p>
                    </div>
                    <div class="col-md-4">
                        <p><strong>Gender:</strong> <span>{{ $patient->gender??'' }}</span></p>
                    </div>
                    <div class="col-md-4">
                        <p><strong>Date of Birth:</strong> <span>{{ $patient->date_of_birth??'' }}</span></p>
                    </div>
                    <div class="col-md-4">
                        <p><strong>NID No.:</strong> <span>{{ $patient->nid??'' }}</span></p>
                    </div>
                    <div class="col-md-4">
                        <p><strong>Age:</strong> <span>{{ $patient->age??'' }}</span></p>
                    </div>
                    <div class="col-md-4">
                        <p><strong>Blood Group:</strong> <span>{{ $patient->blood_group??'' }}</span></p>
                    </div>
                    <div class="col-md-4">
                        <p><strong>Address:</strong> <span>{{ $patient->address??'' }}</span></p>
                    </div>
                    <div class="col-md-4">
                        <p><strong>Marital Status:</strong> <span>{{ $patient->marital_status??'' }}</span></p>
                    </div>
                    <div class="col-md-4">
                        <p><strong>Height (cm):</strong> <span>{{ $patient->height_cm??'' }}</span></p>
                    </div>
                    <div class="col-md-4">
                        <p><strong>Weight (kg):</strong> <span>{{ $patient->weight_kg ??''}}</span></p>
                    </div>
                    <div class="col-md-4">
                        <p><strong>Body Fat (%):</strong> <span>{{ $patient->body_fat_percentage??'' }}</span></p>
                    </div>
                    <div class="col-md-4">
                        <p><strong>Work Phone:</strong> <span>{{ $patient->work_phone??'' }}</span></p>
                    </div>
                    <div class="col-md-4">
                        <p><strong>City:</strong> <span>{{ $patient->city??'' }}</span></p>
                    </div>
                    <div class="col-md-4">
                        <p><strong>State:</strong> <span>{{ $patient->state??'' }}</span></p>
                    </div>
                    <div class="col-md-4">
                        <p><strong>Post Code:</strong> <span>{{ $patient->post_code??'' }}</span></p>
                    </div>
                    <div class="col-md-4">
                        <p><strong>Country:</strong> <span>{{ $patient->country??'' }}</span></p>
                    </div>
                    <div class="col-md-4">
                        <p><strong>Emergency Contact Person:</strong> <span>{{ $patient->emergency_contact_person??'' }}</span></p>
                    </div>
                    <div class="col-md-4">
                        <p><strong>Emergency Contact Phone:</strong> <span>{{ $patient->emergency_phone??'' }}</span></p>
                    </div>
                </div>
            </div>
            <!-- End of Patient Information -->
        </div>
    </div>

</div>

