(function () {
  document.querySelectorAll("[data-appt-station-picker]").forEach((select) => {
    select.addEventListener("change", () => {
      select.form?.submit();
    });
  });

  const form = document.getElementById("appt-booking-form");

  if (!form) {
    return;
  }

  const panel = document.getElementById("appt-new-vehicle-panel");
  const reloadLink = document.getElementById("appt-reload-slots");

  const toggle = form.querySelector('input[data-appt-toggle="new-vehicle"]');

  function syncNewVehiclePanel() {
    if (!panel || !toggle) {
      return;
    }

    const enabled = toggle.checked;
    panel.hidden = !enabled;

    form.querySelectorAll('[data-appt-field="vehicle"]').forEach((field) => {
      const wrapper = field.closest(".form-group, .field-group, .mb-3");

      if (wrapper) {
        wrapper.style.display = enabled ? "none" : "";
      }

      field.required = !enabled;
      field.disabled = enabled;
    });

    form.querySelectorAll("[data-appt-new-vehicle]").forEach((field) => {
      field.disabled = !enabled;
    });
  }

  function syncReloadLink() {
    if (!reloadLink) {
      return;
    }

    const url = new URL(reloadLink.href, window.location.origin);
    const stationField = form.querySelector('[data-appt-field="station"]');
    const dateField = form.querySelector('[data-appt-field="date"]');

    if (stationField?.value) {
      url.searchParams.set("station", stationField.value);
    }

    if (dateField?.value) {
      url.searchParams.set("prefill_date", dateField.value);
    }

    url.searchParams.set("open_booking", "1");
    reloadLink.href = url.toString();
  }

  toggle?.addEventListener("change", syncNewVehiclePanel);
  form.addEventListener("change", syncReloadLink);

  syncNewVehiclePanel();
  syncReloadLink();
})();
