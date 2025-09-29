let userId = 0;
let firstName = "";
let lastName = "";

function isValidEmail(email) {
  // Simple regex: text@text.domain
  const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return regex.test(email);
}

function saveCookie() {
  let minutes = 20;
  let date = new Date();
  date.setTime(date.getTime() + minutes * 60 * 1000);
  let expires = ";expires=" + date.toGMTString() + ";path=/";

  document.cookie = "firstName=" + firstName + expires;
  document.cookie = "lastName=" + lastName + expires;
  document.cookie = "userId=" + userId + expires;
}

function readCookie() {
  userId = -1;
  let data = document.cookie.split(";");

  for (let i = 0; i < data.length; i++) {
    let c = data[i].trim();
    if (c.startsWith("firstName=")) {
      firstName = c.substring("firstName=".length, c.length);
    } else if (c.startsWith("lastName=")) {
      lastName = c.substring("lastName=".length, c.length);
    } else if (c.startsWith("userId=")) {
      userId = parseInt(c.substring("userId=".length, c.length));
    }
  }

  if (userId < 0) {
    window.location.href = "index.html";
  } else {
    document.getElementById("userName").innerHTML =
      firstName + " " + lastName;
  }
}

function doLogout() {
  userId = 0;
  firstName = "";
  lastName = "";
  document.cookie =
    "firstName= ; expires = Thu, 01 Jan 1970 00:00:00 GMT;path=/";
  document.cookie =
    "lastName= ; expires = Thu, 01 Jan 1970 00:00:00 GMT;path=/";
  document.cookie =
    "userId= ; expires = Thu, 01 Jan 1970 00:00:00 GMT;path=/";
  window.location.href = "index.html";
}

function doLogin() {
  let login = document.getElementById("loginName").value.trim();
  let password = document.getElementById("loginPassword").value.trim();

  if (login === "" || password === "") {
    document.getElementById("loginResult").innerHTML =
      "Please enter username and password";
    return;
  }

  let tmp = { login: login, password: password };
  let jsonPayload = JSON.stringify(tmp);

  let url = "LAMPAPI/Login.php";

  let xhr = new XMLHttpRequest();
  xhr.open("POST", url, true);
  xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");
  xhr.onreadystatechange = function () {
    if (this.readyState === 4 && this.status === 200) {
      let jsonObject = JSON.parse(xhr.responseText);
      userId = jsonObject.id;

      if (userId < 1) {
        document.getElementById("loginResult").innerHTML =
          "User/Password combination incorrect";
        return;
      }

      firstName = jsonObject.firstName;
      lastName = jsonObject.lastName;

      saveCookie();
      window.location.href = "contacts.html";
    }
  };
  xhr.send(jsonPayload);
}

function doRegister() {
  let fName = document.getElementById("firstName").value.trim();
  let lName = document.getElementById("lastName").value.trim();
  let login = document.getElementById("registerName").value.trim();
  let password = document.getElementById("registerPassword").value.trim();

  if (fName === "" || lName === "" || login === "" || password === "") {
    document.getElementById("registerResult").innerHTML =
      "All fields are required";
    return;
  }

  let tmp = {
    firstName: fName,
    lastName: lName,
    login: login,
    password: password,
  };
  let jsonPayload = JSON.stringify(tmp);

  let url = "LAMPAPI/Register.php";

  let xhr = new XMLHttpRequest();
  xhr.open("POST", url, true);
  xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");
  xhr.onreadystatechange = function () {
    if (this.readyState === 4 && this.status === 200) {
      document.getElementById("registerResult").innerHTML =
        "Registration successful, please log in.";
    }
  };
  xhr.send(jsonPayload);
}

function toggleLogin() {
  let loginDiv = document.getElementById("loginDiv");
  let registerDiv = document.getElementById("registerDiv");

  if (loginDiv.style.display === "none") {
    loginDiv.style.display = "block";
    registerDiv.style.display = "none";
  } else {
    loginDiv.style.display = "none";
    registerDiv.style.display = "block";
  }
}

function addContact() {
  let fName = document.getElementById("contactFirstName").value.trim();
  let lName = document.getElementById("contactLastName").value.trim();
  let phone = document.getElementById("contactPhone").value.trim();
  let email = document.getElementById("contactEmail").value.trim();

  if (fName === "" || lName === "" || phone === "" || email === "") {
    document.getElementById("contactAddResult").innerHTML =
      "All fields are required";
    return;
  }
  
  if (!isValidEmail(email)){
    document.getElementById("contactAddResult").innerHTML =
      "Valid email is required";
      return;
  }

  let tmp = {
    firstName: fName,
    lastName: lName,
    phoneNumber: phone,
    emailAddress: email,
    userId: userId,
  };
  let jsonPayload = JSON.stringify(tmp);

  let url = "LAMPAPI/CreateContacts.php";

  let xhr = new XMLHttpRequest();
  xhr.open("POST", url, true);
  xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");
  xhr.onreadystatechange = function () {
    if (this.readyState === 4 && this.status === 200) {
      document.getElementById("contactAddResult").innerHTML =
        "Contact has been added";
    }
  };
  xhr.send(jsonPayload);
}

function searchContact() {
  let srch = document.getElementById("searchText").value.trim();

  let tmp = { search: srch, userId: userId };
  let jsonPayload = JSON.stringify(tmp);

  let url = "LAMPAPI/SearchContacts.php";

  let xhr = new XMLHttpRequest();
  xhr.open("POST", url, true);
  xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");
  xhr.onreadystatechange = function () {
    if (this.readyState === 4 && this.status === 200) {
      let jsonObject = JSON.parse(xhr.responseText);

      let contactList = document.getElementById("contactList");
      contactList.innerHTML = "";

      if (jsonObject.error !== "") {
        document.getElementById("contactSearchResult").innerHTML =
          jsonObject.error;
        return;
      }

      document.getElementById("contactSearchResult").innerHTML =
        jsonObject.results.length + " contact(s) found";

      jsonObject.results.forEach((contact) => {
        let row = document.createElement("div");
        row.className = "contact-row";

        let firstNameInput = document.createElement("input");
        firstNameInput.type = "text";
        firstNameInput.value = contact.FirstName;
        firstNameInput.readOnly = true;

        let lastNameInput = document.createElement("input");
        lastNameInput.type = "text";
        lastNameInput.value = contact.LastName;
        lastNameInput.readOnly = true;

        let phoneInput = document.createElement("input");
        phoneInput.type = "text";
        phoneInput.value = contact.Phone;
        phoneInput.readOnly = true;

        let emailInput = document.createElement("input");
        emailInput.type = "text";
        emailInput.value = contact.Email;
        emailInput.readOnly = true;

        row.appendChild(firstNameInput);
        row.appendChild(lastNameInput);
        row.appendChild(phoneInput);
        row.appendChild(emailInput);

        let editBtn = document.createElement("button");
        editBtn.textContent = "Edit";
        editBtn.onclick = function () {
          if (editBtn.textContent === "Edit") {
            [firstNameInput, lastNameInput, phoneInput, emailInput].forEach(
              (field) => (field.readOnly = false)
            );
            editBtn.textContent = "Save";
          } else {
            updateContact(
              contact.ID,
              firstNameInput.value,
              lastNameInput.value,
              phoneInput.value,
              emailInput.value
            );
            [firstNameInput, lastNameInput, phoneInput, emailInput].forEach(
              (field) => (field.readOnly = true)
            );
            editBtn.textContent = "Edit";
          }
        };
        row.appendChild(editBtn);

        let deleteBtn = document.createElement("button");
        deleteBtn.textContent = "Delete";
        deleteBtn.onclick = function () {
          if (confirm("Are you sure you want to delete this contact?")) {
            deleteContact(contact.ID);
            row.remove();
          }
        };
        row.appendChild(deleteBtn);

        contactList.appendChild(row);
      });
    }
  };
  xhr.send(jsonPayload);
}

function updateContact(id, firstName, lastName, phone, email) {
  let tmp = {
    id: id,
    firstName: firstName,
    lastName: lastName,
    phoneNumber: phone,
    emailAddress: email,
    userId: userId,
  };
  let jsonPayload = JSON.stringify(tmp);

  let url = "LAMPAPI/UpdateContacts.php";
  

  let xhr = new XMLHttpRequest();
  xhr.open("POST", url, true);
  xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");
  if (!isValidEmail(email)){
    document.getElementById("contactAddResult").innerHTML =
      "Valid email is required";
      return;
  }
  xhr.onreadystatechange = function () {
    if (this.readyState === 4 && this.status === 200) {
      console.log("Contact updated");
    }
  };
  xhr.send(jsonPayload);
}

function deleteContact(id) {
  let tmp = { id: id, userId: userId };
  let jsonPayload = JSON.stringify(tmp);

  let url = "LAMPAPI/DeleteContacts.php";

  let xhr = new XMLHttpRequest();
  xhr.open("POST", url, true);
  xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");
  xhr.onreadystatechange = function () {
    if (this.readyState === 4 && this.status === 200) {
      console.log("Contact deleted");
    }
  };
  xhr.send(jsonPayload);
}
