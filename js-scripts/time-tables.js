document.querySelectorAll('input[name="operation"]').forEach(radio => {
    radio.addEventListener('change', function() {
        if (this.value === 'create') {
            document.getElementById('existing_time_table_select').style.display = 'none';
            document.getElementById('day_week').disabled = false;
            document.getElementById('time_start').disabled = false;
            document.getElementById('time_end').disabled = false;
            document.getElementById('semester').disabled = false;
            document.getElementById('teacher_id').disabled = false;
            document.getElementById('class_id').disabled = false;
            document.getElementById('subject_id').disabled = false;
            clearFormFields();
        } else {
            document.getElementById('existing_time_table_select').style.display = 'block';
            if (this.value === 'delete') {
                document.getElementById('day_week').disabled = true;
                document.getElementById('time_start').disabled = true;
                document.getElementById('time_end').disabled = true;
                document.getElementById('semester').disabled = true;
                document.getElementById('teacher_id').disabled = true;
                document.getElementById('class_id').disabled = true;
                document.getElementById('subject_id').disabled = true;
            } else {
                document.getElementById('day_week').disabled = false;
                document.getElementById('time_start').disabled = false;
                document.getElementById('time_end').disabled = false;
                document.getElementById('semester').disabled = false;
                document.getElementById('teacher_id').disabled = false;
                document.getElementById('class_id').disabled = false;
                document.getElementById('subject_id').disabled = false;
            }
        }
    });
});

document.getElementById('time_table_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    document.getElementById('day_week').value = selectedOption.getAttribute('data-day');
    document.getElementById('time_start').value = selectedOption.getAttribute('data-start');
    document.getElementById('time_end').value = selectedOption.getAttribute('data-end');
    document.getElementById('semester').value = selectedOption.getAttribute('data-semester');
    document.getElementById('teacher_id').value = selectedOption.getAttribute('data-teacher');
    document.getElementById('class_id').value = selectedOption.getAttribute('data-class');
    document.getElementById('subject_id').value = selectedOption.getAttribute('data-subject');
});

document.getElementById('teacher_id').addEventListener('change', function () {
    var teacherId = this.value;
    
    fetch(`http://localhost/grade-center/controllers/get_teacher_subjects.php?teacher_id=${teacherId}`)
        .then(response => response.json())
        .then(data => {
            var subjectSelect = document.getElementById('subject_id');
            subjectSelect.innerHTML = '';

            data.subjects.forEach(function (subject) {
                var option = document.createElement('option');
                option.value = subject.id;
                option.textContent = subject.name;
                subjectSelect.appendChild(option);
            });
        })
        .catch(error => console.error('Error fetching subjects:', error));
});

function clearFormFields() {
    document.getElementById('day_week').value = '';
    document.getElementById('time_start').value = '';
    document.getElementById('time_end').value = '';
    document.getElementById('semester').value = '';
    document.getElementById('teacher_id').value = '';
    document.getElementById('class_id').value = '';
    document.getElementById('subject_id').value = '';
    document.getElementById('time_table_id').selectedIndex = -1;
}