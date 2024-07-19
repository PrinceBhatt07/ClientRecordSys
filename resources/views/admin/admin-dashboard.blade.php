<x-app-layout>

    <input type="hidden" id="user_id" value="{{ Auth::user()->id }}">
    <!-- Show All Users -->
    @include('admin.admin-users-table')

    <!---- Add User Modal ---->
    @include('admin.admin-add-users')

    <!-- Edit User Modal -->
    @include('admin.admin-edit-users')

    <!-- Confirm Deletion Modal -->
    @include('admin.admin-confirm-deletion')
</x-app-layout>

<script>
    $(document).ready(function() {
        function loadTable(searchTerm = '') {
            $.ajax({
                url: "{{ route('admin-get-all-users') }}",
                type: "GET",
                data: {
                    search: searchTerm
                },
                success: function(data) {
                    if (data.success) {
                        var html = "";
                        data.data.forEach(function(user) {
                            var role = user.is_super_admin ? 'Super Admin' : (user
                                .is_admin ? 'Admin' : 'User');
                            var user_id = $('#user_id').val();
                            console.log(user_id,'----------')
                            console.log(user.id,'p----------')
                            if (!user.is_super_admin) {
                                html += `<tr>
                                    <td style="width:350px">${user.name}</td>
                                    <td style="width:350px">${role}</td>
                                    <td style="width:350px">${user.email}</td>
                                    <td style="width:350px">
                                         ${role === 'Admin' ? 'Admin' : 'User'}
                                    </td>
                                    <td style="width:350px">
                                        <button type="button" class="btn btn-success edit-user" data-id="${user.id}" ${user.id == user_id  ? 'disabled' : ''}>Edit</button>
                                        <button type="button" class="btn btn-danger delete-user" data-name="${user.name}" data-id="${user.id}" ${user.id == user_id ? 'disabled' : ''}>Delete</button>
                                    </td>
                                </tr>`;
                            }
                        });
                        $('#AdminTable').html(html);
                    } else {
                        $('#AdminTable').html('<tr><td colspan="5">No User Found!</td></tr>');
                    }
                }
            });
        }
        loadTable();


        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        
        $('#searchInput').on('input', function() {
            loadTable($(this).val());
        });

        $('#addUsersForm').submit(function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            $.ajax({
                url: "{{ route('admin-add-users') }}",
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        toastr.options = {
                            'timeOut': 2000,
                            'closeButton': true,
                            'progressBar': true,
                            'positionClass': 'toast-top-center'
                        };
                        toastr.success("", response.message);
                        $("#addUsersForm")[0].reset();
                        $('#addAdminUser').css('display', 'none');
                        $('.modal-backdrop').remove();
                        loadTable();
                    } else {
                        toastr.error(response.message);
                    }
                }
            });
        });

        // -----------------------------Edit User ---------------------------------------------------/
        $('#AdminTable').on('click', '.edit-user', function() {
            var id = $(this).data('id');
            $('#editUserModal').modal('show');
            $('#editUserId').val(id);

            $.ajax({
                url: "{{ route('admin-edit-user') }}",
                type: "GET",
                data: {
                    id: id
                },
                success: function(response) {
                    if (response.success) {
                        $('#editUserName').val(response.data.name);
                        $('#editUserEmail').val(response.data.email);
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(xhr, status, error) {
                    toastr.error('An error occurred: ' + xhr.responseText);
                }
            });
        });

        $('#editUserForm').submit(function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            $.ajax({
                url: "{{ route('admin-update-user') }}",
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        toastr.options = {
                            'timeOut': 2000,
                            'closeButton': true,
                            'progressBar': true,
                            'positionClass': 'toast-top-center'
                        };
                        toastr.success("", response.message);
                        $("#editUserForm")[0].reset();
                        $('.modal').modal('hide');
                        loadTable();
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(response) {
                    if (response.responseJSON.errors) {
                        $('#name-error').text(response.responseJSON.errors.name);
                        $('#email-error').teexampleModalCenterxt(response.responseJSON
                            .errors.email);
                    } else {
                        toastr.error('An error occurred. Please try again.');
                    }
                }
            });
        });

        // ----------------------------------------------------- Delete User ---------------------------------------------------------------//

        var userIdToDelete = null;

        $('#AdminTable').on('click', '.delete-user', function(e) {
            e.preventDefault();
            $('#confirmDeleteBodyForUser').html('');
            userIdToDelete = $(this).data('id');
            userNameToDelete = $(this).attr('data-name');
            $('#confirmDeleteModal').modal('show');
            $('#confirmDeleteBodyForUser').append('Are you sure you want to delete <strong>' + userNameToDelete + ' </strong>?');
        });


        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });


        $('#confirmDeleteButton').on('click', function() {
            if (userIdToDelete) {
                $.ajax({
                    url: "{{ route('admin-delete-user') }}",
                    type: "POST",
                    data: {
                        id: userIdToDelete
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.options = {
                                timeOut: 2000,
                                closeButton: true,
                                progressBar: true,
                                positionClass: "toast-top-center",
                            };
                            toastr.success(response.message);

                            loadTable();
                        } else {

                            toastr.error(response.message ||
                                'An error occurred.');
                        }
                    },
                    error: function(xhr, status, error) {

                        toastr.error(
                            'Failed to delete user. Please try again.');
                    },
                    complete: function() {

                        $('#confirmDeleteModal').modal('hide');
                        userIdToDelete = null;
                    }
                });
            }
        });

    });
</script>