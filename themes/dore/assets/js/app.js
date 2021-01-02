window.addEventListener("load", async (e) => {
  // Register Servie-worker
  if ("serviceWorker" in navigator) {
    navigator.serviceWorker
      .register(`${appPath}sw.js`)
      .then(function (swReg) {
        swRegistration = swReg;
        initializeUI();
      })
      .catch(function (error) {
        console.error("Service Worker Error", error);
      });
  }
});

function initializeUI() {
  if (notifyButton !== null) {
    notifyButton.addEventListener("click", function () {
      notifyButton.classList.add("disabled");
      console.log(isSubscribed);
      if (isSubscribed) {
        unsubscribeUser();
      } else {
        subscribeUser();
      }
    });
  }
  // Set the initial subscription value
  swRegistration.pushManager.getSubscription().then(function (subscription) {
    isSubscribed = !(subscription === null);
    //console.log(isSubscribed);
    if (isSubscribed) {
      //console.log('User IS subscribed.');
    } else {
      //console.log('User is NOT subscribed.');
    }
    if (notifyButton !== null) {
      updateBtn();
    }
  });
}

function unsubscribeUser() {
  swRegistration.pushManager
    .getSubscription()
    .then(function (subscription) {
      if (subscription) {
        return subscription.unsubscribe();
      }
    })
    .catch(function (error) {
      console.log("Error unsubscribing", error);
    })
    .then(function () {
      updateSubscriptionOnServer(null);

      console.log("User is unsubscribed.");
      isSubscribed = false;

      if (notifyButton !== null) {
        updateBtn();
      }
    });
}

function subscribeUser() {
  const applicationServerKey = urlB64ToUint8Array(applicationServerPublicKey);
  swRegistration.pushManager
    .subscribe({
      userVisibleOnly: true,
      applicationServerKey: applicationServerKey,
    })
    .then(function (subscription) {
      console.log("User is subscribed.");

      updateSubscriptionOnServer(subscription);

      isSubscribed = true;

      if (notifyButton !== null) {
        updateBtn();
      }
    })
    .catch(function (error) {
      console.error("Failed to subscribe the user: ", error);
      if (notifyButton !== null) {
        updateBtn();
      }
    });
}

function updateSubscriptionOnServer(subscription) {
  // TODO: Send subscription to application server
  if (subscription) {
    fetch(`${appPath}notification/action/subscribe/`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(subscription),
    }).then((response) => {
      response.json().then((result) => {
        if (result.status) {
          toastr.success(result.message);
        }
      });
    });
    localStorage.setItem("subscription", JSON.stringify(subscription));
  } else {
    fetch(`${appPath}notification/action/unsubscribe/`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        endpoint: "",
        expirationTime: null,
        keys: {
          auth: "",
          p256dh: "",
        },
      }),
    }).then((response) => {
      response.json().then((result) => {
        if (result.status) {
          toastr.error(result.message);
        }
      });
    });
    localStorage.removeItem("subscription");
  }
}

function urlB64ToUint8Array(base64String) {
  const padding = "=".repeat((4 - (base64String.length % 4)) % 4);
  const base64 = (base64String + padding)
    .replace(/\-/g, "+")
    .replace(/_/g, "/");

  const rawData = window.atob(base64);
  const outputArray = new Uint8Array(rawData.length);

  for (let i = 0; i < rawData.length; ++i) {
    outputArray[i] = rawData.charCodeAt(i);
  }
  return outputArray;
}

function updateBtn() {
  if (Notification.permission === "denied") {
    notifyButton.innerHTML =
      '<span><i class="fas fa-bell-slash"></i> Notification Blocked</span>';
    notifyButton.classList.add("disabled");
    updateSubscriptionOnServer(null);
    return;
  }
  if (isSubscribed) {
    notifyButton.innerHTML =
      '<span><i class="far fa-bell-slash"></i> Disable Notification</span>';
  } else {
    notifyButton.innerHTML =
      '<span><i class="far fa-bell"></i> Enable Notification</span>';
  }
  notifyButton.classList.remove("disabled");
}
const startLoader = () => {
  if (!$("body").hasClass("show-spinner")) {
    $("body").addClass("show-spinner");
    $("main").css("opacity", 0);
  }
};
const stopLoader = () => {
  if ($("body").hasClass("show-spinner")) {
    $("body").removeClass("show-spinner");
    $("main").css("opacity", 1);
  }
};
toastr.options = {
  positionClass: "toast-bottom-center",
  newestOnTop: false,
  timeOut: 0,
  extendedTimeout: 0,
};
var submitFormSelector = document.getElementById("validate-1");
if (submitFormSelector) {
  submitFormSelector.addEventListener("submit", (e) => {
    e.preventDefault();
    const formURL = submitFormSelector.getAttribute("action");
    var formData = new FormData(submitFormSelector);
    toastr.clear();
    toastr.info("Please wait...");
    fetch(formURL, {
      method: "POST",
      body: formData,
    })
      .then(function (response) {
        return response.json();
      })
      .then(function (result) {
        toastr.clear();
        if (result.status == true) {
          toastr.success(result.message, "", { timeOut: 1000 });
          if (result.redirect != "") {
            document.location = result.redirect;
          }
        } else {
          var message = result.error.replace("/[\n\r]/g", "");
          toastr.error(message, "", { timeOut: 2000 });
        }
      });
  });
}

var submitFormSelectors = document.getElementsByClassName("validate-form");
var i;
for (i = 0; i < submitFormSelectors.length; i++) {
  const submitFormSelector = submitFormSelectors[i];
  if (submitFormSelector) {
    submitFormSelector.addEventListener("submit", (e) => {
      e.preventDefault();
      const formURL = submitFormSelector.getAttribute("action");
      var formData = new FormData(submitFormSelector);
      toastr.clear();
      toastr.info("Please wait...");
      fetch(formURL, {
        method: "POST",
        body: formData,
      })
        .then(function (response) {
          return response.json();
        })
        .then(function (result) {
          console.log(result);
          toastr.clear();
          if (result.status == true) {
            toastr.success(result.message, "", { timeOut: 1000 });
            if (result.redirect != "") {
              document.location = result.redirect;
            }
          } else {
            var message = result.error.replace("/[\n\r]/g", "");
            toastr.error(message, "", { timeOut: 2000 });
          }
        });
    });
  }
}

/* Upload Image */
const uploadFormSelector = document.getElementById("upload_image");
if (uploadFormSelector) {
  uploadFormSelector.addEventListener("submit", (e) => {
    e.preventDefault();
    const formURL = uploadFormSelector.getAttribute("action");
    var formData = new FormData(uploadFormSelector);
    toastr.clear();
    toastr.info("Please wait...");
    fetch(formURL, {
      method: "POST",
      body: formData,
    })
      .then(function (response) {
        return response.json();
      })
      .then(function (result) {
        toastr.clear();
        if (result.status == true) {
          toastr.success(result.message);
          document.location = result.redirect;
        } else {
          var message = result.error.replace("/[\n\r]/g", "");
          toastr.error(message);
        }
      });
  });
}

async function fetchPage(url = defaultPage) {
  try {
    const response = await fetch(url);
    const json = await response.json();
    if (json.status == true) {
      outputDiv.innerHTML = json.message;
      loader.style.display = "none";
    } else if (json.status == false) {
    }
  } catch (error) {
    outputDiv.innerHTML = "No internet connection";
    loader.style.display = "none";
  }
}

/* JS Confirmation dialog */
function show_confirm(msg, url) {
  var k = confirm(msg);
  if (k) {
    //toastr.success ('Action completed successfully');
    document.location = url;
  }
}

function show_confirm_ajax(msg, url, redirect) {
  var k = confirm(msg);
  if (k) {
    fetch(url, {
      method: "POST",
    })
      .then(function (response) {
        toastr.info("Please wait...");
        return response.json();
      })
      .then(function (result) {
        if (result.status == true) {
          toastr.success(result.message);
          document.location = redirect;
        } else {
          var message = result.error.replace("/[\n\r]/g", "");
          toastr.error(message);
        }
      });
  }
}

/*----==== Logout User ====----*/
function logout_user() {
  // Logout URL
  const logoutURL = appPath + logoutPath;

  // Clear Local Storage
  if (typeof Storage !== "undefined") {
    localStorage.clear();
  } else {
    setCookie("user_token", "", "-1");
  }

  // Clear Server Session
  fetch(logoutURL, {
    method: "POST",
  })
    .then(function (response) {
      return response.json();
    })
    .then(function (result) {
      document.location = result.redirect;
    });
}

function update_session(user_token) {
  const updateURL = appPath + updatePath + "/" + user_token;
  fetch(updateURL, {
    method: "POST",
  })
    .then(function (response) {
      return response.json();
    })
    .then(function (result) {
      document.location = result.redirect;
    });
}

/*
 * Interactive App install button
 */
let deferredPrompt;
window.addEventListener("beforeinstallprompt", (event) => {
  // Prevent Chrome 67 and earlier from automatically showing the prompt
  event.preventDefault();

  // Stash the event so it can be triggered later.
  deferredPrompt = event;

  // Update UI notify the user they can add to home screen
  const installBannerSelector = document.querySelector("#installBanner");
  if (installBannerSelector) {
    installBannerSelector.style.visibility = "visible";
    // Attach the install prompt to a user gesture
    document.querySelector("#installBtn").addEventListener("click", (event) => {
      // Show the prompt
      deferredPrompt.prompt();

      // Wait for the user to respond to the prompt
      deferredPrompt.userChoice.then((choiceResult) => {
        if (choiceResult.outcome === "accepted") {
          // Update UI notify the user they can add to home screen
          document.querySelector("#installBanner").style.visibility = "hidden";
        } else {
          console.log("User dismissed the A2HS prompt");
        }
        deferredPrompt = null;
      });
    });
  }
});

// Check if app was successfully installed
window.addEventListener("appinstalled", (evt) => {
  app.logEvent("a2hs", "installed");
  document.querySelector("#installBanner").style.visibility = "hidden";
  //document.querySelector('#installBanner').style.display = 'none';
  $("#installBanner").hide();
});
$("main#content").css({
  "padding-top": `${$("header").height()}px`,
  "padding-bottom": `${$("footer").height()}px`,
});
/* ===== Side Menu Toggler ===== */
$("#toggle_sidebar").on("click", function (e) {
  e.stopPropagation();
  $("#sidebar").toggleClass("sidebar-open");
});
$("#toggle_sidebar_left").on("click", function (e) {
  e.stopPropagation();
  $("#sidebar-left").toggleClass("sidebar-open");
});
$("#toggle_sidebar_right").on("click", function (e) {
  e.stopPropagation();
  $("#sidebar-right").toggleClass("sidebar-open");
});
$("html, body").click(function (e) {
  if ($("#sidebar").hasClass("sidebar-open")) {
    $("#sidebar").removeClass("sidebar-open");
  }
  if ($("#sidebar-left").hasClass("sidebar-open")) {
    $("#sidebar-left").removeClass("sidebar-open");
  }
  if ($("#sidebar-right").hasClass("sidebar-open")) {
    $("#sidebar-right").removeClass("sidebar-open");
  }
});

/*=========== Check All ===========*/
function check_all() {
  $(".checks").prop("checked", $("#checkAll").prop("checked"));
}

/* ===== Tooltips Init ===== */
$(function () {
  $('[data-toggle="tooltip"]').tooltip();
});

/* ===== Password  ===== */
$(document).ready(function () {
  $(".custom-file-input").on("change", (e) => {
    var thisElement = $(e.target);
    var files = thisElement.prop("files");
    var file = files[0];
    var fileName = file.name;
    thisElement.next(".custom-file-label").text(fileName);
  });
  $("#password").on("keyup", function () {
    checkStrength(this.value);
    matchPassword($("#conf_password").val());
  });
  $("#conf_password").on("keyup", function () {
    matchPassword(this.value);
  });
  //If confirm_password didn't match.
  function matchPassword(conf_password) {
    if (
      conf_password === $("#password").val() &&
      $("#conf_password").val().length != 0
    ) {
      $("#re_pass").removeClass();
      $("#re_pass").addClass("fa fa-check text-success");
    } else {
      $("#re_pass").removeClass();
      $("#re_pass").addClass("fa fa-exclamation-triangle  text-danger");
    }
  }
  function checkStrength(password) {
    var strength = 0;
    //If password contains both lower and uppercase characters, increase strength value.
    if (password.match(/([a-z].*[A-Z])|([A-Z].*[a-z])/)) {
      strength += 1;
      $("#letter").removeClass();
      $("#letter").addClass("fa fa-check text-success");
    } else {
      $("#letter").removeClass();
      $("#letter").addClass("fa fa-exclamation-triangle  text-danger");
    }
    //If it has numbers and characters, increase strength value.
    if (password.match(/([a-zA-Z])/) && password.match(/([0-9])/)) {
      strength += 1;
      $("#number").removeClass();
      $("#number").addClass("fa fa-check text-success");
    } else {
      $("#number").removeClass();
      $("#number").addClass("fa fa-exclamation-triangle  text-danger");
    }
    //If it has one special character, increase strength value.
    if (password.match(/([!,%,&,@,#,$,^,*,?,_,~])/)) {
      strength += 1;
      $("#spcl_char").removeClass();
      $("#spcl_char").addClass("fa fa-check text-success");
    } else {
      $("#spcl_char").removeClass();
      $("#spcl_char").addClass("fa fa-exclamation-triangle  text-danger");
    }
    if (password.length > 7) {
      strength += 1;
      $("#length").removeClass();
      $("#length").addClass("fa fa-check text-success");
    } else {
      $("#length").removeClass();
      $("#length").addClass("fa fa-exclamation-triangle  text-danger");
    }
    // If value is less than 2
    if (strength < 2) {
      $("#password-strength").removeClass();
      $("#password-strength").addClass("progress-bar bg-danger");
      $("#password-strength").css("width", "30%");
      $("#password-strength").html("Very Weak");
      $("input[type=submit]").attr("disabled", true);
    } else if (strength == 2) {
      $("#password-strength").removeClass();
      $("#password-strength").addClass("progress-bar bg-warning");
      $("#password-strength").css("width", "60%");
      $("#password-strength").html("Week");
      $("input[type=submit]").attr("disabled", true);
    } else if (strength == 4) {
      $("#password-strength").removeClass();
      $("#password-strength").addClass("progress-bar bg-success");
      $("#password-strength").css("width", "100%");
      $("#password-strength").html("Strong");
      $("input[type=submit]").attr("disabled", false);
    }
  }
});
/*
 * Cookie Functions
 *
 * @setCookie Create a new cookie, Change or Delete cookie
 * @getCookie Get the value of a cookie
 * @checkCookie To check if a cookie is set
 *
 */
function setCookie(cname, cvalue, exdays) {
  var d = new Date();
  d.setTime(d.getTime() + exdays * 24 * 60 * 60 * 1000);
  var expires = "expires=" + d.toUTCString();
  document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}

function getCookie(cname) {
  var name = cname + "=";
  var decodedCookie = decodeURIComponent(document.cookie);
  var ca = decodedCookie.split(";");
  for (var i = 0; i < ca.length; i++) {
    var c = ca[i];
    while (c.charAt(0) == " ") {
      c = c.substring(1);
    }
    if (c.indexOf(name) == 0) {
      return c.substring(name.length, c.length);
    }
  }
  return "";
}

function checkCookie(cname) {
  var name = getCookie(cname);
  if (name != "") {
    return true;
  } else {
    return false;
  }
}

function copy_text(selector) {
  var copySelector = document.getElementById(selector);
  copySelector.select();
  copySelector.setSelectionRange(0, 99999);
  document.execCommand("copy");
  toastr.success("Copied", "", { timeOut: 1000 });
}
