  <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">

          <!-- Modal Body -->
          <div class="modal-body">
              <div class="container-fluid">

                  <!-- Header Section -->
                  <div class="text-center" style="margin-bottom:20px;">
                      <h2 style="font-weight:bold;color:#337ab7;margin-bottom:5px;">Nutrition Prescription</h2>
                      <p><strong>Date:</strong> {{ $prescription->prescription_date }}</p>
                      <p><strong>Doctor:</strong>
                          {{ trim(($doctor->first_name ?? '') . ' ' . ($doctor->last_name ?? '')) }}</p>
                      <p><strong>Nutritionist:</strong>
                          {{ $prescription->editor
                              ? trim(($prescription->editor->first_name ?? '') . ' ' . ($prescription->editor->last_name ?? ''))
                              : ($prescription->creator
                                  ? trim(($prescription->creator->first_name ?? '') . ' ' . ($prescription->creator->last_name ?? ''))
                                  : '-') }}
                      </p>
                  </div>

                  <!-- Patient Information -->
                  <div class="panel panel-default" style="padding:15px; margin-bottom:20px;">
                      <div class="panel-heading"><strong>Patient Information</strong></div>
                      <div class="panel-body">
                          <div class="row">
                              <div class="col-md-6">
                                  <p><strong>Name:</strong> {{ $patient->name ?? '-' }}</p>
                                  <p><strong>Disease:</strong>
                                      @forelse ($diseases as $disease)
                                          <span class="label label-info">{{ $disease->name }}</span>
                                      @empty
                                          <span class="label label-default">N/A</span>
                                      @endforelse
                                  </p>
                                  <p><strong>Age:</strong> {{ $profile->age ?? '-' }}</p>
                                  <p><strong>Gender:</strong> {{ $profile->gender ? ucfirst($profile->gender) : '-' }}
                                  </p>
                              </div>
                              <div class="col-md-6 text-center">
                                  <img src="data:image/png;base64,{{ DNS1D::getBarcodePNG($patient->contact_id, 'C39', 1.0, 30, [39, 48, 54], false) }}"
                                      class="img-responsive center-block"
                                      style="border:1px solid #ddd; padding:5px; background:#fff;">
                                  <p><strong>Height:</strong>
                                      {{ $presOrg->current_height_feet ? $presOrg->current_height_feet . ' ft ' . $presOrg->current_height_inches . ' in' : '-' }}
                                  </p>
                                  <p><strong>Weight:</strong> {{ $presOrg->current_weight ?? '-' }} kg</p>
                              </div>
                          </div>
                      </div>
                  </div>
                  <h5>@lang('clinic::lang.guidline_description')</h5>
                  <div>
                      <p>{!! $prescription->guidline_description ?? '-' !!}</p>
                  </div>
                  <!-- Food Plan -->
                  <h5>Food Plan</h5>
                  <div class="table-responsive" style="margin-bottom:20px;">
                      <table class="table table-bordered table-striped table-hover">
                          <thead class="thead-default">
                              <tr>

                                  <th>Products</th>
                                  <th>Time</th>
                                  <th>Instructions</th>
                              </tr>
                          </thead>
                          <tbody>
                              @foreach ($foods as $food)
                                  <tr>
                                      <td>{{ $food->product_name ?? '-' }}</td>
                                      <td>{{ $food->meal_time ?? '-' }}</td>
                                      <td>{{ $food->instructions ?? '-' }}</td>
                                  </tr>
                              @endforeach
                          </tbody>
                      </table>
                  </div>

                  <!-- Lifestyle Plan -->
                  <h5>Lifestyle Recommendations</h5>
                  <div class="table-responsive" style="margin-bottom:20px;">
                      <table class="table table-bordered table-striped table-hover">
                          <thead class="thead-success">
                              <tr>
                                  <th>Products</th>
                                  <th>Time</th>
                                  <th>Instructions</th>
                              </tr>
                          </thead>
                          <tbody>
                              @foreach ($lifestyles as $lifestyle)
                                  <tr>
                                      <td>{{ $lifestyle->product_name ?? '-' }}</td>
                                      <td>{{ $lifestyle->meal_time ?? '-' }}</td>

                                      <td>{{ $lifestyle->instructions ?? '-' }}</td>
                                  </tr>
                              @endforeach
                          </tbody>
                      </table>
                  </div>
              </div>
          </div>

          <!-- Modal Footer -->
          <div class="modal-footer">
              <a href="{{ route('nutritionist-visit.print', $prescription->prescription_id) }}" class="btn btn-success"
                  target="_blank">Print</a>
              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          </div>

      </div>
  </div>
