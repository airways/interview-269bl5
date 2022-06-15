/**
 * Show a Bootstrap alert at the top of the screen. Calling this function replaces any existing alert in
 * the relevant container
 *
 * @param {string} message
 * @param {string} type - The Bootstrap alert type to display
 */
window.showAlert = (message, type) => {
    // for alert types, see https://getbootstrap.com/docs/5.1/components/alerts/#examples
    const alertContainer = document.getElementById('alert-container');

    if (alertContainer) {
        const wrapper = document.createElement('div')
        wrapper.innerHTML = `
        <div class="alert alert-${type} alert-dismissible show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>`;

        alertContainer.replaceChildren(wrapper);
    }
};

/**
 * After page completes loading, bind UI handlers and finalize UI state for the reports page.
 *
 * @param {Event} event
 */
window.onload = function() {
    // Send reminder email buttons are disabled initially so the user doesn't click
    // them before we can bind the event handler here. Enable the buttons and bind the
    // events.
    const buttons = document.getElementsByClassName('send-reminder-button');
    for(var i = 0, buttons_length = buttons.length; i < buttons_length; ++i) { // Prevent repeated length evaluation
        buttons[i].removeAttribute('disabled');
        buttons[i].onclick = sendReminderEmail;
    }
}

/**
 * Send a request to the server for a reminder email to be transmitted to the contact.
 *
 * @param {MouseEvent} event
 */
function sendReminderEmail(event) {
    const invoiceId = event.target.getAttribute('data-invoice_id');
    if(invoiceId) {
        fetch('/reports/sendReminder/'+invoiceId, {
                method: 'POST',
            })
            .then(function(response) {
                if(!response.ok) {
                    window.showAlert('Reminder sent. Internal server error. If this condition persists please contact support.', 'danger');
                } else {
                    return response.json();
                }
            })
            .then(function(data) {
                if(data.error) {
                    window.showAlert('Reminder not sent. ' + data.message, 'danger');
                } else {
                    window.showAlert('Reminder sent.', 'success');
                }
            }).catch(function(error) {
                window.showAlert('Reminder not sent. You may be offline or there may be an internal server error, please try again later. If this condition persists please contact support.', 'danger');
            })
    } else {
        window.showAlert('Reminder not sent. Internal client error. If this condition persists please contact support.', 'danger');
    }
}
