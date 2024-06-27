document.querySelectorAll('input[name="operation"]').forEach(radio => {
    radio.addEventListener('change', function() {
        if (this.value === 'create') {
            document.getElementById('existing_absence_select').style.display = 'none';
            document.getElementById('date_of_absence').disabled = false;
            document.getElementById('student_id').disabled = false;
            document.getElementById('subject_id').disabled = false;
            clearFormFields();
        } else {
            document.getElementById('existing_absence_select').style.display = 'block';
            if (this.value === 'delete') {
                document.getElementById('date_of_absence').disabled = true;
                document.getElementById('student_id').disabled = true;
                document.getElementById('subject_id').disabled = true;
            } else {
                document.getElementById('date_of_absence').disabled = false;
                document.getElementById('student_id').disabled = false;
                document.getElementById('subject_id').disabled = false;
            }
        }
    });
});

document.getElementById('absence_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    document.getElementById('date_of_absence').value = selectedOption.getAttribute('data-date');
    document.getElementById('student_id').value = selectedOption.getAttribute('data-student');
    document.getElementById('subject_id').value = selectedOption.getAttribute('data-subject');
});

function clearFormFields() {
    document.getElementById('date_of_absence').value = '';
    document.getElementById('student_id').value = '';
    document.getElementById('subject_id').value = '';
    document.getElementById('absence_id').selectedIndex = -1;
}