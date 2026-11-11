/* Portus enhances native WooCommerce cart events; it owns no pricing or cart endpoint. */
(function ($) {
  "use strict";
  let timer;
  let legacyCouponError = "";
  const root = () => document.querySelector(".pcp-cart");
  const legacyErrorId = "pcp-coupon-error";
  let pendingFocus = null;

  function syncQuantityControls() {
    document
      .querySelectorAll(".pcp-cart .pcp-quantity-control")
      .forEach((control) => {
        const input = control.querySelector("input.qty");
        const plus = control.querySelector('[data-step="1"]');
        const minus = control.querySelector('[data-step="-1"]');
        if (!input) {
          if (plus) plus.disabled = true;
          return;
        }
        const maximum =
          input.max !== "" && Number(input.max) >= 0
            ? Number(input.max)
            : Number(control.dataset.max);
        if (plus)
          plus.disabled =
            input.disabled ||
            input.readOnly ||
            input.type === "hidden" ||
            (maximum >= 0 && Number(input.value) >= maximum);
        if (minus) {
          minus.disabled = input.disabled;
          minus.dataset.quantityLabel ||= minus.getAttribute("aria-label");
          const remove = control
            .closest(".pcp-product-row")
            ?.querySelector(".pcp-remove .remove");
          if (Number(input.value) <= 1 && remove)
            minus.setAttribute("aria-label", remove.getAttribute("aria-label"));
          else minus.setAttribute("aria-label", minus.dataset.quantityLabel);
        }
      });
  }

  // CSS inline-size containment inflates intrinsic flex heights in some Elementor
  // cart widgets. Observe width instead; height remains entirely content-driven.
  function syncWidth(cart) {
    const layout = cart.querySelector(".pcp-layout");
    const width = (layout || cart).getBoundingClientRect().width;
    cart.classList.toggle("pcp-compact", width <= 960);
    cart.classList.toggle("pcp-narrow", width <= 520);
    cart.classList.toggle("pcp-tiny", width <= 350);
  }
  $(function () {
    const carts = document.querySelectorAll(".pcp-cart");
    carts.forEach(syncWidth);
    if (typeof ResizeObserver !== "undefined") {
      const observer = new ResizeObserver((entries) => {
        entries.forEach((entry) => syncWidth(entry.target));
      });
      carts.forEach((cart) => observer.observe(cart));
    } else {
      $(window).on("resize", () => carts.forEach(syncWidth));
    }
  });

  function clearLegacyCouponError() {
    legacyCouponError = "";
    const cart = root();
    cart?.querySelector(".pcp-coupon-error")?.remove();
    const input = cart?.querySelector("#coupon_code");
    if (!input) return;
    const described = (input.getAttribute("aria-describedby") || "")
      .split(/\s+/)
      .filter(Boolean);
    if (!described.includes(legacyErrorId)) return;
    const remaining = described.filter((id) => id !== legacyErrorId);
    if (remaining.length)
      input.setAttribute("aria-describedby", remaining.join(" "));
    else {
      input.removeAttribute("aria-describedby");
      input.removeAttribute("aria-invalid");
      input.classList.remove("has-error");
    }
  }

  function renderLegacyCouponError() {
    if (!legacyCouponError) return;
    const cart = root();
    const coupon = cart?.querySelector(".pcp-coupon");
    const input = coupon?.querySelector("#coupon_code");
    if (!coupon || !input) return;
    let notice = coupon.querySelector(".pcp-coupon-error");
    if (!notice) {
      notice = document.createElement("p");
      notice.id = legacyErrorId;
      notice.className = "coupon-error-notice pcp-coupon-error";
      notice.setAttribute("role", "alert");
      coupon.appendChild(notice);
    }
    notice.textContent = legacyCouponError;
    input.classList.add("has-error");
    input.setAttribute("aria-invalid", "true");
    const described = new Set(
      (input.getAttribute("aria-describedby") || "")
        .split(/\s+/)
        .filter(Boolean),
    );
    described.add(legacyErrorId);
    input.setAttribute("aria-describedby", [...described].join(" "));
  }

  // WooCommerce 9 prints coupon errors as page notices; newer releases already render them inline.
  $(document).ajaxSuccess(function (_event, _xhr, settings, response) {
    if (!root() || typeof response !== "string") return;
    let endpoint;
    try {
      endpoint = new URL(settings.url, window.location.href);
    } catch (_) {
      return;
    }
    if (endpoint.searchParams.get("wc-ajax") !== "apply_coupon") return;
    const parsed = new DOMParser().parseFromString(response, "text/html");
    const error = parsed.querySelector(".woocommerce-error, .is-error");
    if (!error) {
      clearLegacyCouponError();
      return;
    }
    if (
      root().querySelector(
        ".pcp-coupon .coupon-error-notice:not(.pcp-coupon-error)",
      )
    ) {
      clearLegacyCouponError();
      return;
    }
    legacyCouponError = error.textContent.trim();
    document
      .querySelectorAll(
        ".woocommerce-notices-wrapper .woocommerce-error, .woocommerce-notices-wrapper .is-error",
      )
      .forEach((notice) => {
        if (notice.textContent.trim() === legacyCouponError) notice.remove();
      });
    renderLegacyCouponError();
  });
  $(document).on("input", ".pcp-cart #coupon_code", clearLegacyCouponError);
  $(document).on("click", ".pcp-cart .pcp-remove .remove", function () {
    pendingFocus = { remove: true };
  });
  $(document).on("click", ".pcp-cart .pcp-quantity-step", function () {
    const input = this.closest(".pcp-quantity-control")?.querySelector(
      "input.qty",
    );
    const form = this.closest("form");
    if (
      this.disabled ||
      !input ||
      input.disabled ||
      $(form).hasClass("processing") ||
      $(form).data("blockUI.isBlocked")
    )
      return;
    if (Number(this.dataset.step) < 0 && Number(input.value) <= 1) {
      clearTimeout(timer);
      pendingFocus = { remove: true };
      this.closest(".pcp-product-row")
        ?.querySelector(".pcp-remove .remove")
        ?.click();
      return;
    }
    if (
      !input ||
      input.disabled ||
      input.readOnly ||
      $(input).closest("form").hasClass("processing")
    )
      return;
    const before = input.value;
    try {
      Number(this.dataset.step) > 0 ? input.stepUp() : input.stepDown();
    } catch (_) {
      return;
    }
    if (input.value !== before) {
      pendingFocus = { name: input.name, step: this.dataset.step };
      $(input).trigger("change");
    }
    syncQuantityControls();
  });
  function announce(message) {
    const status = root()?.querySelector(".pcp-status");
    if (status) status.textContent = message;
  }
  $(document).on(
    "change",
    ".pcp-cart .woocommerce-cart-form input.qty",
    function () {
      clearTimeout(timer);
      syncQuantityControls();
      if (document.activeElement === this)
        pendingFocus = { name: this.name, input: true };
      const form = this.closest("form");
      timer = setTimeout(function () {
        if (!form.isConnected || !form.reportValidity()) return;
        const button = form.querySelector('[name="update_cart"]');
        if (button && !$(form).data("blockUI.isBlocked")) {
          announce(portusCartPage.updating);
          $(button).prop("disabled", false).trigger("click");
        }
      }, 650);
    },
  );
  function syncMobile() {
    document.querySelectorAll(".pcp-cart").forEach(function (cart) {
      const total = cart.querySelector(
        ".cart_totals .order-total .woocommerce-Price-amount",
      );
      const target = cart.querySelector(".pcp-mobile-total");
      const bar = cart.querySelector(".pcp-mobile-bar");
      if (target && total) target.textContent = total.textContent;
      if (bar) bar.hidden = !total;
    });
  }
  $(document.body).on(
    "updated_wc_div updated_cart_totals wc_cart_emptied",
    function () {
      clearTimeout(timer);
      syncMobile();
      syncQuantityControls();
      renderLegacyCouponError();
      announce(portusCartPage.updated);
      if (pendingFocus) {
        const cart = root();
        const input = [...(cart?.querySelectorAll("input.qty") || [])].find(
          (element) => element.name === pendingFocus.name,
        );
        let target = pendingFocus.input
          ? input
          : input
              ?.closest(".pcp-quantity-control")
              ?.querySelector(`[data-step="${pendingFocus.step}"]`);
        if (!target || target.disabled)
          target =
            input
              ?.closest(".pcp-quantity-control")
              ?.querySelector('[data-step="-1"]') ||
            cart?.querySelector(
              ".pcp-empty .pcp-continue, .pcp-quantity-step:not(:disabled)",
            );
        target?.focus({ preventScroll: true });
        pendingFocus = null;
      }
    },
  );
  $(function () {
    syncMobile();
    syncQuantityControls();
  });
})(jQuery);
