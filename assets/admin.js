(function () {
  "use strict";
  const admin = document.querySelector(".pcp-admin");
  if (!admin) return;
  function tab(name) {
    const valid = [...admin.querySelectorAll("[data-tab]")].some(
      (b) => b.dataset.tab === name,
    );
    if (!valid) name = "general";
    admin.querySelectorAll("[data-panel]").forEach((panel) => {
      panel.hidden = panel.dataset.panel !== name;
    });
    admin.querySelectorAll("[data-tab]").forEach((button) => {
      button.setAttribute("aria-pressed", String(button.dataset.tab === name));
    });
    const submit = admin.querySelector("#pcp-settings .submit");
    if (submit)
      submit.hidden = ![
        ...admin.querySelectorAll("#pcp-settings [data-panel]"),
      ].some((panel) => panel.dataset.panel === name);
  }
  admin.querySelectorAll("[data-tab]").forEach((button) =>
    button.addEventListener("click", () => {
      tab(button.dataset.tab);
      history.replaceState(null, "", "#" + button.dataset.tab);
    }),
  );
  tab(location.hash.slice(1) || "general");
  const preview = admin.querySelector(".pcp-preview");
  const mapping = {
    accent: "--preview-accent",
    button_text: "--preview-text",
    radius: "--preview-radius",
    spacing: "--preview-gap",
    surface: "--preview-surface",
    background: "--preview-bg",
    text_color: "--preview-body",
    font: "font-family",
  };
  function update() {
    const mode = document.getElementById("pcp-container_mode");
    const width = document.getElementById("pcp-container_width");
    if (mode && width)
      width.closest(".pcp-field").hidden = mode.value !== "custom";
    Object.entries(mapping).forEach(([key, property]) => {
      const field = document.getElementById("pcp-" + key);
      if (field)
        preview.style.setProperty(
          property,
          field.value + (["radius", "spacing"].includes(key) ? "px" : ""),
        );
    });
    ["title", "subtitle"].forEach((key) => {
      const field = document.getElementById("pcp-" + key);
      if (field)
        document.getElementById("pcp-preview-" + key).textContent = field.value;
    });
  }
  admin.addEventListener("input", update);
  update();
  admin.querySelectorAll("form[data-confirm]").forEach((form) =>
    form.addEventListener("submit", (e) => {
      if (!confirm(form.dataset.confirm)) e.preventDefault();
    }),
  );
})();
