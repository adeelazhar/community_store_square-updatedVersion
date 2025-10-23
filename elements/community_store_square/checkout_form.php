<?php defined('C5_EXECUTE') or die(_("Access Denied.")); ?>

<div style="background:#eee; padding:10px; margin-bottom:10px;">
    Square Payment Form Loaded
</div>

<?php
if (isset($vars) && is_array($vars)) {
    extract($vars);
}
?>

<!-- Load Square SDK -->
<?php if ($mode == 'live') { ?>
    <script src="https://web.squarecdn.com/v1/square.js"></script>
<?php } else { ?>
    <script src="https://sandbox.web.squarecdn.com/v1/square.js"></script>
<?php } ?>

<!-- ✅ Payment Form Markup -->
<div id="square-payment-wrapper" class="form-group" style="margin-top:15px; display:none;">
    <form id="payment-form">
        <div id="card-container" style="min-height:120px; margin-bottom:15px;"></div>
    </form>
    <div id="payment-status-container" style="display:none;"></div>
    <input type="hidden" id="card-nonce" name="nonce">
</div>

<script>
$(document).ready(function () {
    const appId = "<?= $publicAPIKey; ?>";
    const locationId = "<?= $locationKey; ?>";
    let initialized = false;

   
    async function initSquareCard() {
        if (initialized) return; // 👈 prevent multiple inits
        initialized = true;

        $("#square-payment-wrapper").show();
      
        try {
            const payments = window.Square.payments(appId, locationId);
            const card = await payments.card();
            await card.attach("#card-container");
         
            $("#card-container").css({
                border: "1px solid #ccc",
                padding: "10px",
                "border-radius": "8px",
                background: "#fafafa"
            });

            $(".store-btn-complete-order")
                .off("click.square")
                .on("click.square", async function (e) {
                    e.preventDefault();

                    try {
                        const tokenResult = await card.tokenize();
                        if (tokenResult.status === "OK") {
                            $("#card-nonce").val(tokenResult.token);
                            console.log("✅ Token generated:", tokenResult.token);
                            $("#store-checkout-form-group-payment").submit();
                        } else {
                            alert("❌ Tokenization failed: " + tokenResult.status);
                            console.error("Tokenization failed:", tokenResult);
                        }
                    } catch (err) {
                        console.error("❌ Error during tokenization:", err);
                    }
                });
        } catch (err) {
            console.error("❌ Failed to initialize Square form:", err);
        }
    }

    // Only init once when user selects Square
    $('input[name="payment-method"]').on("change", function () {
        if ($(this).val() === "community_store_square") {
            setTimeout(initSquareCard, 400);
        } else {
            $("#square-payment-wrapper").hide();
        }
    });

    // If Square already selected when page loads
    const active = $('input[name="payment-method"]:checked').val();
    if (active === "community_store_square") {
        setTimeout(initSquareCard, 600);
    }
});
</script>
