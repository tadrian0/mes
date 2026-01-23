$(document).ready(function() {
    var table = $('#totemSelectTable').DataTable({
        ajax: 'api/machines-fetch.php',
        columns: [
            { data: 'MachineID' },
            { data: 'Name', render: (d,t,r) => `<div class="fw-bold">${d}</div><small class="text-muted">${r.Location}</small>` },
            { data: 'Model' },
            { data: 'CountryName', defaultContent: '-' },
            { data: 'CityName', defaultContent: '-' },
            { data: 'PlantName', defaultContent: '-' },
            { data: 'SectionName', defaultContent: '-' },
            { data: null, orderable:false, render: (d,t,r) => `<a href="?machine_id=${r.MachineID}" class="btn btn-primary btn-sm w-100"><i class="fa-solid fa-arrow-right"></i> Open Totem</a>` }
        ],
        order: [[ 1, 'asc' ]],
        pageLength: 10,
        lengthMenu: [10, 25, 50],
        language: { search: "" } 
    });

    $('#globalSearch').on('keyup', function() { table.search(this.value).draw(); });

    function updateCascadingOptions() {
        let country = $('#filter_country').val();
        let city    = $('#filter_city').val();
        let plant   = $('#filter_plant').val();

        $.ajax({
            url: 'api/get-filter-options.php',
            data: { country, city, plant },
            dataType: 'json',
            success: function(res) {
                const populate = (sel, data, curr) => {
                    let $el = $(sel).empty().append('<option value="">All</option>');
                    data.forEach(i => $el.append(new Option(i, i, false, i === curr)));
                };

                let currCity = $('#filter_city').val();
                populate('#filter_city', res.cities, currCity);
                
                populate('#filter_plant', res.plants, $('#filter_plant').val());
                populate('#filter_section', res.sections, $('#filter_section').val());
            }
        });
    }

    $('#filter_country, #filter_city, #filter_plant').on('change', function() {
        if(this.id == 'filter_country') { $('#filter_city').val(''); $('#filter_plant').val(''); $('#filter_section').val(''); }
        if(this.id == 'filter_city') { $('#filter_plant').val(''); $('#filter_section').val(''); }
        if(this.id == 'filter_plant') { $('#filter_section').val(''); }
        
        applyFilters();
        updateCascadingOptions();
    });

    $('#filter_section').on('change', applyFilters);

    function applyFilters() {
        $('.filter-input').each(function() {
            let colIndex = $(this).data('col-index');
            let val = $.fn.dataTable.util.escapeRegex($(this).val());
            table.column(colIndex).search(val ? '^'+val+'$' : '', true, false);
        });
        table.draw();
    }
    
    updateCascadingOptions();
});