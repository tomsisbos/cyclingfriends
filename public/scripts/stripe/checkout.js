// This is your test publishable API key.
const stripe = Stripe("pk_test_51NchR1IjJ2ELyWfBNtY0qlpT8tfIly7jajA6YJBCGNDIw8Ym0BHCy64eju21ohxiDkZRWDGNmtr3xu1rfJtmjSZL00GrFe1HIl")
var ride_id = (new URL(window.location).pathname.match(/[^\/]+/g)).find(part => !isNaN(parseFloat(part)))

checkStatus()

// Fetches the payment intent status after payment submission
async function checkStatus() {
    
    const clientSecret = new URLSearchParams(window.location.search).get(
        "payment_intent_client_secret"
    )

    if (!clientSecret) {
        return
    }

    const { paymentIntent } = await stripe.retrievePaymentIntent(clientSecret)

    /*switch (paymentIntent.status) {
        case "succeeded":
            showMessage("決済手続きが完了しました！")
            break
        case "processing":
            showMessage("決済手続き中...")
            break
        case "requires_payment_method":
            showMessage("決済手続きが失敗しました。再度お試しください。")
            break
        default:
            showMessage("エラーが発生しました。")
            break
    }*/
}

// ------- UI helpers -------

function showMessage(messageText) {
    const messageContainer = document.querySelector("#payment-message")

    messageContainer.classList.remove("hidden")
    messageContainer.textContent = messageText

    setTimeout(function () {
        messageContainer.classList.add("hidden")
        messageContainer.textContent = ""
    }, 4000)
}