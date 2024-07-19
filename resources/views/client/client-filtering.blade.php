<style>
    .dropdown-submenu {
        position: relative;
    }

    .dropdown-submenu .dropdown-menu {
        top: 0;
        left: 100%;
        margin-top: -1px;
    }

    #allSelectedFilters {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .filter-tag {
        display: flex;
        align-items: center;
        margin-right: 4px;
        padding: 2px 10px;
        background-color: #007bff;
        color: white;
        border-radius: 3px;
        font-size: 14px;
    }

    .filter-tag .remove-tag {
        margin-left: 5px;
        cursor: pointer;
    }
</style>

<div class="container flex">
    <div class="dropdown">
        <button class="btn btn-info dropdown-toggle filterButton" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            Filter
        </button>
        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
            <li class="dropdown-submenu">
                <a class="dropdown-item dropdown-toggle" href="#">Country</a>
                <ul class="dropdown-menu" style="height:120px;overflow:scroll" id="countryMenu">
                    <li class="dropdown-item">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="select_all" id="select_all_countries">
                            <label class="form-check-label" for="select_all_countries">
                                Select All
                            </label>
                        </div>
                    </li>
                </ul>
            </li>
            <li class="dropdown-submenu">
                <a class="dropdown-item dropdown-toggle" href="#">Technology</a>
                <ul class="dropdown-menu" style="height:150px;overflow:scroll" id="technologyMenu">
                    <li class="dropdown-item">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="select_all" id="select_all_technologies">
                            <label class="form-check-label" for="select_all_technologies">
                                Select All
                            </label>
                        </div>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
    <button id="clearFilters" class="btn btn-warning mx-2" style="width: 115px;">Clear Filters</button>
    <div id="allSelectedFilters"></div>
</div>

<script>
    $(document).ready(function() {
        $('.filterButton').on('click', function() {
            loadTechnologies();
            loadCountries();
        });

        function closeOtherSubmenus(currentSubmenu) {
            $('.dropdown-submenu .dropdown-menu').not(currentSubmenu).hide();
        }

        $('.dropdown-submenu a.dropdown-toggle').on("click", function(e) {
            var nextMenu = $(this).next('ul');
            closeOtherSubmenus(nextMenu);
            nextMenu.toggle();
            e.stopPropagation();
            e.preventDefault();
        });

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        function loadTechnologies() {
            $.ajax({
                url: "{{ route('getTechnologies') }}",
                type: "GET",
                success: function(data) {
                    if (data.success == true) {
                        var technologies = data.data;
                        var html = `<li class="dropdown-item">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="select_all" id="select_all_technologies">
                                        <label class="form-check-label" for="select_all_technologies">
                                            Select All
                                        </label>
                                    </div>
                                </li>`;

                        if (technologies.length > 0) {
                            technologies.forEach(function(tech) {
                                html += `<li class="dropdown-item">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="${tech.technology}" id="tech_${tech.id}">
                                            <label class="form-check-label" for="tech_${tech.id}">
                                                ${tech.technology}
                                            </label>
                                        </div>
                                    </li>`;
                            });
                        } else {
                            html = `<li class="dropdown-item">No Technologies Found!</li>`;
                        }

                        $('#technologyMenu').html(html);
                    } else {
                        $('#technologyMenu').html('<li class="dropdown-item">No Technologies Found!</li>');
                    }
                }
            });
        }

        function loadCountries() {
            $.ajax({
                url: "{{ route('client-details') }}",
                type: "GET",
                success: function(data) {
                    if (data.success == true) {
                        var countries = data.data // Adjust based on actual data structure
                        var html = `<li class="dropdown-item">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="select_all" id="select_all_countries">
                                        <label class="form-check-label" for="select_all_countries">
                                            Select All
                                        </label>
                                    </div>
                                </li>`;
                        var uniqueCountries = new Set();
                        if (countries.length > 0) {
                            countries.forEach(function(country) {
                                if (!uniqueCountries.has(country.country) && country.is_archived === false) {
                                    uniqueCountries.add(country.country);
                                    html += `<li class="dropdown-item">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="${country.country}" id="country_${country.id}">
                                                <label class="form-check-label" for="country_${country.id}">
                                                    ${country.country}
                                                </label>
                                            </div>
                                        </li>`;
                                }
                            });
                        } else {
                            html = `<li class="dropdown-item">No Countries Found!</li>`;
                        }

                        $('#countryMenu').html(html);
                    } else {
                        $('#countryMenu').html('<li class="dropdown-item">No Countries Found!</li>');
                    }
                }
            });
        }

        loadTechnologies();
        loadCountries();

        // Handle checkbox changes for technologies and countries
        $('#technologyMenu, #countryMenu').on('change', '.form-check-input', function() {
            var selectedTechnologies = [];
            $('#technologyMenu .form-check-input:checked').each(function() {
                if ($(this).val() !== 'select_all') {
                    selectedTechnologies.push($(this).val());
                }
            });

            var selectedCountries = [];
            $('#countryMenu .form-check-input:checked').each(function() {
                if ($(this).val() !== 'select_all') {
                    selectedCountries.push($(this).val());
                }
            });

            displaySelectedFilters(selectedTechnologies, selectedCountries);
            loadTable(selectedTechnologies, selectedCountries);
        });

        // Handle "Select All" functionality for technologies
        $('#technologyMenu').on('change', '#select_all_technologies', function() {
            var isChecked = $(this).is(':checked');
            $('#technologyMenu .form-check-input').not(this).prop('checked', isChecked).trigger('change');
        });

        // Handle "Select All" functionality for countries
        $('#countryMenu').on('change', '#select_all_countries', function() {
            var isChecked = $(this).is(':checked');
            $('#countryMenu .form-check-input').not(this).prop('checked', isChecked).trigger('change');
        });

        // Clear filters button functionality
        $('#clearFilters').on('click', function() {
            // Uncheck all checkboxes
            $('#technologyMenu .form-check-input, #countryMenu .form-check-input').prop('checked', false).trigger('change');

            // Clear the selected filters display
            $('#allSelectedFilters').html('');

            // Reload the table without any filters
            loadTable([], []);
        });

        // Function to display selected filters as tags
        function displaySelectedFilters(technologies, countries) {
            var selectedFiltersHtml = '';

            technologies.forEach(function(tech) {
                selectedFiltersHtml += `<span class="filter-tag" data-filter="${tech}">
                                            ${tech} <span class="remove-tag" data-filter="${tech}">×</span>
                                        </span>`;
            });

            countries.forEach(function(country) {
                selectedFiltersHtml += `<span class="filter-tag" data-filter="${country}">
                                            ${country} <span class="remove-tag" data-filter="${country}">×</span>
                                        </span>`;
            });

            $('#allSelectedFilters').html(selectedFiltersHtml);
        }

        // Event listener for removing a filter tag
        $('#allSelectedFilters').on('click', '.remove-tag', function() {
            var filterValue = $(this).data('filter');

            // Uncheck the corresponding checkbox
            $(`#technologyMenu .form-check-input[value="${filterValue}"], #countryMenu .form-check-input[value="${filterValue}"]`).prop('checked', false).trigger('change');

            // Remove the tag from the display
            $(this).parent().remove();
        });

        const isAdmin = @json(optional(Auth::user()) -> is_admin);
        const isSuperAdmin = @json(optional(Auth::user()) -> is_super_admin);

        // Show the results based on the selected filters
        function loadTable(selectedTechnologies,selectedCountries) {
            $.ajax({
                url: "{{ route('client-details') }}",
                type: "GET",
                data: {
                    technologies: selectedTechnologies,
                    countries: selectedCountries
                },
                success: function(data) {
                    if (data.success == true) {
                        var userData = data.data.data;
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
                                    <td style="display: flex; justify-content: space-evenly;padding-top: 25px; width:110px">`;
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
                        let records_per_page = 5;
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
    });
</script>