<div class="table-responsive">
    <div class="table-wrapper">
        <div class="table-title">
            <div class="row searchBar">
                <div class="search-box">
                </div>
                <button type="button" class="btn btn-primary adduserModal" data-toggle="modal" data-target="#addAdminUser">
                    Add User
                </button>
            </div>
        </div>

        <table style="margin-top: 10px;" class="table table-striped table-hover table-bordered">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Role</th>
                    <th>Email</th>
                    <th>Assign Role</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="superAdminTable">
            </tbody>
        </table>
    </div>
</div>
<script>
    $(document).ready(function() {
        $('.adduserModal').on('click',function(){
            $('#addUsersForm')[0].reset();
        });
    });
</script>