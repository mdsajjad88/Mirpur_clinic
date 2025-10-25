@if(count($module_permissions) > 0)
    @php
        $module_role_permissions = [];
        if (!empty($role_permissions)) {
            $module_role_permissions = $role_permissions;
        }
    @endphp

    @foreach($module_permissions as $module_key => $permissions)
        @if($module_key === 'Clinic')
            {{-- ✅ Clinic Module with Custom CRUD Table --}}
            @php
                $clinic_permissions = $permissions;
                $count = count($clinic_permissions);
                Log::info('count: ' . $count);
                $crud_keywords = [
                    'create' => ['create', 'store', 'add'],
                    'view' => ['view', 'show', 'read'],
                    'edit' => ['edit', 'update'],
                    'delete' => ['delete', 'remove'],
                ];

                $crud_titles = [
                    'create' => 'Create',
                    'view' => 'View',
                    'edit' => 'Edit',
                    'delete' => 'Delete',
                ];

                $grouped_permissions = [];
                $non_crud_permissions = [];

                function extractGroupName($value) {
                    $parts = explode('.', $value);
                    return count($parts) > 2 ? $parts[0] . '.' . $parts[1] : $parts[0];
                }

                $temp_grouped = [];

                foreach ($clinic_permissions as $perm) {
                    $group = extractGroupName($perm['value']);
                    $temp_grouped[$group][] = $perm;
                }

                foreach ($temp_grouped as $group => $perms) {
                    $crud_map = [];
                    $other_perms = [];

                    foreach ($perms as $perm) {
                        $matched = false;
                        foreach ($crud_keywords as $type => $keywords) {
                            foreach ($keywords as $keyword) {
                                if (Str::endsWith($perm['value'], '.' . $keyword)) {
                                    $crud_map[$type] = $perm;
                                    $matched = true;
                                    break 2;
                                }
                            }
                        }
                        if (!$matched) {
                            $other_perms[] = $perm;
                        }
                    }

                    if (count($crud_map) >= 2) {
                        $grouped_permissions[$group] = $crud_map;
                    } else {
                        foreach ($crud_map as $perm) {
                            $non_crud_permissions[] = $perm;
                        }
                        foreach ($other_perms as $perm) {
                            $non_crud_permissions[] = $perm;
                        }
                    }
                }

                $permissionGroups = array_chunk($grouped_permissions, ceil(count($grouped_permissions) / 2), true);
            @endphp

            <h4><strong>Clinic Module Permissions - CRUD Table</strong></h4>
            <div class="container-fluid">
                <div class="row">
                    @foreach($permissionGroups as $chunk)
                        <div class="col-md-6 mb-4">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm">
                                    <thead class="bg-light">
                                        <tr>
                                            <th style="width: 25%;">Permission</th>
                                            @foreach($crud_titles as $label)
                                                <th class="text-center" style="width: 18%;">{{ $label }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($chunk as $group => $crud_map)
                                            @php
                                                $group_label = ucwords(str_replace(['.', '_'], ' ', $group));
                                            @endphp
                                            <tr>
                                                <td><strong>{{ $group_label }}</strong></td>
                                                @foreach($crud_titles as $type => $label)
                                                    <td class="text-center">
                                                        @if(isset($crud_map[$type]))
                                                            @php
                                                                $perm = $crud_map[$type];
                                                                $checked = in_array($perm['value'], $role_permissions ?? []);
                                                            @endphp
                                                            {!! Form::checkbox('permissions[]', $perm['value'], $checked, ['class' => 'input-icheck']) !!}
                                                        @else
                                                            &mdash;
                                                        @endif
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Non-CRUD or single CRUD permissions --}}
                @if(count($non_crud_permissions))
                    <h4><strong>Other Clinic Permissions</strong></h4>
                    <div class="row">
                        @foreach($non_crud_permissions as $perm)
                            <div class="col-md-3 mb-2">
                                <label>
                                    {!! Form::checkbox('permissions[]', $perm['value'], in_array($perm['value'], $role_permissions ?? []), ['class' => 'input-icheck']) !!}
                                    {{ $perm['label'] }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @else
            {{-- ✅ Other modules with default layout --}}
            <hr>
            <div class="row check_group">
                <div class="col-md-2">
                    <h4>{{ $module_key }}</h4>
                </div>
                <div class="col-md-10">
                    <div class="row">
                        @php $counter = 0; @endphp
                        @foreach($permissions as $module_permission)
                            @php
                                if(empty($role_permissions) && $module_permission['default']) {
                                    $module_role_permissions[] = $module_permission['value'];
                                }
                                $counter++;
                            @endphp
                            <div class="col-md-3">
                                <div class="checkbox">
                                    <label>
                                        @if(!empty($module_permission['is_radio']))
                                            {!! Form::radio('radio_option[' . $module_permission['radio_input_name'] . ']', $module_permission['value'], in_array($module_permission['value'], $module_role_permissions), ['class' => 'input-icheck']) !!}
                                            {{ $module_permission['label'] }}
                                        @else
                                            {!! Form::checkbox('permissions[]', $module_permission['value'], in_array($module_permission['value'], $module_role_permissions), ['class' => 'input-icheck']) !!}
                                            {{ $module_permission['label'] }}
                                        @endif
                                    </label>
                                </div>
                            </div>
                            @if($counter % 4 == 0)
                                </div><div class="row">
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    @endforeach
@endif
