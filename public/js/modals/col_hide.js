// Array for col hide elements selection
var selectedColHideElementsValues = [];

// Fill array with col hide elements values
document.querySelectorAll('.col_hide_control').forEach(
    function (control) {
        control.addEventListener('change', function (e) {
            if (e.target.checked) {
                selectedColHideElementsValues.push(e.target.value);
            } else {
                selectedColHideElementsValues = selectedColHideElementsValues.filter(
                    function (value) {
                        return value !== e.target.value;
                    }
                );
            }
        });
    });

// Hide selected elements
if(document.querySelector('.col_hide_save')){
    document.querySelector('.col_hide_save').addEventListener('click',
        function (e) {
            document.querySelectorAll('.col_hide_column').forEach(function (element) {
                if (selectedColHideElementsValues.includes(element.dataset.value)) {
                    element.classList.add('d-none');
                } else {
                    element.classList.remove('d-none');
                }
            });

            $("#colHideModal").modal('hide');
        });
}

