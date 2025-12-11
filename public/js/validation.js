(function () {
  function showError(message) {
    if (window.Swal) {
      Swal.fire({ icon: 'error', title: 'Error', text: message, confirmButtonColor: '#d33' });
    } else {
      alert(message);
    }
  }

  function validateEmail(email) {
    return /^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(email);
  }

  function validateUsername(u) {
    return /^[A-Za-z0-9_]{3,30}$/.test(u);
  }

  function validatePhone(p) {
    return /^[0-9]{10}$/.test(p);
  }

  // LOGIN
  var loginForm = document.getElementById('loginForm');
  if (loginForm) {
    loginForm.addEventListener('submit', function (e) {
      var email = (document.getElementById('email') || {}).value || '';
      var pass  = (document.getElementById('password') || {}).value || '';
      if (!email.trim() || !pass) {
        e.preventDefault(); showError('Email and password are required.'); return;
      }
      if (!validateEmail(email.trim())) {
        e.preventDefault(); showError('Enter a valid email address.'); return;
      }
    });
  }

  // REGISTER
  var registerForm = document.getElementById('registerForm');
  if (registerForm) {
    registerForm.addEventListener('submit', function (e) {
      var u = (document.getElementById('username') || {}).value || '';
      var em = (document.getElementById('reg_email') || {}).value || '';
      var p = (document.getElementById('reg_password') || {}).value || '';
      var c = (document.getElementById('confirm_password') || {}).value || '';

      if (!u.trim() || !em.trim() || !p || !c) {
        e.preventDefault(); showError('All fields are required.'); return;
      }
      if (!validateUsername(u.trim())) {
        e.preventDefault(); showError('Username must be 3-30 characters (letters, numbers, underscore).'); return;
      }
      if (!validateEmail(em.trim())) {
        e.preventDefault(); showError('Enter a valid email address.'); return;
      }
      if (p !== c) {
        e.preventDefault(); showError('Passwords do not match.'); return;
      }
      if (p.length < 8) {
        e.preventDefault(); showError('Password must be at least 8 characters long.'); return;
      }
      if (!(/[A-Z]/.test(p) && /[a-z]/.test(p) && /\d/.test(p))) {
        e.preventDefault(); showError('Password must include upper, lower and a number.'); return;
      }
    });

    // optional: realtime email existence check (calls /index.php?url=auth/checkEmail via fetch)
    var regEmailInput = document.getElementById('reg_email');
    if (regEmailInput) {
      var emailTimer = null;
      regEmailInput.addEventListener('input', function () {
        clearTimeout(emailTimer);
        var val = this.value.trim();
        if (!validateEmail(val)) return;
        emailTimer = setTimeout(function () {
          fetch('index.php?url=auth/checkEmail', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email: val })
          }).then(function (r) { return r.json(); })
            .then(function (data) {
              if (data.exists) {
                showError('This email is already registered.');
              }
            }).catch(function () {});
        }, 600);
      });
    }
  }

  // EDIT USER (admin)
  var editForm = document.getElementById('editUserForm');
  if (editForm) {
    editForm.addEventListener('submit', function (e) {
      var username = (document.getElementById('username') || {}).value || '';
      var email    = (document.getElementById('email') || {}).value || '';
      var phone    = (document.getElementById('phone') || {}).value || '';
      var status   = (document.getElementById('user_status') || {}).value || '';
      var p1 = (document.getElementById('new_password') || {}).value || '';
      var p2 = (document.getElementById('confirm_password') || {}).value || '';

      if (!validateUsername(username.trim())) {
        e.preventDefault(); showError('Username must be 3-30 characters (letters, numbers, underscore).'); return;
      }
      if (!validateEmail(email.trim())) {
        e.preventDefault(); showError('Enter a valid email.'); return;
      }
      if (phone && !validatePhone(phone)) {
        e.preventDefault(); showError('Phone must be 10 digits.'); return;
      }
      if (status !== 'Active' && status !== 'Hold') {
        e.preventDefault(); showError('Invalid status selected.'); return;
      }
      if (p1 !== '') {
        if (p1.length < 8 || !( /[A-Z]/.test(p1) && /[a-z]/.test(p1) && /\d/.test(p1) )) {
          e.preventDefault(); showError('New password must be at least 8 chars and include upper, lower and number.'); return;
        }
        if (p1 !== p2) {
          e.preventDefault(); showError('New password and confirm password do not match.'); return;
        }
      }
    });
  }

  // CUSTOMER DETAILS
  var custForm = document.getElementById('customerDetailsForm');
  if (custForm) {
    custForm.addEventListener('submit', function (e) {
      var full = (document.getElementById('full_name') || {}).value || '';
      var dob  = (document.getElementById('dob') || {}).value || '';
      var addr = (document.getElementById('address') || {}).value || '';
      var phone = (document.getElementById('phone') || {}).value || '';

      if (!full.trim() || !dob.trim() || !addr.trim() || !phone.trim()) {
        e.preventDefault(); showError('All fields are required.'); return;
      }
      if (!/^\d{4}-\d{2}-\d{2}$/.test(dob) || isNaN(Date.parse(dob))) {
        e.preventDefault(); showError('DOB must be in YYYY-MM-DD format.'); return;
      }
      if (!validatePhone(phone.trim())) {
        e.preventDefault(); showError('Phone must be 10 digits.'); return;
      }
    });
  }
})();
