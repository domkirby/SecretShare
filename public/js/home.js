$(document).ready(function() {
    console.log("Document ready");
    const windowHost = window.location.hostname;
    const form = $("#secretForm");
    form.on("submit", async function(e) {
        e.preventDefault();
        const key = await generateRandomKey();
        const plaintext = $("#secret").val();
        const encryptedData = await encryptData(key, plaintext);
        
        $("#encryptedSecret").val(encryptedData);
        var payload = form.serialize();
        console.log("encryptedData:", encryptedData);
        console.log("Payload:", payload);
        
        $.ajax({
            method: "POST",
            url: "/api/saveSecret",
            data: payload,
            success: function(data) {
                var secretUrl = `https://${windowHost}/secret/${data.secret_id}#${key}`;
                $("#secretLink").val(secretUrl);
                $("#secretLinkContainer").show();
                $("#createSecretContainer").hide();
                $("#copyLink").on("click", function() {
                    navigator.clipboard.writeText(secretUrl);
                    $("#copyLink").text("Copied!");
                });
            },
            error: function($xhr) {
                $("#errors").text($xhr.responseJSON.error);
                $("#errors").show();
            }
        });
    });
});