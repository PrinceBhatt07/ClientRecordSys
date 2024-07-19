<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" style="max-width: 800px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUserModalLabel">Edit Client</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="height:710px; overflow-y:scroll">
                <form id="editUserForm">
                    <input type="hidden" id="editUserId" name="userId">
                    <div class="mb-3">
                        <label for="editUserName" class="form-label"><span style="color: red">*</span><strong>Name</strong></label>
                        <input type="text" class="form-control" id="editUserName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="editUserContact" class="form-label"><span style="color: red">*</span><strong>Contact</strong></label>
                        <input type="text" class="form-control" id="editUserContact" name="contact" required>
                    </div>
                    <div class="mb-3">
                        <label for="editUserEmail" class="form-label"><span style="color: red">*</span><strong>Email</strong></label>
                        <input type="email" class="form-control" id="editUserEmail" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="editUserCountry" class="form-label"><span style="color: red">*</span><strong>Country</strong></label>
                        <input type="text" class="form-control" id="editUserCountry" name="country" required>
                        <div class="countryDropdown" style="border:1px solid black; padding:5px 12px; display: none;"></div>
                    </div>
                    <div class="mb-3">
                        <label for="editUserAddress" class="form-label"><strong>Address</strong></label>
                        <input type="text" class="form-control" id="editUserAddress" name="address">
                    </div>
                    <div class="mb-3">
                        <label for="editUserWebsite" class="form-label"><strong>Website</strong></label>
                        <input type="text" class="form-control" id="editUserWebsite" name="website_url">
                    </div>
                    <div class="mb-3">
                        <label for="editUserSkype" class="form-label"><strong>Skype ID</strong></label>
                        <input type="text" class="form-control" id="editUserSkype" name="skype_id">
                    </div>
                    <div class="mb-3">
                        <label for="editUserFacebook" class="form-label"><strong>Facebook URL</strong></label>
                        <input type="text" class="form-control" id="editUserFacebook" name="facebook_url">
                    </div>
                    <div class="mb-3">
                        <label for="editUserLinkedin" class="form-label"><strong>LinkedIn URL</strong></label>
                        <input type="text" class="form-control" id="editUserLinkedin" name="linkedin_url">
                    </div>
                    <div id="editproject-wrapper"></div>
                </form>
            </div>
            <div class="modal-footer" style="display: flex; justify-content:space-between;">
                <div>
                    <button id="editno-options-button" class="btn btn-primary">Add Project</button>
                </div>
                <div>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" form="editUserForm">Save changes</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#editUserCountry').on('input', function() {
            var countryInput = $(this).val().toLowerCase();
            if (countryInput === '') {
                $('.countryDropdown').hide();
                return; // Stop further execution if the input is blank
            }

            if (countryInput.length > 0) {
                $.ajax({
                    url: "{{ route('client-details') }}",
                    type: "GET",
                    success: function(data) {
                        var clientDetails = data.data.data;
                        var countryList = '';
                        var seenCountries = new Set();

                        clientDetails.forEach(function(client) {
                            var country = client.country.toLowerCase();
                            if (country.includes(countryInput) && !seenCountries.has(country)) {
                                countryList += '<li>' + client.country + '</li>';
                                seenCountries.add(country);
                            }
                        });

                        if (countryList.length > 0) {
                            $('.countryDropdown').html('<ul>' + countryList + '</ul>').show();
                        } else {
                            $('.countryDropdown').hide();
                        }
                    }
                });
            } else {
                $('.countryDropdown').hide();
            }
        });

        $(document).on('click', '.countryDropdown li', function() {
            var selectedCountry = $(this).text();
            $('#editUserCountry').val(selectedCountry);
            $('.countryDropdown').hide();
        });

        // Hide the dropdown if clicked outside
        $(document).on('click', function(event) {
            if (!$(event.target).closest('.form-group').length) {
                $('.countryDropdown').hide();
            }
        });
    });
</script>
