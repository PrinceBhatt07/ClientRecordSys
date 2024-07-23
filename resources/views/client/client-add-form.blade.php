<div class="modal fade" id="userFormModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" style="max-width: 800px">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Add Client Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="height:710px ; overflow-y:scroll">
                <form id="userForm" method="post">
                    @csrf
                    <div class="form-group">
                        <label for="exampleInputName1"><span style="color: red">*</span><strong>Name</strong></label>
                        <input type="text" class="form-control" id="exampleInputName1" name="name" aria-describedby="nameHelp" placeholder="Enter name">
                        <div style="color:red" id="name-error"></div>
                    </div>
                    <div class="form-group">
                        <label for="exampleInputEmail1"><span style="color: red">*</span><strong>Email
                                Id</strong></label>
                        <input type="email" class="form-control" id="exampleInputEmail1" name="email" aria-describedby="emailHelp" placeholder="Enter email Id">
                        <div style="color:red" id="email-error"></div>
                    </div>
                    <div class="form-group">
                        <label for="exampleInputContact1"><span style="color: red">*</span><strong>Contact
                                Number</strong></label>
                        <input type="tel" class="form-control" id="exampleInputContact1" name="contact" aria-describedby="contactHelp" placeholder="Enter contact number">
                        <div style="color:red" id="contact-error"></div>
                    </div>
                    <div class="form-group">
                        <label for="exampleInputAddress1"><strong>Address</strong></label>
                        <input type="text" class="form-control" id="exampleInputAddress1" name="address" aria-describedby="addressHelp" placeholder="Enter address">
                        <div style="color:red" id="address-error"></div>
                    </div>
                    <div class="form-group">
                        <label for="exampleInputCountry1"><span style="color: red">*</span><strong>Country</strong></label>
                        <input type="text" class="form-control" id="country" name="country" aria-describedby="countryHelp" placeholder="Enter Country">
                        <div class="countryDropdown" style="border:1px solid black;padding:5px 12px"></div>
                        <div style="color:red" id="country-error"></div>
                    </div>
                    <div class="form-group">
                        <label for="exampleInputWebsiteUrl1"><span style="color: red">*</span><strong>Website
                                Url</strong></label>
                        <input type="text" class="form-control" id="exampleInputWebsiteUrl1" name="websiteUrl" aria-describedby="websiteUrlHelp" placeholder="Enter WebsiteUrl">
                        <div style="color:red" id="websiteUrl-error"></div>
                    </div>
                    <div class="form-group">
                        <label for="exampleInputSkypeId1"><strong>SkypeId</strong></label>
                        <input type="text" class="form-control" id="exampleInputSkypeId1" name="skypeId" aria-describedby="skypeIdHelp" placeholder="Enter SkypeId">
                        <div style="color:red" id="skype-error"></div>
                    </div>
                    <div class="form-group">
                        <label for="exampleInputFacebookId1"><strong>FacebookId</strong></label>
                        <input type="text" class="form-control" id="exampleInputFacebookId1" name="facebookId" aria-describedby="facebookIdHelp" placeholder="Enter FacebookId">
                        <div style="color:red" id="facebook-error"></div>
                    </div>
                    <div class="form-group">
                        <label for="exampleInputLinkedinID1"><strong>Linkedin ID</strong></label>
                        <input type="text" class="form-control" id="exampleInputLinkedinID1" name="linkedinId" aria-describedby="linkedinIDHelp" placeholder="Enter Linkedin ID">
                        <div style="color:red" id="linkedin-error"></div>
                    </div>
                    <div id="project-wrapper">
                    </div>
                    <div>
                        <a id="add-project-button" class="btn btn-primary" style="color: white;">Add Project</a>
                    </div>
                </form>
            </div>
            <div class="modal-footer" style="justify-content:space-between">
                <div>
                    <button type="button" id="closeModal" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" id="save-button" class="btn btn-success" form="userForm">Save
                        Details</button>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        $('#openform').on('click', function() {
            $('#userFormModal').modal('show');
            $("#userForm")[0].reset();
        });

        $('#userFormModal').on('shown.bs.modal', function() {
            $(this).find('.modal-body').scrollTop(
                0);
        });
        $('#userFormModal').on('hidden.bs.modal', function() {
            $(this).find('.modal-body').scrollTop(
                0);
        });


        $('#country').on('input', function() {
            var countryInput = $(this).val().toLowerCase();
            if (countryInput.length === 0) {
                $('.countryDropdown').hide();
                return; // Stop further execution if the input is blank
            }

            $.ajax({
                url: "{{ route('client-details') }}",
                type: "GET",
                success: function(data) {
                    var clientDetails = data.data;
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
        });

        $(document).on('click', '.countryDropdown li', function() {
            var selectedCountry = $(this).text();
            $('#country').val(selectedCountry);
            $('.countryDropdown').hide();
        });

        // Hide the dropdown if clicked outside
        $(document).on('click', function(event) {
            $('.countryDropdown').hide();
        });


        function toggleRemoveButton() {
            if ($('.newProject-container').length <= 1) {
                $('.remove-button').css('display', 'none');
            } else {
                $('.remove-button').css('display', 'block');
            }
        }

        toggleRemoveButton();

        const isAdmin = @json(optional(Auth::user()) -> is_admin);
        const isSuperAdmin = @json(optional(Auth::user()) -> is_super_admin);


        function loadTable() {
            $.ajax({
                url: "{{ route('client-details') }}",
                type: "GET",
                success: function(data) {
                    if (data.success == true) {
                        var userData = data.data;
                        var html = "";

                        var filteredData = userData.filter(function(data) {
                            return data.is_archived === false;
                        });

                        if (filteredData.length > 0) {
                            filteredData.forEach(function(data, index) {
                                html += `<tr>
                                    <td>${index + 1}</td>
                                    <td>${data.name}</td>
                                    <td>${data.contact}</td>
                                    <td>${data.email}</td>
                                    <td>${data.country}</td>
                                    <td style="width: 160px">${data.address ? data.address : 'N/A'}</td>
                                    <td style="width: 100px">${data.website_url}</td>
                                    <td>`;
                                data.technologies.forEach(function(technology) {
                                    html += `<span class="bg-primary text-white text-xs fw-medium me-2 px-2 py-1 rounded">${technology.technology}</span>`;
                                });
                                html += `</td>
                                    <td style="display: flex; justify-content: space-evenly;padding-top: 25px;">`;
                                html += `<button type="button" class="viewUser" title="View" data-id="${data.id}"><i class="fa-solid fa-eye"></i></button>
                                    <button type="button" class="edit" title="Edit" data-id="${data.id}"><i class="fa-solid fa-pen-to-square"></i></button>`;
                                if (isAdmin || isSuperAdmin) {
                                    html += `<button type="button" class="archiveClient" title="Delete" data-name="${data.name}" data-id="${data.id}"><i class="fas fa-archive"></i></button>`;
                                }
                                html += `</td>
                                </tr>`;
                            });
                        } else {
                            html = `<tr>
                                <td colspan="9" rowspan="2" class="center-align"><b>No Details Found!</b></td>
                            </tr>`;
                        }

                        $('#userTable').html(html);

                        const total_records_tr = $('#userTable tr');
                        let records_per_page = 10;
                        let page_number = 1;
                        const total_records = total_records_tr.length;
                        let total_pages = Math.ceil(total_records / records_per_page);

                        generatePage();
                        DisplayRecords();

                        function DisplayRecords() {
                            let start_index = (page_number - 1) * records_per_page;
                            let end_index = start_index + (records_per_page - 1); // end_index should be exclusive
                            if (end_index >= total_records) {
                                end_index = total_records - 1;
                            }
                            let statement = '';
                            for (let i = start_index; i <= end_index; i++) {
                                statement += `<tr>${total_records_tr[i].innerHTML}</tr>`;
                            }

                            $('#userTable').html(statement);
                            $('.dynamic-item').removeClass('active');
                            $('#page' + page_number).addClass('active');

                            // Disable/enable previous and next buttons based on page_number and total_pages
                            $('#prevBtn').parent().toggleClass('disabled', page_number === 1);
                            $('#nextBtn').parent().toggleClass('disabled', page_number === total_pages);

                            $('#page-details').html(`Showing ${start_index + 1} to ${end_index + 1} of ${total_records} entries`);
                        }

                        function generatePage() {
                            let prevBtn = `<li class="page-item ${page_number === 1 ? 'disabled' : ''}">
                                <a class="page-link" id="prevBtn" href="javascript:void(0);">Prev</a>
                            </li>`;

                            let nextBtn = `<li class="page-item ${page_number === total_pages ? 'disabled' : ''}">
                                <a class="page-link" id="nextBtn" href="javascript:void(0);">Next</a>
                            </li>`;
                            let buttons = '';
                            for (let i = 1; i <= total_pages; i++) {
                                buttons += `<li class="page-item dynamic-item ${i === page_number ? 'active' : ''}" id="page${i}">
                                    <a class="page-link pageNumber" href="javascript:void(0);">${i}</a>
                                </li>`;
                            }

                            $('#pagination').html(prevBtn + buttons + nextBtn);
                        }

                        $(document).on('click', '#nextBtn', function() {
                            if (page_number < total_pages) {
                                page_number++;
                                DisplayRecords();
                            }
                        });

                        $(document).on('click', '#prevBtn', function() {
                            if (page_number > 1) {
                                page_number--;
                                DisplayRecords();
                            }
                        });

                        $(document).on('click', '.pageNumber', function() {
                            page_number = parseInt($(this).text());
                            DisplayRecords();
                        });

                        $('#record_size').on('change', function() {
                            records_per_page = parseInt($(this).val());
                            total_pages = Math.ceil(total_records / records_per_page);
                            page_number = 1;
                            generatePage();
                            DisplayRecords();
                        });

                    } else {
                        var html = `<tr>
                            <td colspan="9" rowspan="2" class="center-align"><b>No Details Found!</b></td>
                        </tr>`;
                        $('#userTable').html(html);
                    }
                }
            });
        }

        // ----------------------------------End of Loading user Table-----------------------------------------------//


        $('#closeModal').on('click', function() {
            $("#userForm")[0].reset();
        });

        const projectWrapper = $('#project-wrapper');
        let projectArray = [];
        const projectHTML = `
        <div class="newProject-container">
            <div class="newprojectBox">
                <div class="form-group">
                    <label for="exampleInputProjectTitle1"><span style="color: red">*</span><strong>Project Title</strong></label>
                    <input rows="4" class="form-control project-title"  name="projectTitle" aria-describedby="ProjectTitleHelp" placeholder="Enter Project Title">
                    <div style="color:red" class="projectTitle-error"></div>
                </div>
                <div class="form-group">
                    <label for="exampleInputProjectDescription1"><span style="color: red">*</span><strong>Project Description</strong></label>
                    <textarea rows="4" class="form-control project-description" name="projectDescription" aria-describedby="ProjectDescriptionHelp" placeholder="Enter Project Description"></textarea>
                    <div style="color:red" class="projectDescription-error"></div>
                </div>
            </div>
            <div class="form-group dropdown-container">
                <label for="exampleInputTechnologyUsed1"><span style="color: red">*</span><strong>Technology Used</strong></label>
                <div class="selected-tags" style="padding: 10px;"></div>
                <input type="text" class="form-control technology-input" id="exampleInputTechnologyUsed1" aria-describedby="technologyUsedHelp" placeholder="Enter Technology Used" autocomplete="off">
                <div class="dropdown-content" style="display:none;flex-direction: column; width:100%; padding:8px ;background-color:#f0eeee;">
                    <div class="add-new-technology">Add Technology</div>
                    @foreach ($technologies as $technology)
                        <label><input style="margin-right: 7px;" type="checkbox" name="{{ $technology['technology'] }}" value="{{ $technology['id'] }}">{{ $technology['technology'] }}</strong></label>
                    @endforeach
                </div>
            </div>
            <button class="btn btn-danger remove-button" >Remove</button>
        </div>`;

        function updateRemoveButtons() {
            if (projectArray.length === 1) {
                projectArray[0].find('.remove-button').hide();
            } else {
                projectArray.forEach(project => project.find('.remove-button').show());
            }
        }

        function collectFormData() {
            const formData = [];

            projectArray.forEach(project => {
                const projectData = {
                    technologies: [],
                    projects: []
                };

                // Collect project title and description
                const projectTitle = project.find('.project-title').val().trim();
                const projectDescription = project.find('.project-description').val().trim();

                projectData.projects.push({
                    projectTitle: projectTitle,
                    projectDescription: projectDescription
                });
                
                // Collect selected technologies
                const selectedTechnologies = project.find(
                    '.dropdown-content input[type="checkbox"]:checked');
                selectedTechnologies.each(function() {
                    const technologyId = $(this).val();
                    const technologyName = $(this).parent().text().trim();

                    projectData.technologies.push({
                        id: technologyId,
                        name: technologyName
                    });
                });

                formData.push(projectData);
            });

            return formData;
        }

        function scrollToModalBottom() {
            var modalContent = document.querySelector('#userFormModal .modal-body');
            modalContent.scrollTo(0, modalContent.scrollHeight);
        }

        function addProject() {

            // Add new project
            const newProject = $(projectHTML);
            projectWrapper.append(newProject);
            projectArray.push(newProject);
            updateRemoveButtons();
            // Add functionality to remove project
            newProject.find('.remove-button').on('click', function() {
                newProject.remove();
                projectArray = projectArray.filter(project => project[0] !== newProject[0]);
                updateRemoveButtons();
            });

            // Add functionality to add technology as tag
            newProject.find('.dropdown-content').on('click', 'label', function() {
                const checkbox = $(this).find('input[type="checkbox"]');
                const technologyName = checkbox.attr('name');
                const selectedTagsContainer = newProject.find('.selected-tags');

                if (checkbox.prop('checked')) {
                    const inputField = newProject.find('.technology-input');
                    inputField.val('');
                    // Add tag
                    const tagHTML =
                        `<span class="selected-tag"><span>${technologyName}</span><button class="remove-tag">&times;</button></span>`;
                    selectedTagsContainer.append(tagHTML);

                    // Add functionality to remove tag
                    selectedTagsContainer.find('.selected-tag:last .remove-tag').on('click',
                        function() {
                            $(this).parent().remove();
                            // Uncheck checkbox when tag is removed
                            checkbox.prop('checked', false);
                        });
                } else {
                    // Remove tag if unchecked
                    selectedTagsContainer.find(`.selected-tag:contains(${technologyName})`).remove();
                }
            });

            // Add functionality for 'Add Technology' button
            newProject.find('.add-new-technology').on('click', function() {
                const inputField = newProject.find('.technology-input');
                const newTechnology = inputField.val().trim();
                const dropdownContent = newProject.find('.dropdown-content');

                if (newTechnology !== '') {
                    // Simulate adding a new technology (you may need an AJAX call to actually add it)
                    const newCheckbox = $(
                        `<label><input style="margin-right: 7px;" type="checkbox" name="${newTechnology}" value="${newTechnology}">${newTechnology}</label>`
                    );
                    dropdownContent.append(newCheckbox);

                    // Clear input field after adding new technology
                    inputField.val('');

                    // Trigger click on the newly added checkbox to add it as tag
                    newCheckbox.find('input[type="checkbox"]').click();
                }
            });

            newProject.find('.technology-input').on('input', function() {
                const inputField = $(this);
                const dropdownContent = inputField.next('.dropdown-content');
                const filter = inputField.val().toLowerCase().trim();

                if (filter !== '') {
                    dropdownContent.show(); // Show the dropdown content when there is a filter
                    dropdownContent.children('label').each(function() {
                        const technology = $(this).text().toLowerCase();
                        if (technology.startsWith(filter)) {
                            $(this)
                                .show(); // Show the technology label if it matches the filter
                        } else {
                            $(this)
                                .hide(); // Hide the technology label if it doesn't match the filter
                        }
                    });

                    // Check if any technologies are visible
                    const visibleTechnologies = dropdownContent.children('label').filter(':visible');
                    if (visibleTechnologies.length === 0) {
                        // If no technologies match the filter, show 'Add Technology'
                        dropdownContent.find('.add-new-technology').show();
                    } else {
                        dropdownContent.find('.add-new-technology').hide();
                    }
                } else {
                    // If filter is empty, hide dropdown and 'Add Technology'
                    dropdownContent.hide();
                    dropdownContent.find('.add-new-technology').hide();
                }
            });
            scrollToModalBottom();

        }


        // Initialize with one project by default
        addProject();

        $('#add-project-button').on('click', addProject);



        // ----------------------------------End For Technologies-----------------------------------------------//

        $('#userForm').submit(function(e) {
            e.preventDefault();

            const projectFormData = collectFormData();
            var projects = JSON.stringify(projectFormData);

            const formData = new FormData(this);
            formData.append('projects', projects);

            $.ajax({
                url: "{{ route('add-client') }}",
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
                            "positionClass": "toast-top-center",
                        };
                        toastr.success("", response.message);
                        $("#userForm")[0].reset();
                        $('#userFormModal').modal('hide');
                        $('#tags-container').children().remove();
                        $('#options').hide();
                        data = [];
                        $('.selected-tags').empty();
                        loadTable();

                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(response) {
                    if (response.responseJSON) {
                        var errors = response.responseJSON.message;
                        $('#name-error').text(errors.name ? errors.name[0] : '');
                        $('#email-error').text(errors.email ? errors.email[0] : '');
                        $('#contact-error').text(errors.contact ? errors.contact[0] : '');
                        $('#address-error').text(errors.address ? errors.address[0] : '');
                        $('#country-error').text(errors.country ? errors.country[0] : '');
                        $('#websiteUrl-error').text(errors.websiteUrl ? errors.websiteUrl[
                            0] : '');
                    }
                    setTimeout(() => {
                        $('#name-error').text('');
                        $('#email-error').text('');
                        $('#contact-error').text('');
                        $('#address-error').text('');
                        $('#country-error').text('');
                        $('#websiteUrl-error').text('');
                        $('#skype-error').text('');
                        $('#linkedin-error').text('');
                        $('#facebook-error').text('');
                        $('#technology-error').text('');
                        $('#projectTitle-error').text('');
                        $('#projectDescription-error').text('');
                    }, 3000);
                }
            });
        });
    });
</script>