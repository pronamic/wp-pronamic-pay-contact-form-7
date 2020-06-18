"use strict";

document.addEventListener('wpcf7submit', function (event) {
  var detail = event.detail;

  if ('pronamic_pay_redirect' !== detail.status) {
    return;
  }

  window.location.href = detail.apiResponse.pronamic_pay_redirect_url;
});
//# sourceMappingURL=payment-form-processor.js.map