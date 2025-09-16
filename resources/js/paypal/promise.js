import { loadScript } from "@paypal/paypal-js";

loadScript({ clientId: "test" })
    .then((paypal) => {
        paypal
            .Buttons()
            .render("#your-container-element")
            .catch((error) => {
                console.error("failed to render the PayPal Buttons", error);
            });
    })
    .catch((error) => {
        console.error("failed to load the PayPal JS SDK script", error);
    });

export default 'promise.js'