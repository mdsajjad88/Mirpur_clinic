<div class="modal-dialog" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">Show {{ ucfirst($details->type) }} Info</h4>
        </div>

        <div class="modal-body">
            <p><strong>Name:</strong> {{ $details->name }}</p>

            @if($details->type === 'seminar')
                <p><strong>Patient Allow Count:</strong> {{ $details->patient_allow_count }}</p>
                <p><strong>Fee:</strong> {{ $details->fee }}</p>
                <p><strong>Mobile:</strong> {{ $details->mobile }}</p>
                <p><strong>Website URL:</strong> <a href="{{ $details->website_url }}" target="_blank">{{ $details->website_url }}</a></p>
                <p><strong>Already Registered Link:</strong> <a href="{{ $details->already_registered_link }}" target="_blank">{{ $details->already_registered_link }}</a></p>

                <p><strong>Show Division:</strong> {{ $details->is_show_division ? 'Yes' : 'No' }}</p>
                <p><strong>Show District:</strong> {{ $details->is_show_district ? 'Yes' : 'No' }}</p>
                <p><strong>Show Primary Disease:</strong> {{ $details->is_show_primary_disease ? 'Yes' : 'No' }}</p>
                <p><strong>Show Secondary Disease:</strong> {{ $details->is_show_secondary_disease ? 'Yes' : 'No' }}</p>
                <p><strong>Is Active:</strong> {{ $details->is_active ? 'Yes' : 'No' }}</p>

                @if(!empty($details->banner_img))
                    <p><strong>Banner Image:</strong></p>
                    <img src="{{ asset('uploads/img/' . $details->banner_img) }}" class="img-responsive" alt="Banner Image" style="max-height: 200px;">
                @endif
            @else
                {{-- You can add other type-specific fields here --}}
                <p><strong>Days Prior: </strong> {{ $details->date_counting }}</p>
                <p><strong>Extend Days: </strong> {{ $details->date_counting_with_pre_date }}</p>
            @endif
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
    </div>
</div>
