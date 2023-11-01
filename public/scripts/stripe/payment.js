/*TEST MODE*/ /// const stripe = Stripe("pk_test_51NchR1IjJ2ELyWfBNtY0qlpT8tfIly7jajA6YJBCGNDIw8Ym0BHCy64eju21ohxiDkZRWDGNmtr3xu1rfJtmjSZL00GrFe1HIl")
const stripe = Stripe("pk_live_51NchR1IjJ2ELyWfBjQTMz8XdY8gbkPkZ6HuqnrmdolfM7WaXPFiKGEQBl3xYp7mdIUu8hiWvyAs58Gjaz1WSRZ7X00m9n1lOFr")

const form = document.querySelector("#payment-form")
const emailAddress = form.dataset.email
var ride_id = (new URL(window.location).pathname.match(/[^\/]+/g)).find(part => !isNaN(parseFloat(part)))
var elements

initialize()

form.addEventListener("submit", handleSubmit)

// Fetches a payment intent and captures the client secret
async function initialize() {

    const { clientSecret } = await fetch("/api/stripe/tours/create-payment-intent.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(ride_id),
    }).then((r) => r.json())

    elements = stripe.elements({ clientSecret })

    const linkAuthenticationElement = elements.create("linkAuthentication")
    linkAuthenticationElement.mount("#link-authentication-element")

    var paymentElement = elements.create('payment', {
        fields: {
            billingDetails: {
                email: 'never'
            }
        }
    })
    paymentElement.mount("#payment-element")
}

async function handleSubmit(e) {
    e.preventDefault()
    setLoading(true)

    const { error } = await stripe.confirmPayment({
        elements,
        confirmParams: {
            return_url: "https://www.cyclingfriends.co/ride/" + ride_id + "/checkout",
            payment_method_data: {
                billing_details: {
                    email: emailAddress,
                }
            },
        },
    })

    // This point will only be reached if there is an immediate error when
    // confirming the payment. Otherwise, your customer will be redirected to
    // your `return_url`. For some payment methods like iDEAL, your customer will
    // be redirected to an intermediate site first to authorize the payment, then
    // redirected to the `return_url`.
    if (error.type === "card_error" || error.type === "validation_error") {
        showMessage(error.message)
    } else {
        showMessage("An unexpected error occurred.")
    }

    setLoading(false)
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

// Show a spinner on payment submission
function setLoading(isLoading) {
    if (isLoading) {
        // Disable the button and show a spinner
        document.querySelector("#submit").disabled = true
        document.querySelector("#spinner").classList.remove("hidden")
        document.querySelector("#button-text").classList.add("hidden")
    } else {
        document.querySelector("#submit").disabled = false
        document.querySelector("#spinner").classList.add("hidden")
        document.querySelector("#button-text").classList.remove("hidden")
    }
}