<div class="modal fade" id="addAdminUser" tabindex="-1" role="dialog" aria-labelledby="addAdminUserTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">Add User</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="addUsersForm" method="post">
                    @csrf
                    <div class="form-group">
                        <label for="exampleInputName1"><span style="color: red">*</span>Name<span style="color: red;padding-left:10px"><i>Required</i></span></label>
                        <input type="text" class="form-control" id="exampleInputName1" name="name" aria-describedby="nameHelp" placeholder="Enter name">
                        <div style="color:red" id="name-error"></div>
                    </div>
                    <div class="form-group">
                        <label for="exampleInputEmail1"><span style="color: red">*</span>Email<span style="color: red;padding-left:10px"><i>Required</i></span></label>
                        <input type="email" class="form-control" id="exampleInputEmail1" name="email" aria-describedby="EmailHelp" placeholder="Enter Email">
                        <div style="color:red" id="Email-error"></div>
                    </div>
                    <div class="form-group">
                        <label for="exampleInputRole1"><span style="color: red">*</span>Role<span style="color: red;padding-left:10px"><i>Required</i></span></label><br>
                        <select class="form-control" name="role">
                            <option value="Admin">Admin</option>
                            <option value="User">User</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="exampleInputPassword1"><span style="color: red">*</span>Password<span style="color: red;padding-left:10px"><i>Required</i></span></label>
                        <input type="password" class="form-control" id="exampleInputPassword1" name="password" aria-describedby="PasswordHelp" placeholder="Password" required>
                        <div style="color:red" id="Password-error"></div>
                        <div>
                            <button type="button" class="mt-2 btn btn-primary" id="generatePassword">Generate Password</button>
                            <button type="button" class="btn btn-warning mt-2" id="copyPassword">Copy Password</button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="closeBtn" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary" form="addUsersForm" id="submitBtn">Add User</button>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {

        $('#closeBtn, .close').on('click', function() {
            $('#addAdminUser').modal('hide');
            $('#addUsersForm')[0].reset();
        });

        $('#addAdminUser').on('hidden.bs.modal', function () {
            $('#addUsersForm')[0].reset();
        });

        $('.adduserModal').on('click', function() {
            $('#addUsersForm')[0].reset();
        });

        $('#generatePassword').on('click', function() {
            const password = generatePassword();
            $('#exampleInputPassword1').val(password);
        });

        $('#copyPassword').on('click', function() {
            const password = $('#exampleInputPassword1').val();
            navigator.clipboard.writeText(password)
                .then(() => {
                    toastr.options = {
                        'timeOut': 2000,
                        'closeButton': true,
                        'progressBar': true,
                        "positionClass": "toast-top-center",
                    };
                    toastr.success("", "Password copied to clipboard");
                })
                .catch(err => {
                    toastr.error('Failed to copy: ', err);
                });
        });

        function generatePassword() {
            const length = 12;
            const charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+<>?";
            let password = "";
            for (let i = 0; i < length; i++) {
                const randomIndex = Math.floor(Math.random() * charset.length);
                password += charset[randomIndex];
            }
            return password;
        }

        // Ensure form fields are empty when the modal is shown
        $('#addAdminUser').on('shown.bs.modal', function() {
            $('#addUsersForm')[0].reset();
        });
    });
</script>
