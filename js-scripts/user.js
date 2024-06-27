document.querySelectorAll('input[name="action"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const userFormFields = document.getElementById('user_form_fields');
        const existingUserSelect = document.getElementById('existing_user_select');

        if (this.value === 'create') {
            userFormFields.style.display = 'block';
            existingUserSelect.style.display = 'none';
            clearFormFields();
        } else if (this.value === 'update') {
            userFormFields.style.display = 'block';
            existingUserSelect.style.display = 'block';
        } else if (this.value === 'delete') {
            userFormFields.style.display = 'none';
            existingUserSelect.style.display = 'block';
        }
    });
});

document.getElementById('user_id').addEventListener('change', function() {
    const userId = this.value;
    fetch(`http://localhost/grade-center/controllers/get_user_data.php?user_id=${userId}`)
        .then(response => response.json())
        .then(data => {
            if (data.username) {
                document.getElementById('username').value = data.username;
                document.getElementById('full_name').value = data.full_name;
                document.getElementById('age').value = data.age;

                const schoolSelect = document.getElementById('school_id');
                const classSelect = document.getElementById('class_id');
                
                if (data.school_id) {
                    schoolSelect.value = data.school_id;
                    fetchClasses(data.school_id, data.class_id);
                } else {
                    schoolSelect.value = '';
                    classSelect.innerHTML = '<option value="">-- Select Class --</option>';
                }

                document.querySelectorAll('[name="roles[]"]').forEach(checkbox => {
                    checkbox.checked = data.roles.includes(checkbox.value);
                });
            } else {
                document.getElementById('username').value = '';
                document.getElementById('full_name').value = '';
                document.getElementById('age').value = '';
                document.getElementById('school_id').value = '';
                document.getElementById('class_id').innerHTML = '<option value="">-- Select Class --</option>';
                document.querySelectorAll('[name="roles[]"]').forEach(checkbox => {
                    checkbox.checked = false;
                });
            }
        })
        .catch(error => console.error('Error fetching user data:', error));
});

function fetchClasses(schoolId, selectedClassId = null) {
    fetch(`http://localhost/grade-center/controllers/get_classes.php?school_id=${schoolId}`)
        .then(response => response.json())
        .then(data => {
            const classSelect = document.getElementById('class_id');
            classSelect.innerHTML = '<option value="">-- Select Class --</option>';
            data.forEach(cls => {
                const option = document.createElement('option');
                option.value = cls.id;
                option.textContent = `${cls.grade}${cls.letter}`;
                if (selectedClassId && cls.id == selectedClassId) {
                    option.selected = true;
                }
                classSelect.appendChild(option);
            });
        })
        .catch(error => console.error('Error fetching classes:', error));
}

document.getElementById('school_id').addEventListener('change', function() {
    const schoolId = this.value;
    fetchClasses(schoolId);
});

fetch('http://localhost/grade-center/controllers/get_schools.php')
    .then(response => response.json())
    .then(data => {
        const schoolSelect = document.getElementById('school_id');
        data.forEach(school => {
            const option = document.createElement('option');
            option.value = school.id;
            option.textContent = school.name;
            schoolSelect.appendChild(option);
        });
    })
    .catch(error => console.error('Error fetching schools:', error));

    function clearFormFields() {
        document.getElementById('user_id').selectedIndex = 0;
        document.getElementById('username').value = '';
        document.getElementById('password').value = '';
        document.getElementById('full_name').value = '';
        document.getElementById('age').value = '';
        document.getElementById('school_id').selectedIndex = 0;
        document.getElementById('class_id').selectedIndex = 0;
    
        const roles = document.getElementsByName('roles[]');
        roles.forEach(role => {
            role.checked = false;
        });
    }
    
    