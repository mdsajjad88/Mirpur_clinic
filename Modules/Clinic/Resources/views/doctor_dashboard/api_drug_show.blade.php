<style>
    .sub-headding {
        font-size: 17px;
        background-color: #E6E6E6;
        color: #435B66;
        font-weight: bold;
        padding: 5px;
        margin-bottom: 10px;
        border-radius: 3px;
    }

    .headding {
        font-size: 18px;
        /* background-color: #E6E6E6; */
        color: #435B66;
        font-weight: bold;
        padding: 5px;
        margin-bottom: 10px;
        border-radius: 3px;
        text-align: center;
    }
</style>
<div class="modal-dialog modal-lg no-print" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title" id="modalTitle"><i class="fas fa-pills"></i> {{ $medicine['name'] }}</h3>
            {{-- <button type="button" class="btn-close no-print" data-dismiss="modal" aria-label="Close"></button> --}}
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="card mb-3">
                        <div class="card-body">
                            <h4 class="headding">Medicine Overview</h5>
                                <table class="table table-borderless">
                                    <tr>
                                        <td width="30%"><strong>Brand Name</strong></td>
                                        <td>{{ $medicine['name'] }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Generic Name</strong></td>
                                        <td>{{ $medicine['generic_name'] }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Drug Class</strong></td>
                                        <td>{{ $medicine['drug_class'] }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Therapeutic Class</strong></td>
                                        <td>{!! $medicine['therapeutic_class_description'] !!}</td>
                                    </tr>
                                </table>
                        </div>
                    </div>


                    <div class="card mb-3">
                        <div class="card-body">
                            <h4 class="headding">Clinical Information</h5>

                                <div class="mb-4">
                                    <div class="sub-headding">Indication</div>
                                    <p>{{ $medicine['indication'] }}</p>
                                </div>


                                <div class="mb-4">
                                    <div class="sub-headding">Pharmacology</div>
                                    <p>{!! $medicine['pharmacology_description'] !!}</p>
                                </div>


                                <div class="mb-4">
                                    <div class="sub-headding">Dosage & Administration</div>
                                    <p>{!! $medicine['dosage_description'] !!}</p>
                                    <p>{!! $medicine['administration_description'] !!}</p>
                                </div>


                                <div class="mb-4">
                                    <div class="sub-headding">Interaction</div>
                                    <p>{!! $medicine['interaction_description'] !!}</p>
                                </div>


                                <div class="mb-4">
                                    <div class="sub-headding">Contraindications</div>
                                    <p>{!! $medicine['contraindications_description'] !!}</p>
                                </div>


                                <div class="mb-4">
                                    <div class="sub-headding">Side Effects</div>
                                    <p>{!! $medicine['side_effects_description'] !!}</p>
                                </div>


                                <div class="mb-4">
                                    <div class="sub-headding">Pregnancy & Lactation</div>
                                    <p>{!! $medicine['pregnancy_lactation_description'] !!}</p>
                                </div>


                                <div class="mb-4">
                                    <div class="sub-headding">Precautions & Warnings</div>
                                    <p>{!! $medicine['precautions_description'] !!}</p>
                                </div>


                                <div class="mb-4">
                                    <div class="sub-headding">Storage Conditions</div>
                                    <p>{!! $medicine['storage_conditions_description'] !!}</p>
                                </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary no-print" data-dismiss="modal">@lang('messages.close')</button>
        </div>
    </div>
</div>


<script type="text/javascript">
    $(document).ready(function() {
        // Smooth scroll for anchor links
        $('.list-group-item').on('click', function(e) {
            e.preventDefault();
            var target = $(this).attr('href');
            $('html, body').animate({
                scrollTop: $(target).offset().top - 100
            }, 500);
        });
    });
</script>
