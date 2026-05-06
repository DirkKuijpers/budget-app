// Login show/hide password
const togglePassword = document.querySelector("#togglePassword");
const password = document.querySelector("#password");

if(togglePassword && password){
    togglePassword.addEventListener("click", function () {
        const type = password.getAttribute("type") === "password" ? "text" : "password";
        password.setAttribute("type", type);
    });
}

// Register show/hide password
const togglePasswordReg = document.querySelector("#togglePasswordReg");
const passwordReg = document.querySelector("#passwordReg");

if(togglePasswordReg && passwordReg){
    togglePasswordReg.addEventListener("click", function () {
        const type = passwordReg.getAttribute("type") === "password" ? "text" : "password";
        passwordReg.setAttribute("type", type);
    });
}
// === Popups openen ===
document.getElementById('addSavingBtn').addEventListener('click', () => {
    document.getElementById('addSavingPopup').classList.add('show');
});

// Sluit buttons
document.querySelectorAll('.close-popup').forEach(btn => {
    btn.addEventListener('click', e => {
        e.target.closest('.popup-form').classList.remove('show');
    });
});

// === Nieuw spaarpotje toevoegen ===
document.getElementById('addSavingForm').addEventListener('submit', e => {
    e.preventDefault();
    const form = e.target;
    const data = new FormData(form);
    data.append('action', 'add_saving');

    fetch('savings.php', { method:'POST', body:data })
        .then(res => res.json())
        .then(res => {
            if(res.success) window.location.reload();
        });
});

// === Spaarpotje aanpassen ===
document.querySelectorAll('.edit-btn').forEach(btn => {
    btn.addEventListener('click', e => {
        const card = e.target.closest('.saving-card');
        const id = btn.getAttribute('data-id');
        const name = card.querySelector('p strong').innerText;
        const goal = card.querySelectorAll('p')[1].innerText.split(' / ')[1].replace(' €','');

        const form = document.getElementById('editSavingForm');
        form.id.value = id;
        form.name.value = name;
        form.goal.value = goal;

        document.getElementById('editSavingPopup').classList.add('show');
    });
});

document.getElementById('editSavingForm').addEventListener('submit', e => {
    e.preventDefault();
    const form = e.target;
    const data = new FormData(form);
    data.append('action','edit_saving');

    fetch('savings.php',{method:'POST', body:data})
        .then(res=>res.json())
        .then(res=>{
            if(res.success) window.location.reload();
        });
});

// === Bedrag toevoegen ===
document.querySelectorAll('.add-money-btn').forEach(btn=>{
    btn.addEventListener('click', e=>{
        const id = btn.getAttribute('data-id');
        const form = document.getElementById('addMoneyForm');
        form.id.value = id;
        document.getElementById('addMoneyPopup').classList.add('show');
    });
});

document.getElementById('addMoneyForm').addEventListener('submit', e=>{
    e.preventDefault();
    const form = e.target;
    const data = new FormData(form);
    data.append('action','add_money');

    fetch('savings.php',{method:'POST', body:data})
        .then(res=>res.json())
        .then(res=>{
            if(res.success) window.location.reload();
        });
});
// === Spaarpotje verwijderen ===
document.querySelectorAll('.delete-btn').forEach(btn => {
    btn.addEventListener('click', e => {
        if(!confirm('Weet je zeker dat je dit spaarpotje wilt verwijderen?')) return;

        const id = btn.getAttribute('data-id');
        const data = new FormData();
        data.append('action', 'delete_saving');
        data.append('id', id);

        fetch('savings.php', { method:'POST', body:data })
            .then(res => res.json())
            .then(res => {
                if(res.success) window.location.reload();
            });
    });
});
