<div class="modal fade" id="subscriptionModal" tabindex="-1" role="dialog" aria-labelledby="subscriptionModalTitle"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="subscriptionModalTitle"><b>@lang('clinic.add_subscription')</b></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="container-fluid">
                <form action="" method="post">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col">
                                <label for="subscription">@lang('clinic.subscription')</label>
                                <select name="" id="" class="form-control">
                                    <option value="">6 Month</option>
                                    <option value="">3 Month</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col mt-2">
                                <table class="table table-striped" border="1">
                                    <tr>
                                        <td>@lang('clinic.price')</td>
                                        <td>6000.00 TK</td>
                                    </tr>
                                    <tr>
                                        <td>@lang('clinic.v_days')</td>
                                        <td>183 Days</td>
                                    </tr>
                                    <tr>
                                        <td>@lang('clinic.total_consultation')</td>
                                        <td>6 Time Visits</td>
                                    </tr>
                                    <tr>
                                        <td>@lang('clinic.packages')</td>
                                        <td>Doctor Consultion 6000 TK</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label for="amount">@lang('clinic.amount')</label>
                                <input type="number" name="amount" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label for="discount_amount">@lang('clinic.dis_amount')</label>
                                <input type="number" name="discount_amount" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label for="amount">@lang('clinic.paid_amount')</label>
                                <input type="number" name="paid_amount" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label for="discount_amount">@lang('clinic.due_amount')</label>
                                <input type="number" name="due_amount" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label for="amount">@lang('clinic.t_media')</label>
                                <select name="transaction_media" id="" class="form-control">
                                    <option value="">Select</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="discount_amount">@lang('clinic.p_date')</label>
                                <input type="date" name="payment_date" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label for="remarks">@lang('clinic.remarcks')</label>
                                <textarea name="" id="" rows="2" class="form-control"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary">Pay and Confirm</button>
                        <button type="button" class="btn btn-warning" data-dismiss="modal">Cancel</button>

                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
