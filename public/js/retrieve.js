$(document).ready(function() {
    console.log("Document ready");
    const hash = window.location.hash;
    const secretKey = hash.substring(1);
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
                    const decryptedData = await decryptData(secretKey, data.secret);
                    $("#decryptedSecret").val(decryptedData);
                    $("#retrieveSecretContainer").hide();
                    $("#decryptedSecretContainer").show();
                    $("#copySecretButton").on("click", function() {
                        navigator.clipboard.writeText(decryptedData);
                        $("#copySecretButton").text("Copied!");
                    });
                } catch(error) {
                    console.error("Decryption failed:", error);
                    $("#decryptedSecret").val("Decryption failed. Please ensure you have the full and complete link.");
                    $("#retrieveSecretContainer").hide();
                    $("#decryptedSecretContainer").show();
                }
            },
            error: function($xhr) {
                $("#errors").text($xhr.responseJSON.error);
                $("#errors").show();
                //spinner.hide();
                //submitBtn.prop("disabled", false);
            }
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
});