<x-app-layout>
<style>
        .newProject-container{
    display: flex;
    flex-direction: column;
  }
.newProject-container .remove-button{
    align-self: end;
}
    </style>
    <!-- Client Dashboard -->
    @include('client.cleint-dashboard')

    <!-- Confirm Deletion Modal -->
    @include('client.client-confirm-modal')

    <!-- View User Modal -->
    @include('client.client-view')

    <!-- Edit User Modal -->
    @include('client.client-edit')

</x-app-layout>

<!-- <script src="{{ asset('js/dashboard.js')}}"></script> -->
<script>
    $(document).ready(function() {

        $('#edit').on('click', function() {
            $('#editUserModal').modal('show');
        });

        $('#editUserModal').on('shown.bs.modal', function() {
            $(this).find('.modal-body').scrollTop(
                0); // Reset scroll position to top after modal is shown
        });

        $('#editUserModal').on('hidden.bs.modal', function() {
            $(this).find('.modal-body').scrollTop(
                0); // Reset scroll position to top when modal is hidden
        });

        $('#searchInput').on('input', function(e) {
            loadTable(e.target.value)
        });

        // Set up CSRF token for all AJAX requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        const urlParams = new URLSearchParams(window.location.search);
        const page = urlParams.get('page');
        const isAdmin = @json(optional(Auth::user()) -> is_admin);
        const isSuperAdmin = @json(optional(Auth::user()) -> is_super_admin);

        function loadTable(searchTerm) {
            $.ajax({
                url: "{{ route('client-details') }}",
                type: "GET",
                data: {
                    searchTerm: searchTerm
                },
                success: function(data) {
                    if (data.success == true) {
                        var userData = data.data;
                        console.log(userData);
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

        loadTable();
        // ---------------------------------------For Archiving User------------------------------------------//
        var clientIdToArchive = null;
        var clientNameToArchive = null;


        $(document).on('click', '.archiveClient', function(e) {
            e.preventDefault();
            clientIdToArchive = $(this).data('id');
            clientNameToArchive = $(this).attr('data-name');

            $('#confirmModalLabel').html('');
            $('.confirmBody').html('');

            var heading = `Confirm Archivement`;
            $('#confirmModalLabel').append(heading);

            var message = `Are you sure you want to delete <strong>${clientNameToArchive}</strong>?`;
            $('.confirmBody').append(message);
            $('#confirmModal').modal('show');
        });

        $('#confirmButton').on('click', function() {
            if (clientIdToArchive) {
                $.ajax({
                    url: "{{ route('archive-client') }}",
                    type: "POST",
                    data: {
                        clientId: clientIdToArchive
                    },
                    success: function(response) {
                        if (response.success === true) {
                            toastr.options = {
                                'timeOut': 2000,
                                'closeButton': true,
                                'progressBar': true,
                                "positionClass": "toast-top-center",
                            };
                            toastr.success("", response.message);
                            loadTable();
                        }
                    }
                });
                $('#confirmModal').modal('hide');
                clientIdToArchive = null;
            }
        });
        // ---------------------------------------End For Archiving User------------------------------------------//

        //---------------------------- Function to load user details for viewing--------------------------------//

        $(document).on('click', '.viewUser', function(e) {
            e.preventDefault();
            var userId = $(this).data('id');
            $.ajax({
                url: "{{ route('view-client') }}",
                type: "GET",
                data: {
                    userId: userId
                },
                success: function(response) {
                    if (response.success) {
                        var user = response.data;
                        console.log(user, '---------------');
                        var html = '<table class="table table-bordered">';

                        function formatDate(dateStr) {
                            const options = {
                                year: 'numeric',
                                month: 'long',
                                day: 'numeric',
                                hour: '2-digit',
                                minute: '2-digit',
                                timeZone: 'Asia/Kolkata',
                                timeZoneName: 'short'
                            };
                            const date = new Date(dateStr);
                            return date.toLocaleDateString('en-US', options).replace('IST', 'IST').replace('AM', 'at').replace('PM', 'at');
                        }


                        if (user.name) {
                            html += `
                        <tr>
                            <th>Name</th>
                            <td>${user.name}</td>
                        </tr>`;
                        }
                        if (user.contact) {
                            html += `
                        <tr>
                            <th>Contact</th>
                            <td>${user.contact}</td>
                        </tr>`;
                        }
                        if (user.email) {
                            html += `
                        <tr>
                            <th>Email</th>
                            <td>${user.email}</td>
                        </tr>`;
                        }
                        if (user.country) {
                            html += `
                        <tr>
                            <th>Country</th>
                            <td>${user.country}</td>
                        </tr>`;
                        }
                        if (user.address) {
                            html += `
                        <tr>
                            <th>Address</th>
                            <td>${user.address}</td>
                        </tr>`;
                        }
                        if (user.website_url) {
                            html += `
                        <tr>
                            <th>Website URL</th>
                            <td>${user.website_url}</td>
                        </tr>`;
                        }
                        if (user.facebook_url) {
                            html += `
                        <tr>
                            <th>Facebook URL</th>
                            <td>${user.facebook_url}</td>
                        </tr>`;
                        }
                        if (user.linkedin_url) {
                            html += `
                        <tr>
                            <th>LinkedIn URL</th>
                            <td>${user.linkedin_url}</td>
                        </tr>`;
                        }
                        if (user.skype_id) {
                            html += `
                        <tr>
                            <th>Skype ID</th>
                            <td>${user.skype_id}</td>
                        </tr>`;
                        }
                        if (user.projects && user.projects.length > 0) {
                            html += `
                        <tr>
                            <th>Projects</th>
                            <td>`;
                            user.projects.forEach(project => {
                                if (project.project_title || project.project_description || (project.technologies && project.technologies.length > 0)) {
                                    html += `
                                <table class="table table-bordered" style="background-color: #e6e1e1;margin-top: 5px;">
                                    ${project.project_title ? `
                                    <tr>
                                        <th>Project Title</th>
                                        <td>${project.project_title}</td>
                                    </tr>` : ''}
                                    ${project.project_description ? `
                                    <tr>
                                        <th>Project Description</th>
                                        <td>${project.project_description}</td>
                                    </tr>` : ''}
                                    ${project.technologies && project.technologies.length > 0 ? `
                                    <tr>
                                        <th>Technologies Used in Project</th>
                                        <td>${project.technologies.map(t => t.technology).join(', ')}</td>
                                    </tr>` : ''}
                                </table>`;
                                }
                            });
                            html += `</td>
                        </tr>`;
                        }
                        if (response.createdBy) {
                            const formattedDate = formatDate(user.created_at);
                            html += `
                        <tr>
                            <th>Created Date</th>
                            <td>${formattedDate} By <strong>${response.createdBy}</strong></td>
                        </tr>`;
                        }
                        if (user.updated_by) {
                            const formattedDate = formatDate(user.updated_at);
                            html += `
                        <tr>
                            <th>Last Updated</th>
                            <td>${formattedDate} By <strong>${user.updated_by}</strong></td>
                        </tr>`;
                        }

                        html += '</table>';
                        $('#viewUserDetails').html(html);
                        $('#viewUserModal').modal('show');
                    }
                }
            });
        });

        //---------------------------- End of Function to load user details for viewing--------------------------------//

        let editingData = [];

        $(document).on('click', '.edit', function(e) {
            e.preventDefault();

            const userId = $(this).data('id');
            $.ajax({
                url: "{{ route('edit-client') }}",
                type: "GET",
                data: {
                    userId: userId
                },
                success: function(response) {
                    if (response.success) {
                        const user = response.data;
                        $('#editUserId').val(user.id);
                        $('#editUserName').val(user.name);
                        $('#editUserContact').val(user.contact);
                        $('#editUserEmail').val(user.email);
                        $('#editUserCountry').val(user.country);
                        $('#editUserAddress').val(user.address);
                        $('#editUserWebsite').val(user.website_url);
                        $('#editUserSkype').val(user.skype_id);
                        $('#editUserFacebook').val(user.facebook_url);
                        $('#editUserLinkedin').val(user.linkedin_url);


                        editingData = user.projects.map(item => ({
                            technologies: item.technologies.map(tech => ({
                                id: tech.id.toString(),
                                name: tech.technology
                            })),
                            projects: [{
                                id: item.id.toString(),
                                projectTitle: item.project_title,
                                projectDescription: item
                                    .project_description
                            }]
                        }));

                        fetchTechnologies();
                        $('#editUserModal').modal('show');
                    }
                }
            });
        });

        function fetchTechnologies() {
            $.ajax({
                url: "{{ route('getTechnologies') }}",
                type: "GET",
                success: function(response) {
                    if (response.success === true) {
                        renderProjectData(editingData, response.data);
                    }
                }
            });
        }

        let projectArray = [];

        function renderProjectData(editingData, technologies) {
            projectArray = [];

            $('#editproject-wrapper').html('');

            editingData.forEach((data, index) => {
                const uniqID = `project_${index}`;
                let techOptions = technologies.map(tech =>
                    data.technologies.some(item => item.name.toLowerCase() === tech.technology
                        .toLowerCase()) ?
                    `<label><input style="margin-right: 7px;" type="checkbox" name="${tech.technology}" value="${tech.id}" checked="checked">${tech.technology}</label>` :
                    `<label><input style="margin-right: 7px;" type="checkbox" name="${tech.technology}" value="${tech.id}">${tech.technology}</label>`
                ).join('');

                let technologiesTags = data.technologies.map((tech) =>
                    `<span class="selected-tag" data-project-id="${index}" data-tech-id="${tech.id}">
                ${tech.name}<span class="remove-tag" project-id="${index}" tech-id="${tech.id}" style="cursor:pointer">&times;</span>
            </span>`
                ).join('');

                var contentHTML = `
            <div class="newProject-container" id="${uniqID}">
                <div class="newprojectBox">
                    <div class="form-group">
                        <label for="exampleInputProjectTitle1"><span style="color: red">*</span><strong>Project Title</strong></label>
                        <input rows="4" class="form-control project-title" name="projectTitle" aria-describedby="ProjectTitleHelp" placeholder="Enter Project Title" id="${data.projects[0].id}" value="${data.projects[0].projectTitle}" required>
                        <div style="color:red" class="projectTitle-error"></div>
                    </div>
                    <div class="form-group">
                        <label for="exampleInputProjectDescription1"><span style="color: red">*</span><strong>Project Description</strong></label>
                        <textarea rows="4" class="form-control project-description" name="projectDescription" aria-describedby="ProjectDescriptionHelp" placeholder="Enter Project Description" required>${data.projects[0].projectDescription}</textarea>
                        <div style="color:red" class="projectDescription-error"></div>
                    </div>
                </div>
                <div class="form-group dropdown-container">
                    <label for="exampleInputTechnologyUsed1"><span style="color: red">*</span><strong>Technology Used</strong></label>
                    <div class="selected-tags" style="padding: 10px;">${technologiesTags}</div>
                    <input type="text" class="form-control technology-input" id="exampleInputTechnologyUsed1" aria-describedby="technologyUsedHelp" placeholder="Enter Technology Used" autocomplete="off">
                    <div class="dropdown-content" style="display:none;flex-direction: column; width:100%; padding:8px; background-color:#f0eeee;">
                        <div class="add-new-technology" style="display: none;">Add Technology</div>
                        ${techOptions}
                    </div>
                </div>
                <span class="btn btn-danger remove-button">Remove</span>
            </div>
        `;
                projectArray.push(contentHTML);
            });

            $('#editproject-wrapper').append(projectArray.join(''));
            updateRemoveButtons();
            handleCheckboxChanges(); // Call new function to handle checkbox changes
            handleTechnologySearch(); // Call new function to handle technology search
        }

        function updateRemoveButtons() {
            if (projectArray.length === 1) {
                $('.remove-button').hide();
            } else {
                $('.remove-button').show();
            }
        }



        function handleCheckboxChanges() {
            $('#editproject-wrapper').on('change', 'input[type="checkbox"]', function() {
                $('.technology-input').val('');
                let isChecked = $(this).prop('checked');
                let technologyName = $(this).attr('name');
                let technologyId = $(this).val();

                // Set or remove 'checked' attribute based on isChecked
                if (isChecked) {
                    $(this).attr('checked', "checked"); // Add 'checked' attribute
                } else {
                    $(this).removeAttr('checked'); // Remove 'checked' attribute
                }

                let projectId = $(this).closest('.newProject-container').attr('id');
                let projectIndex = parseInt(projectId.replace(/\D/g, ''), 10);

                if (isChecked) {
                    // Check if the tag already exists
                    if (!$(
                            `.selected-tag[data-tech-id="${technologyId}"][data-project-id="${projectIndex}"]`
                        )
                        .length) {
                        // Add tag
                        let tagHTML = `<span class="selected-tag" data-project-id="${projectIndex}" data-tech-id="${technologyId}">
                        ${technologyName}<span class="remove-tag" project-id="${projectIndex}" tech-id="${technologyId}" style="cursor:pointer">&times;</span>
                    </span>`;
                        $(this).closest('.dropdown-container').find('.selected-tags').append(tagHTML);
                    }
                } else {
                    // Remove tag
                    $(`.selected-tag[data-tech-id="${technologyId}"][data-project-id="${projectIndex}"]`)
                        .remove();
                }
            });
        }

        function handleTechnologySearch() {
            $('#editproject-wrapper').on('input', '.technology-input', function() {
                let searchValue = $(this).val().toLowerCase();
                let dropdownContent = $(this).siblings('.dropdown-content');
                let techOptions = dropdownContent.find('label');
                let addNewTechButton = dropdownContent.find('.add-new-technology');

                if (searchValue === '') {
                    dropdownContent.hide();
                    techOptions.hide();
                    addNewTechButton.hide();
                    return;
                }

                let matchedOptions = techOptions.filter(function() {
                    return $(this).text().toLowerCase().startsWith(searchValue);
                });

                techOptions.hide();
                matchedOptions.show();

                if (matchedOptions.length === 0) {
                    addNewTechButton.show();
                } else {
                    addNewTechButton.hide();
                }

                dropdownContent.show();
            });

            // Add event listener for addNewTechButton click
            $('#editproject-wrapper').on('click', '.add-new-technology', function() {
                let inputField = $(this).closest('.dropdown-content').siblings('.technology-input');
                let newTech = inputField.val().trim();

                if (newTech !== '') {
                    // Create the new tag element
                    let newTag = $('<span class="selected-tag">' + newTech +
                        ' <span type="button" class="remove-tag">&times;</span></span>');

                    $(this).closest('.dropdown-container').find('.selected-tags').append(newTag);

                    let newCheckedInput =
                        `<label class="tech-tag"><input style="margin-right: 7px;" type="checkbox" name="${newTech}" value="${newTech}" checked="checked">${newTech}</label>`;
                    $(this).closest('.dropdown-container').find('.dropdown-content').append(
                        newCheckedInput);

                    // Clear the input field and hide the dropdown
                    inputField.val('');
                    $(this).closest('.dropdown-content').hide();
                }
            });

            // Add event listener for removing tags
            $('#editproject-wrapper').on('click', '.remove-tag', function() {
                let removedTagName = $(this).closest('.selected-tag').text().trim();
                $(this).closest('.selected-tag').remove();

                // Uncheck corresponding checkbox if exists
                let checkboxLabel = $(this).closest('.dropdown-container').find('.dropdown-content')
                    .find(`label:contains('${removedTagName}')`);
                if (checkboxLabel.length > 0) {
                    checkboxLabel.find('input[type="checkbox"]').prop('checked', false);
                }
            });
        }

        // Call the function to initialize the event handlers
        handleTechnologySearch();



        $('#editproject-wrapper').on('input', '.project-title, .project-description', function() {
            var projectID = $(this).closest('.newProject-container').attr('id');
            let arrayIndex = parseInt(projectID.replace(/\D/g, ''), 10);

            var $projectContainer = $(this).closest('.newProject-container');
            var oldProjectTitleValue = $projectContainer.find('.project-title').attr('value');
            var oldProjectDescriptionValue = $projectContainer.find('.project-description').attr(
                'value');

            var newProjectTitleValue = $projectContainer.find('.project-title').val();
            var newProjectDescriptionValue = $projectContainer.find('.project-description').val();

            // Update the old values with the new ones
            $projectContainer.find('.project-title').attr('value', newProjectTitleValue);
            $projectContainer.find('.project-description').attr('value', newProjectDescriptionValue);
        });




        $('#editproject-wrapper').on('click', '.remove-button', function() {
            let projectID = $(this).closest('.newProject-container').attr('id');
            let arrayIndex = parseInt(projectID.replace(/\D/g, ''), 10);
            $(this).closest('.newProject-container').remove();
            projectArray.splice(arrayIndex, 1);
            updateRemoveButtons();
        });

        $('#editproject-wrapper').on('click', '.remove-tag', function() {
            let projectIndex = $(this).attr('project-id');
            let techId = $(this).attr('tech-id');
            $(`.selected-tag[data-tech-id="${techId}"][data-project-id="${projectIndex}"]`).remove();
            $(`input[type="checkbox"][value="${techId}"]`).prop('checked', false);
        });


        const projectWrapper = $('#editproject-wrapper');
        const projectHTML = `
        <div class="newProject-container" id="${'project_'+projectArray.lenght+1}">
            <div class="newprojectBox">
                <div class="form-group">
                    <label for="exampleInputProjectTitle1"><span style="color: red">*</span><strong>Project Title</strong></label>
                    <input rows="4" class="form-control project-title"  name="projectTitle" aria-describedby="ProjectTitleHelp" placeholder="Enter Project Title" required>
                    <div style="color:red" class="projectTitle-error"></div>
                </div>
                <div class="form-group">
                    <label for="exampleInputProjectDescription1"><span style="color: red">*</span><strong>Project Description</strong></label>
                    <textarea rows="4" class="form-control project-description" name="projectDescription" aria-describedby="ProjectDescriptionHelp" placeholder="Enter Project Description" required></textarea>
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
            <button class="btn btn-danger remove-button">Remove</button>
        </div>`;

        function scrollToModalBottom() {
            var modalContent = document.querySelector('#editUserModal .modal-body');
            modalContent.scrollTo(0, modalContent.scrollHeight);
        }


        function addProject() {
            const newProject = $(projectHTML);
            projectWrapper.append(newProject);
            projectArray.push(newProject);
            updateRemoveButtons();
            scrollToModalBottom()
            // Add functionality to remove project
            newProject.find('.remove-button').on('click', function() {
                newProject.remove();
                projectArray = projectArray.filter(project => project[0] !== newProject[0]);
                updateRemoveButtons();
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
        }

        // Initialize with one project by default
        addProject();

        $('#editno-options-button').on('click', addProject);

        function collectFormData() {
            const formData = [];

            // Iterate over each project container within the #editproject-wrapper
            $('#editproject-wrapper .newProject-container').each(function() {
                const projectData = {
                    technologies: [],
                    projects: []
                };

                // Collect project title and description
                const projectTitle = $(this).find('.project-title').val().trim();
                const projectDescription = $(this).find('.project-description').val().trim();
                const projectId = $(this).find('.project-title').attr('id');

                projectData.projects.push({
                    id: projectId ? projectId : null,
                    projectTitle: projectTitle,
                    projectDescription: projectDescription
                });

                // Collect selected technologies
                const selectedTechnologies = $(this).find('.selected-tag');
                selectedTechnologies.each(function() {
                    const technologyId = $(this).data('tech-id');
                    const technologyName = $(this).text().trim().replace('×',
                        ''); // Remove the remove tag symbol

                    projectData.technologies.push({
                        id: technologyId,
                        name: technologyName
                    });
                });

                formData.push(projectData);
            });

            return formData;
        }

        $('#editUserForm').on('submit', function(e) {
            e.preventDefault();

            var formData = new FormData(this); // Serialize the form data

            const projectFormData = collectFormData();
            var projects = JSON.stringify(projectFormData);
            formData.append('projects', projects);

            $.ajax({
                url: "{{ route('update-client') }}",
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    if (response.success) {
                        toastr.options = {
                            'timeOut': 2000,
                            'closeButton': true,
                            'progressBar': true,
                            "positionClass": "toast-top-center",
                        };
                        toastr.success("", response.message);
                        $('#editUserModal').modal('hide');
                        loadTable();
                        projects = [];
                        technologies = [];
                    } else {
                        toastr.error(response.message);
                    }
                }
            });
        });

        $(document).on('keypress', function(e) {
            var key = e.charCode || e.keyCode || 0;
            if (key == 13) {
                e.preventDefault(); // Prevents the default action
            }
        });
    });
</script>