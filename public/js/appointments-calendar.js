(function () {
  const config = window.carmainAppointmentsCalendar;

  if (!config) {
    return;
  }

  const bookingModalEl = document.getElementById("bookingModal");

  function setFieldValue(id, value) {
    if (!id || value === undefined || value === null || value === "") {
      return;
    }

    const field = document.getElementById(id);

    if (!field) {
      return;
    }

    if (field.tomselect) {
      field.tomselect.setValue(value, true);
      return;
    }

    field.value = value;
    field.dispatchEvent(new Event("change", { bubbles: true }));
  }

  function refreshBookingAutocompletes() {
    if (!bookingModalEl) {
      return;
    }

    bookingModalEl.querySelectorAll('[data-ea-widget="ea-autocomplete"]').forEach((field) => {
      if (field.tomselect) {
        field.tomselect.sync();
      }
    });
  }

  function openBookingModal(options) {
    if (!bookingModalEl || typeof bootstrap === "undefined") {
      return;
    }

    if (options?.date) {
      setFieldValue(config.formDateId, options.date);
    }

    if (options?.time) {
      setFieldValue(config.formTimeId, options.time);
    }

    bootstrap.Modal.getOrCreateInstance(bookingModalEl).show();
  }

  if (bookingModalEl) {
    bookingModalEl.addEventListener("shown.bs.modal", refreshBookingAutocompletes);
  }

  document.querySelectorAll("[data-action='open-booking']").forEach((button) => {
    button.addEventListener("click", () => openBookingModal());
  });

  if (config.openBookingModal) {
    openBookingModal({ time: config.prefillTime || undefined });
  }

  const calendarEl = document.getElementById("appointments-calendar");

  if (!calendarEl || typeof FullCalendar === "undefined") {
    return;
  }

  const calendar = new FullCalendar.Calendar(calendarEl, {
    locale: config.locale === "uk" ? "uk" : "en",
    timeZone: "Europe/Kyiv",
    initialView: "timeGridWeek",
    initialDate: config.initialDate,
    height: "auto",
    slotDuration: `00:${String(Math.max(15, config.slotMinutes)).padStart(2, "0")}:00`,
    headerToolbar: {
      left: "prev,next today",
      center: "title",
      right: "dayGridMonth,timeGridWeek,timeGridDay"
    },
    buttonText: {
      today: config.locale === "uk" ? "Сьогодні" : "Today",
      month: config.locale === "uk" ? "Місяць" : "Month",
      week: config.locale === "uk" ? "Тиждень" : "Week",
      day: config.locale === "uk" ? "День" : "Day"
    },
    dayHeaderFormat: { weekday: "short", day: "numeric", month: "numeric" },
    slotLabelFormat: {
      hour: "numeric",
      minute: "2-digit",
      hour12: false
    },
    navLinks: true,
    nowIndicator: true,
    editable: true,
    eventStartEditable: true,
    eventDurationEditable: false,
    events: function (info, successCallback, failureCallback) {
      const url = new URL(config.eventsUrl, window.location.origin);
      url.searchParams.set("station", config.stationId);
      url.searchParams.set("start", info.startStr);
      url.searchParams.set("end", info.endStr);

      fetch(url.toString(), { credentials: "same-origin" })
        .then((response) => {
          if (!response.ok) {
            throw new Error("events");
          }

          return response.json();
        })
        .then(successCallback)
        .catch(() => failureCallback(new Error("events")));
    },
    eventDrop: function (info) {
      if (info.event.extendedProps.kind === "block") {
        info.revert();
        return;
      }

      const url = config.rescheduleUrlTemplate.replace("__ID__", info.event.id);

      fetch(url, {
        method: "POST",
        credentials: "same-origin",
        headers: {
          "Content-Type": "application/json",
          "X-CSRF-TOKEN": config.csrfToken
        },
        body: JSON.stringify({ start: info.event.start.toISOString() })
      })
        .then(async (response) => {
          if (!response.ok) {
            const payload = await response.json().catch(() => ({}));
            throw new Error(payload.message || "Не вдалося перенести запис.");
          }
        })
        .catch((error) => {
          window.alert(error.message || "Не вдалося перенести запис.");
          info.revert();
        });
    },
    dateClick: function (info) {
      const date = info.dateStr.slice(0, 10);
      let time = "";

      if (!info.allDay && info.dateStr.length > 10) {
        const clicked = info.date;
        const hours = String(clicked.getHours()).padStart(2, "0");
        const minutes = String(clicked.getMinutes()).padStart(2, "0");
        time = `${hours}:${minutes}`;
      }

      openBookingModal({ date, time });
    }
  });

  calendar.render();

  window.carmainOpenBookingModal = openBookingModal;
})();
