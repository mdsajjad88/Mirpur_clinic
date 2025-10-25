<div class="modal-dialog modal-lg" role="document" style="max-width: 900px; border-radius: 12px;">
    <div class="modal-content" style="border-radius: 12px; box-shadow: 0 6px 25px rgba(0,0,0,0.2); border: none;">

        <!-- Header -->
        <div class="modal-header"> <button type="button" class="close" data-dismiss="modal" aria-label="Close"> <span
                    aria-hidden="true">&times;</span> </button>
            <h4 class="modal-title"> @lang('crm::lang.sms_details') </h4>
        </div>

        <!-- Body -->
        <div class="modal-body" style="padding: 20px; font-size: 14px; color: #374151; line-height: 1.6;">
            <p><strong>@lang('crm::lang.sms_body'):</strong> {{ $bulk->sms_body }}</p>
            <p><strong>@lang('crm::lang.total_contacts'):</strong> {{ $bulk->total_contacts }}</p>
            <p><strong>@lang('crm::lang.success'):</strong> {{ $bulk->success_count }}</p>
            <p><strong>@lang('crm::lang.failed'):</strong> {{ $bulk->fail_count }}</p>
            <p><strong>@lang('crm::lang.total_sms'):</strong> {{ $bulk->total_sms_count }}</p>

            <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 20px 0;">

            <h4 style="font-size: 16px; font-weight: 600; margin-bottom: 15px; color: #111827;">
                @lang('crm::lang.individual_logs')
            </h4>

            <!-- Table -->
            <div class="table-responsive" style="border-radius: 8px; overflow: hidden; border: 1px solid #e5e7eb; max-height: 500px; overflow-y: auto;">
                <table class="table table-bordered" style="margin: 0; border-collapse: collapse;">
                    <thead style="background: #f9fafb; font-weight: 600; color: #374151;">
                        <tr>
                            <th style="padding: 10px;">@lang('contact.contact')</th>
                            <th style="padding: 10px;">@lang('contact.mobile')</th>
                            <th style="padding: 10px;">@lang('crm::lang.status')</th>
                            <th style="padding: 10px;">@lang('crm::lang.sms_count')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($logs as $log)
                            <tr style="border-top: 1px solid #e5e7eb;">
                                <td style="padding: 10px;">{{ optional($log->contact)->first_name }} {{ optional($log->contact)->last_name }}</td>
                                <td style="padding: 10px;">
                                    @can('admin')
                                        {{-- Admins see the number directly --}}
                                        {{ $log->mobile_number }}
                                    @else
                                        {{-- Other users see phone icon --}}
                                        <span class="phone-icon" 
                                            onclick="showNumber(this)" 
                                            style="cursor: pointer; color: #3b82f6; font-size: 18px;">
                                            <i class="fas fa-phone-square"></i>
                                        </span>
                                        <span class="hidden-number" style="display: none;">
                                            {{ $log->mobile_number }}
                                        </span>
                                    @endcan
                                </td>
                                <td style="padding: 10px;">
                                    <span
                                        style="display: inline-block; padding: 5px 10px; border-radius: 6px; font-size: 12px; font-weight: 500; color: #fff; background: {{ $log->status == 'success' ? '#10b981' : '#ef4444' }};">
                                        {{ ucfirst($log->status) }}
                                    </span>
                                </td>
                                <td style="padding: 10px;">{{ $log->sms_count }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Footer -->
        <div class="modal-footer"
            style="border-top: 1px solid #e5e7eb; background: #f9fafb; padding: 12px 20px; border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
            <button type="button" class="btn btn-default" data-dismiss="modal"
                style="padding: 8px 16px; border-radius: 6px; background: #e5e7eb; border: none; font-weight: 500; cursor: pointer; transition: 0.3s;">
                @lang('messages.close')
            </button>
        </div>
    </div>
</div>

<script>
function showNumber(el) {
    // Hide all other revealed numbers
    document.querySelectorAll('.hidden-number').forEach(num => num.style.display = 'none');
    document.querySelectorAll('.phone-icon').forEach(icon => icon.style.display = 'inline');

    // Show clicked number
    const number = el.nextElementSibling;
    el.style.display = 'none';
    number.style.display = 'inline';
}
</script>

