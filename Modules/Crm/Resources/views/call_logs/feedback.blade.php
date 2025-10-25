<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">

        <!-- Modal Header -->
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <h3 class="modal-title">Feedback Call</h3>
        </div>

        <!-- Modal Body -->
        <div class="modal-body">
            <!-- Patient & Handler Info -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <p><strong>Patient:</strong> {{ $patientInfo->patient_name }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Call Handler:</strong> {{ $userInfo->user_name }}</p>
                </div>
            </div>

            <!-- Feedback Table -->
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="thead-light text-center">
                        <tr>
                            <th style="width: 30%;">Role</th>
                            <th>Rating</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($feedbackData as $feedback)
                            @if (empty($feedback->role_name) ||
                                    ($feedback->role_name != 'General' && $feedback->rating_value === null && $feedback->role_name != 'Pharmacy') ||
                                    ($feedback->role_name == 'Pharmacy' && $feedback->rating_value === null && empty($feedback->option_text)))
                                @continue
                            @endif


                            <tr>
                                <td>
                                    @if ($feedback->role_name != 'General' && $feedback->rating_value != null)
                                        {{ $feedback->role_name }}
                                    @elseif ($feedback->role_name == 'Pharmacy' && $feedback->rating_value == null)
                                        {{ $feedback->role_name }} ?
                                    @endif
                                </td>
                                <td>
                                    @if ($feedback->role_name != 'General' && $feedback->rating_value != null)
                                        @for ($i = 1; $i <= 5; $i++)
                                            @if ($i <= $feedback->rating_value)
                                                <i class="fas fa-star text-warning"></i>
                                            @else
                                                <i class="fas fa-star text-muted"></i>
                                            @endif
                                        @endfor
                                    @elseif ($feedback->role_name == 'Pharmacy' && $feedback->rating_value == null)
                                        {{ $feedback->option_text }}
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray font-12 text-center footer-total">
                            <td style="width: 30%;">
                                <strong>Comment:</strong>
                            </td>
                            <td style="width: 70%;">
                                {{ $feedbackData->first()->comment ?? '' }}
                            </td>
                        </tr>
                    </tfoot>

                </table>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        </div>

    </div>
</div>
