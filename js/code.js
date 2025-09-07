const urlBase = '/LAMPAPI';
const extension = 'php';

let userId = 0;
let firstName = "";
let lastName = "";

// Register
function doRegister()
{
    let firstName = document.getElementById('firstName').value;
    let lastName = document.getElementById('lastName').value;
    let login = document.getElementById('registerName').value;
    let password = document.getElementById('registerPassword').value;

    document.getElementById("registerResult").innerHTML = "";

    let tmp = { login: login, password: password, firstName: firstName, lastName: lastName };
    let jsonPayload = JSON.stringify(tmp);

    let url = urlBase + '/Register.' + extension;

    let xhr = new XMLHttpRequest();
    xhr.open("POST", url, true);
    xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");

    try
    {
        xhr.onreadystatechange = function()
        {
            if (this.readyState == 4 && this.status == 200)
            {
                let jsonObject = JSON.parse(xhr.responseText);

                if (jsonObject.error && jsonObject.error !== "")
                {
                    document.getElementById("registerResult").innerHTML = jsonObject.error;
                    return;
                }

                document.getElementById("loginName").value = login;
                document.getElementById("loginPassword").value = password;
                doLogin();
            }
        };
        xhr.send(jsonPayload);
    }
    catch(err)
    {
        document.getElementById("registerResult").innerHTML = err.message;
    }
}

// Login
function doLogin()
{
    userId = 0;
    firstName = "";
    lastName = "";
    
    let login = document.getElementById("loginName").value;
    let password = document.getElementById("loginPassword").value;

    document.getElementById("loginResult").innerHTML = "";

    let tmp = { login: login, password: password };
    let jsonPayload = JSON.stringify(tmp);
    
    let url = urlBase + '/Login.' + extension;

    let xhr = new XMLHttpRequest();
    xhr.open("POST", url, true);
    xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");
    try
    {
        xhr.onreadystatechange = function() 
        {
            if (this.readyState == 4 && this.status == 200) 
            {
                let jsonObject = JSON.parse(xhr.responseText);
                userId = jsonObject.id;
    
                if (userId < 1)
                {               
                    document.getElementById("loginResult").innerHTML = "User/Password incorrect";
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
    catch(err)
    {
        document.getElementById("loginResult").innerHTML = err.message;
    }
}

// Cookie Helpers
function saveCookie()
{
    let minutes = 20;
    let date = new Date();
    date.setTime(date.getTime() + (minutes * 60 * 1000)); 
    document.cookie = "firstName=" + firstName + ",lastName=" + lastName + ",userId=" + userId + ";expires=" + date.toGMTString();
}

function readCookie()
{
    userId = -1;
    let data = document.cookie;
    let splits = data.split(",");
    for (var i = 0; i < splits.length; i++) 
    {
        let thisOne = splits[i].trim();
        let tokens = thisOne.split("=");
        if (tokens[0] == "firstName") firstName = tokens[1];
        else if (tokens[0] == "lastName") lastName = tokens[1];
        else if (tokens[0] == "userId") userId = parseInt(tokens[1].trim());
    }
    
    if (userId < 0)
    {
        window.location.href = "index.html";
    }
}

function doLogout()
{
    userId = 0;
    firstName = "";
    lastName = "";
    document.cookie = "firstName= ; expires = Thu, 01 Jan 1970 00:00:00 GMT";
    window.location.href = "index.html";
}

// Contacts CRUD
function addContact()
{
    let firstName = document.getElementById("contactFirstName").value;
    let lastName = document.getElementById("contactLastName").value;
    let phone = document.getElementById("contactPhone").value;
    let email = document.getElementById("contactEmail").value;
    document.getElementById("contactAddResult").innerHTML = "";

    let tmp = { firstName: firstName, lastName: lastName, phoneNumber: phone, emailAddress: email, userId: userId };
    let jsonPayload = JSON.stringify(tmp);

    let url = urlBase + '/CreateContacts.' + extension;
    
    let xhr = new XMLHttpRequest();
    xhr.open("POST", url, true);
    xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");
    try
    {
        xhr.onreadystatechange = function() 
        {
            if (this.readyState == 4 && this.status == 200) 
            {
                let jsonObject = JSON.parse(this.responseText);
                if (jsonObject.error && jsonObject.error !== "")
                {
                    document.getElementById("contactAddResult").innerHTML = "Error: " + jsonObject.error;
                    return;
                }
                document.getElementById("contactAddResult").innerHTML = "Contact added successfully!";
            }
        };
        xhr.send(jsonPayload);
    }
    catch(err)
    {
        document.getElementById("contactAddResult").innerHTML = err.message;
    }
}

function searchContact()
{
    let srch = document.getElementById("searchText").value;
    document.getElementById("contactSearchResult").innerHTML = "";
    
    let contactList = "";

    let tmp = { search: srch, userId: userId };
    let jsonPayload = JSON.stringify(tmp);

    let url = urlBase + '/SearchContacts.' + extension;
    
    let xhr = new XMLHttpRequest();
    xhr.open("POST", url, true);
    xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");
    try
    {
        xhr.onreadystatechange = function() 
        {
            if (this.readyState == 4 && this.status == 200) 
            {
                document.getElementById("contactSearchResult").innerHTML = "Contacts retrieved";
                let jsonObject = JSON.parse(xhr.responseText);
                
                for (let i = 0; i < jsonObject.results.length; i++)
                {
                    let c = jsonObject.results[i];
                    contactList += `${c.FirstName} ${c.LastName} - ${c.Phone} - ${c.Email}`;
                    contactList += ` <button onclick="deleteContact(${c.ID});">Delete</button>`;
                    contactList += ` <button onclick="updateContact(${c.ID}, '${c.FirstName}', '${c.LastName}', '${c.Phone}', '${c.Email}');">Edit</button>`;
                    contactList += "<br />";
                }

                
                document.getElementById("contactList").innerHTML = contactList;
            }
        };
        xhr.send(jsonPayload);
    }
    catch(err)
    {
        document.getElementById("contactSearchResult").innerHTML = err.message;
    }
}

function deleteContact(contactId)
{
    let tmp = { id: contactId, userId: userId };
    let jsonPayload = JSON.stringify(tmp);

    let url = urlBase + '/DeleteContacts.' + extension;

    let xhr = new XMLHttpRequest();
    xhr.open("POST", url, true);
    xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");
    try
    {
        xhr.onreadystatechange = function() 
        {
            if (this.readyState == 4 && this.status == 200) searchContact();
        };
        xhr.send(jsonPayload);
    }
    catch(err) { console.log(err.message); }
}

function updateContact(contactId, oldFirst, oldLast, oldPhone, oldEmail)
{
    let firstName = prompt("Update First Name:", oldFirst);
    let lastName = prompt("Update Last Name:", oldLast);
    let phone = prompt("Update Phone:", oldPhone);
    let email = prompt("Update Email:", oldEmail);

    if (firstName == null || lastName == null || phone == null || email == null) return;

    let tmp = { id: contactId, firstName: firstName, lastName: lastName, phoneNumber: phone, emailAddress: email, userId: userId };
    let jsonPayload = JSON.stringify(tmp);

    let url = urlBase + '/UpdateContacts.' + extension;

    let xhr = new XMLHttpRequest();
    xhr.open("POST", url, true);
    xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");
    try
    {
        xhr.onreadystatechange = function() 
        {
            if (this.readyState == 4 && this.status == 200) searchContact();
        };
        xhr.send(jsonPayload);
    }
    catch(err) { console.log(err.message); }
}

// Toggle Login/Register
function toggleLogin()
{
    let loginDiv = document.getElementById('loginDiv');
    let registerDiv = document.getElementById('registerDiv');
    
    if (loginDiv.style.display === 'none') {
        loginDiv.style.display = 'block';
        registerDiv.style.display = 'none';
    } else {
        loginDiv.style.display = 'none';
        registerDiv.style.display = 'block';
    }
}
