<div class="">
    <div class="table-responsive">
        <div class="table-wrapper">
            <div class="table-title">
                <div class="row searchBar">
                    <div class="search-box" style="display: flex;">
                        <i class="material-icons">&#xE8B6;</i>
                        <input id="searchInput" type="text" style=" height: 34px;border-radius: 20px;padding-left: 35px;border-color: #ddd;box-shadow: none;" class="form-control" placeholder="Search&hellip;">
                        @include('client.client-filtering')
                    </div>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" id="openform" data-bs-target="#userFormModal">
                        Add Client
                    </button>
                    @include('client.client-add-form')
                </div>
            </div>
            <table style="margin-top: 10px;" class="table table-striped table-hover table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Contact</th>
                        <th>Email</th>
                        <th>Country</th>
                        <th>Address</th>
                        <th>Website URL</th>
                        <th class="customTH">Technology Used</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="userTable">
                </tbody>
            </table>
            <div style="display: flex;justify-content: space-between;">
                <div class="col-6">
                    Records per page
                    <select class="form-select" id="record_size" style="max-width: 80px">
                        <option value="5">5</option>
                        <option value="10" selected>10</option>
                        <option value="15">15</option>
                        <option value="20">20</option>
                    </select>
                </div>
                <div class="row mr-2">
                    <div class="col mt-2">
                        <div id="page-details" style="width: 250px;"></div>
                    </div>
                    <div class="col">
                        <nav class="d-flex justify-content-center">
                            <ul class="pagination" id="pagination">
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>