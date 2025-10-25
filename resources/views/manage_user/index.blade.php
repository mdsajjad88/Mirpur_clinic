@extends('clinic::layouts.app2')
@section('title', __( 'user.users' ))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang( 'user.users' )
        <small>@lang( 'user.manage_users' )</small>
    </h1>
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
    </ol> -->
</section>

<!-- Main content -->
<section class="content">




<div class="row">
    <div class="col-md-12">
        <!-- Custom Tabs -->
        <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
                <li class="active">
                    <a href="#user_list_tab" data-toggle="tab" aria-expanded="true"><i class="fa fa-cubes" aria-hidden="true"></i> @lang('user.all_users')</a>
                </li>
                <li>
                    <a href="#user_archive" data-toggle="tab" aria-expanded="true"><i class="fas fa-file-archive"> </i> Archive</a>
                </li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane active" id="user_list_tab">
                    @can('user.create')                          
                        <a class="btn btn-primary pull-right" href="{{action([\App\Http\Controllers\ManageUserController::class, 'create'])}}">
                                    <i class="fa fa-plus"></i> @lang('messages.add')</a>
                        <br><br>
                    @endcan
                    @include('manage_user.partials.user_list')
                </div>
                <div class="tab-pane" id="user_archive">
                    @include('manage_user.partials.user_archive')
                </div>
            </div>
        </div>
    </div>
</div>


    <div class="modal fade user_modal" tabindex="-1" role="dialog" 
    	aria-labelledby="gridSystemModalLabel">
    </div>

</section>
<!-- /.content -->
@stop
@section('javascript')
<script type="text/javascript">
    //Roles table
    $(document).ready( function(){
        var users_table = $('#users_table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: '/users',
                    columnDefs: [ {
                        "targets": [4],
                        "orderable": false,
                        "searchable": false
                    } ],
                    "columns":[
                        {"data":"username"},
                        {"data":"full_name"},
                        {"data":"role"},
                        {"data":"email"},
                        {"data":"action"}
                    ]
                });


        var users_archive_table = $('#users_archive_table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: '/users/archive',
                    columnDefs: [ {
                        "targets": [4],
                        "orderable": false,
                        "searchable": false
                    } ],
                    "columns":[
                        {"data":"username"},
                        {"data":"full_name"},
                        {"data":"role"},
                        {"data":"email"},
                        {"data":"action"}
                    ]
                });
        $(document).on('click', 'button.delete_user_button', function(){
            swal({
              title: LANG.sure,
              text: LANG.confirm_delete_user,
              icon: "warning",
              buttons: true,
              dangerMode: true,
            }).then((willDelete) => {
                if (willDelete) {
                    var href = $(this).data('href');
                    var data = $(this).serialize();
                    $.ajax({
                        method: "DELETE",
                        url: href,
                        dataType: "json",
                        data: data,
                        success: function(result){
                            if(result.success == true){
                                toastr.success(result.msg);
                                users_table.ajax.reload();
                                users_archive_table.ajax.reload();
                            } else {
                                toastr.error(result.msg);
                            }
                        }
                    });
                }
             });
        });

        $(document).on('click', 'button.Force_delete_user_button', function(){
        swal({
            title: LANG.sure,
            text: LANG.confirm_delete_user,
            icon: "warning",
            buttons: true,
            dangerMode: true,
        }).then((willDelete) => {
            if (willDelete) {
                var href = $(this).data('href');

                // Send the CSRF token along with the request
                $.ajax({
                    method: "DELETE",
                    url: href,
                    dataType: "json",
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'), // Add CSRF token here
                    },
                    success: function(result){
                        if(result.success == true){
                            toastr.success(result.msg);
                            users_table.ajax.reload();
                            users_archive_table.ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                    },
                    error: function(xhr, status, error) {
                        toastr.error('Failed to delete the user permanently.');
                    }
                });
            }
        });
    });


    $(document).on('click', 'button.restore_user_button', function(){
        swal({
            title: LANG.sure,
            text: LANG.confirm_restore_user,  // A localized message for restoring user
            icon: "warning",
            buttons: true,
            dangerMode: false,  // Not a dangerous action
        }).then((willRestore) => {
            if (willRestore) {
                var href = $(this).data('href');
                
                // Send an AJAX request to the server
                $.ajax({
                    method: "POST",  // Using POST for restoration, you can change this based on your route
                    url: href,
                    dataType: "json",
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),  // Add CSRF token
                        _method: 'PUT'  // Use PUT method since it's a restoration
                    },
                    success: function(result){
                        if(result.success == true){
                            toastr.success(result.msg);  // Show success notification
                            users_table.ajax.reload();  // Reload the users DataTable
                            users_archive_table.ajax.reload();  // Reload the archived users DataTable
                        } else {
                            toastr.error(result.msg);  // Show error notification
                        }
                    },
                    error: function(xhr, status, error) {
                        toastr.error('Failed to restore the user.');
                    }
                });
            }
        });
    });



        
    });
    
    
</script>
@endsection
