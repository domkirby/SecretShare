$(document).ready(async function() {
    console.log("Document ready");
    const secretUsesCustomPassword = await useCustomPassword();
    console.log('Uses custom password:', secretUsesCustomPassword);
    if(secretUsesCustomPassword) {
        enablePasswordField();
    }
    const spinner = $("#loading");
    const submitBtn = $("#submitButton");
    $("#retrieveSecretForm").on("submit", async function(e) {
        e.preventDefault();
        spinner.show();
        submitBtn.prop("disabled", true);
        var secretId = $("#secretId").val();
        var payload = {
            token: $("#csrfToken").val()
        };
        $.ajax({
            method: "POST",
            url: `/api/retrieveSecret/${secretId}`,
            data: payload,
            success: async function(data) {
                console.log("Data:", data);
                try {
                    const apiReturn = JSON.parse(data.secret);
                    var decryptedData = '';
                    if(secretUsesCustomPassword && apiReturn.customPassword) {
                        const keyset = await generatePBKDF2Key($("#password").val(), apiReturn.saltLength, apiReturn.iterations, apiReturn.salt);
                        console.log('Key Derived');
                        decryptionKey = keyset.key;
                        decryptedData = await decryptData(keyset.key, apiReturn.data);
                    } else if(secretUsesCustomPassword && !apiReturn.customPassword) {
                        $("#errors").text("The encryption key is missing from the link, and a custom password was not used. Please use a valid link. If this secret reached maximum views, it was destroyed.");
                        console.error("The encryption key is missing from the link, and a custom password was not used. Please use a valid link. If this secret reached maximum views, it was destroyed.");
                        $("button").prop("disabled", true);
                        throw new Error("The encryption key is missing from the link, and a custom password was not used. Please use a valid link. If this secret reached maximum views, it was destroyed.");
                    } else {
                        const hash = window.location.hash;
                        const secretKey = hash.substring(1);
                        decryptedData = await decryptData(secretKey, apiReturn.data);
                    }
                    $("#decryptedSecret").val(decryptedData);
                    $("#retrieveSecretContainer").hide();
                    $("#decryptedSecretContainer").show();
                    $("#copySecretButton").on("click", function() {
                        navigator.clipboard.writeText(decryptedData);
                        $("#copySecretButton").text("Copied!");
                    });
                } catch (error) {
                    console.error("Error decrypting data:", error);
                    $("#errors").text("An error occurred while decrypting the data. Please refresh and try again. If the secret reached maximum views, it was destroyed.");
                    $("#errors").show();
                    spinner.hide();
                    submitBtn.prop("disabled", true);
                }
            },
            error: function(xhr) {
                console.error("Error retrieving secret:", xhr.responseJSON);
                errorMsg = "An error occurred while retrieving the secret. Please refresh try again. If the secret reached maximum views, it was destroyed.";
                if(xhr.responseJSON.error) {
                    errorMsg = errorMsg = " (" + xhr.responseJSON.error + ")";
                }
                $("#errors").text(errorMsg);
                $("#errors").show();
                spinner.hide();
                submitBtn.prop("disabled", true);
            }
        });
    });
});

$("#deleteSecretBtn").on("click", function() {
    var secretId = $("#secretId").val();
    var payload = {
        token: $("#csrfToken").val()
    };
    if(confirm("Are you sure you want to delete this secret?")) {
        $.ajax({
            method: "POST",
            url: `/api/deleteSecret/${secretId}`,
            data: payload,
            success: function(data) {
                $("#secretDeleted").show();
                $("#deleteSecretBtn").hide();
            },
            error: function($xhr) {
                $("#errorsTwo").text($xhr.responseJSON.error);
                $("#errorsTwo").show();
            }
        });
     }

    
});

function enablePasswordField()
{
    $("#password").prop("disabled", false);
    $("#password").prop("required", true);
    $("#passwordDiv").show();
    $("#password").focus();
}

async function useCustomPassword()
{
    if(window.location.hash === '')
    {
        return true;
    }
    else {
        return false;
    }
}