const urlParams = new URLSearchParams(window.location.search);
const editId = urlParams.get('id');

const form = document.getElementById('user-form');
const saveBtn = document.getElementById('save-btn');
const nameInput = document.getElementById('name');
const emailInput = document.getElementById('email');
const cpfInput = document.getElementById('cpf');
const passwordInput = document.getElementById('password');

const nameError = document.getElementById('name-error');
const emailError = document.getElementById('email-error');
const cpfError = document.getElementById('cpf-error');
const passwordError = document.getElementById('password-error');
const serverEmailError = document.getElementById('server-email-error');

let isEditMode = false;


const checkName = () => nameInput.value.trim().length >= 3;
const checkEmail = () => /^\S+@\S+\.\S+$/.test(emailInput.value);
const checkPassword = () => {
    if (isEditMode && passwordInput.value === '') return true;
    return passwordInput.value.length >= 6;
};
const checkCPF = () => {
    const val = cpfInput.value.replace(/[^\d]+/g,'');
    // CPF é opcional - retorna true se vazio
    if (val.length === 0) return true;
    // Se tem valor, valida o comprimento
    if (val.length !== 11) return false;
    
    if (typeof jsbrasil !== 'undefined' && jsbrasil.validateBr && jsbrasil.validateBr.cpf) {
        return jsbrasil.validateBr.cpf(val);
    }
    
    return true;
};

function updateButtonState() {
    const isValid = checkName() && checkEmail() && checkCPF() && checkPassword();
    saveBtn.disabled = !isValid;
}

function updateFieldUI(input, errorEl, validatorFn, forceShow = false) {
    const isValid = validatorFn();
    
    if (isValid) {
        errorEl.style.display = 'none';
    } else if (forceShow) {
        errorEl.style.display = 'block';
    }
}

function setupValidation(input, errorEl, validatorFn) {
    input.addEventListener('blur', () => {
        updateFieldUI(input, errorEl, validatorFn, true); 
        updateButtonState();
    });

    input.addEventListener('input', () => {
        if (input === emailInput) serverEmailError.style.display = 'none';
        
        if (input === cpfInput && typeof jsbrasil !== 'undefined' && jsbrasil.maskBr && jsbrasil.maskBr.cpf) {
            cpfInput.value = jsbrasil.maskBr.cpf(cpfInput.value);
        }

        const isValid = validatorFn();
        if (isValid) {
            errorEl.style.display = 'none';
        }
        
        updateButtonState();
    });
}


setupValidation(nameInput, nameError, checkName);
setupValidation(emailInput, emailError, checkEmail);
setupValidation(cpfInput, cpfError, checkCPF);
setupValidation(passwordInput, passwordError, checkPassword);

if (editId) {
    isEditMode = true;
    document.getElementById('page-title').innerText = 'Edit User';
    passwordInput.removeAttribute('required');
    passwordInput.placeholder = 'Keep blank to retain current password';
    loadUser();
} else {
    passwordInput.setAttribute('required', 'required');
    passwordInput.placeholder = 'At least 6 characters';
    updateButtonState(); 
}

async function loadUser() {
    try {
        const user = await App.api(`users/${editId}`); 
        nameInput.value = user.name;
        emailInput.value = user.email;
        
        if (typeof jsbrasil !== 'undefined' && jsbrasil.maskBr && jsbrasil.maskBr.cpf) {
            cpfInput.value = jsbrasil.maskBr.cpf(user.cpf || '');
        } else {
            cpfInput.value = user.cpf || '';
        }
        
        document.getElementById('birth_date').value = user.birth_date || '';
        document.getElementById('role').value = user.role;
        
        updateButtonState();
    } catch (e) {
        App.showToast('Error loading user', 'error');
        console.error(e);
    }
}


form.addEventListener('submit', (e) => {
    e.preventDefault();

    if (saveBtn.disabled) return;

    App.confirm(isEditMode ? 'Save edition' : 'Create user', 'Confirm to save the informations', async () => {
        const data = {
            name: nameInput.value,
            email: emailInput.value,
            cpf: cpfInput.value.replace(/[^\d]+/g,''), 
            birth_date: document.getElementById('birth_date').value,
            role: document.getElementById('role').value
        };

        const pass = passwordInput.value;
        if (!isEditMode || pass) {
            data.password = pass;
        }

        const method = isEditMode ? 'PUT' : 'POST';
        const endpoint = isEditMode ? `users/${editId}` : 'users'; 

        console.log('Submitting data:', data);
        console.log('Endpoint:', endpoint);
        console.log('Method:', method);

        try {
            await App.api(endpoint, method, data);
            App.showToast('User saved successfully', 'success');
            setTimeout(() => window.location.href = 'dashboard.html', 1500);
        } catch (e) {
            if (e.message && e.message.toLowerCase().includes('creating user')) {
                    serverEmailError.style.display = 'block';
                    saveBtn.disabled = true; 
                    App.showToast('Erro: Email already in use', 'error');
            }
        }
    });
});