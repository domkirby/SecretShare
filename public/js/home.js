$(document).ready(function() {
    console.log("Document ready");
    const windowHost = window.location.hostname;
    const form = $("#secretForm");
    const spinner = $("#loading");
    const submitBtn = $("#submitButton");
    const passwordCheckbox = $("#useCustomPassword");
    const passwordInput = $("#customPassword");
    const customPasswordDiv = $("#customPasswordDiv");
    const togglePasswordButton = document.querySelector("#togglePassword");
    const togglePasswordInnerContent = document.querySelector("#togglePasswordInnerContent");
    const pwFieldQuery = document.querySelector("#customPassword");
    const generatePasswordButton = $("#generatePasswordButton");
    const shareButton = $("#shareLink");
    passwordCheckbox.on("change", function() {
        console.log("Checkbox changed");
        if(passwordCheckbox.is(":checked")) {
            customPasswordDiv.show();
            passwordInput.attr("required", true);
            passwordInput.attr('disabled', false);
        } else {
            customPasswordDiv.hide();
            passwordInput.val('');
            passwordInput.attr("required", false);
            passwordInput.attr('disabled', true);
        }
    });

    passwordInput.on("input", function() {  
        const password = passwordInput.val();
        const strength = checkPasswordStrength(password);
        const strengthText = $("#passwordStrength");
        strengthText.text(strength);
    });

    togglePasswordButton.addEventListener("click", function(e) {
        e.preventDefault();
        const type = pwFieldQuery.getAttribute("type") === "password" ? "text" : "password";
        pwFieldQuery.setAttribute("type", type);
        togglePasswordInnerContent.classList.toggle("bi-eye");
    });
    generatePasswordButton.on("click", async function(e) {
        e.preventDefault();
        password = '';
        if(useDicewareForPasswordGeneration) {
          password = await generateDicewarePassphrase();
        } else {
          password = await generateSecurePassword(16);
        }
        passwordInput.val(password);
        passwordInput.trigger("input");
        if(pwFieldQuery.getAttribute("type") === "password") {
            pwFieldQuery.setAttribute("type", "text");
            togglePasswordInnerContent.classList.toggle("bi-eye");
        }
    });

    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))

    form.on("submit", async function(e) {
        e.preventDefault();
        var isCustomPassword = false;
        const keysetPayload = {};
        var key = '';
        if(passwordCheckbox.is(":checked")) {
            const pbKeyset = await generatePBKDF2Key(passwordInput.val());
            keysetPayload.salt = pbKeyset.salt;
            keysetPayload.iterations = pbKeyset.iterations;
            keysetPayload.saltLength = pbKeyset.saltLength;
            key = pbKeyset.key;
            isCustomPassword = true;
        } else {
            key = await generateRandomKey();
        }
        const plaintext = $("#secret").val();
        submitBtn.prop("disabled", true);
        spinner.show();
        try {
            const encryptedData = await encryptData(key, plaintext);
            if(isCustomPassword) {
                keysetPayload.data = encryptedData;
                keysetPayload.customPassword = true;
                $("#encryptedSecret").val(JSON.stringify(keysetPayload));
            } else {
                keysetPayload.customPassword = false;
                keysetPayload.data = encryptedData;
                $("#encryptedSecret").val(JSON.stringify(keysetPayload));
            }
            
            var payload = form.serialize();
                $.ajax({
                    method: "POST",
                    url: "/api/saveSecret",
                    data: payload,
                    success: function(data) {
                        var secretUrl = '';
                        if(isCustomPassword) {
                            secretUrl = `https://${windowHost}/secret/${data.secret_id}`;
                        } else {
                            secretUrl = `https://${windowHost}/secret/${data.secret_id}#${key}`;
                        }
                        $("#secretLink").val(secretUrl);
                        $("#secretLinkContainer").show();
                        $("#createSecretContainer").hide();
                        $("#copyLink").on("click", function() {
                            navigator.clipboard.writeText(secretUrl);
                            $("#copyLink").text("Copied!");
                        });

                        const navigatorShareData = {
                          title: "Secure data is being shared with you",
                          text: "This one-time secret is being shared with you. Click the link to view the secret.",
                          url: secretUrl
                        };
                        shareButton.on("click", function() {
                          if(navigator.share) {
                            navigator.share(navigatorShareData);
                          } else {
                            alert('Your browser does not support this sharing method, please copy the link manually.');
                            console.error("Web Share API not supported");
                          }
                        });
                    },
                    error: function($xhr) {
                        $("#errors").text($xhr.responseJSON.error);
                        $("#errors").show();
                        submitBtn.prop("disabled", false);
                        spinner.hide();
                    }
            });
        } catch(error) {
            console.error("Error encrypting data:", error);
            $("#errors").text("Error encrypting data. Please refresh and retry.");
            $("#errors").show();
        }
    });
});


function checkPasswordStrength(password) {
    let strength = 0;
  
    // Length check
    if (password.length >= 8) {
      strength++;
    }
  
    // Lowercase, uppercase, numbers, special chars
    if (password.match(/[a-z]/)) {
      strength++;
    }
    if (password.match(/[A-Z]/)) {
      strength++;
    }
    if (password.match(/[0-9]/)) {
      strength++;
    }
    if (password.match(/[^a-zA-Z0-9]/)) {
      strength++;
    }
  
    // Strength rating
    switch (strength) {
      case 0:
        return "Missing";
      case 1:
        return "Not great";
      case 2:
        return "Weak";
      case 3:
        return "Okay";
      case 4:
        return "Good (if it's random)";
      case 5:
        return "Great (if it's random)!";
    }
  }

  function turnstileCallback(callback) {
    $("#submitButton").prop("disabled", false);
  }