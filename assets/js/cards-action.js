// let HelpersPro = {
//   setColor: function (color, save = false) {
//     if (!color) return;
//     document.documentElement.style.setProperty('--bs-primary', color);
//   },

//   // Add this function
//   _toggleClass: function (el, class1, class2) {
//     if (!el) return;
//     if (el.classList.contains(class1)) {
//       el.classList.remove(class1);
//       el.classList.add(class2);
//     } else {
//       el.classList.remove(class2);
//       el.classList.add(class1);
//     }
//   }
// };


// Escape key closes fullscreen
document.addEventListener("keyup", function (e) {
  if (e.key === "Escape") {
    const fullCard = document.querySelector(".card-fullscreen");
    if (fullCard) {
      fullCard.classList.remove("card-fullscreen");
      const icon = fullCard.querySelector(".card-expand i") || fullCard.querySelector(".card-expand").firstElementChild;
      if (icon) {
        icon.classList.remove("ri-fullscreen-exit-line");
        icon.classList.add("ri-fullscreen-line");
      }
    }
  }
});



document.addEventListener("DOMContentLoaded", function () {

  // ================================
  // Card collapse
  // ================================
  document.querySelectorAll(".card-collapsible").forEach(header => {
    header.addEventListener("click", function (e) {
      e.preventDefault();
      const collapseEl = header.closest(".card").querySelector(".collapse");
      if (collapseEl) {
        new bootstrap.Collapse(collapseEl);
      }
      header.classList.toggle("collapsed");
      const icon = header.querySelector("i");
      if (icon) {
        icon.classList.toggle("ri-arrow-down-s-line");
        icon.classList.toggle("ri-arrow-up-s-line");
      }
    });
  });

  // ================================
  // Card fullscreen toggle
  // ================================
  document.querySelectorAll(".card-expand").forEach(btn => {
    btn.addEventListener("click", function (e) {
      e.preventDefault();
      const card = btn.closest(".card");
      const icon = btn.querySelector("i");
      if (card && icon) {
        card.classList.toggle("card-fullscreen");
        icon.classList.toggle("ri-fullscreen-line");
        icon.classList.toggle("ri-fullscreen-exit-line");
      }
    });
  });

  // ================================
  // Card close
  // ================================
  document.querySelectorAll(".card-close").forEach(btn => {
    btn.addEventListener("click", function (e) {
      e.preventDefault();
      const card = btn.closest(".card");
      if (card) card.classList.add("d-none");
    });
  });

  // ================================
  // Card reload with fetch
  // ================================
  const cardActions = Array.from(document.querySelectorAll(".card-reload"));

  cardActions.forEach(action => {
    action.addEventListener("click", async function (e) {
      e.preventDefault();

      const card = action.closest(".card");
      if (!card) return;

      const cardBody = card.querySelector(".card-body");
      const cardcontent = card.querySelector(".card-content");
      const alertDiv = card.querySelector(".card-alert");
      const fetchUrl = action.dataset.fetch;
      const id = action.dataset.id;
      const curData = cardcontent.innerHTML;

      if (!fetchUrl) {
        console.error("No fetch URL defined!");
        return;
      }

      // Loading overlay
      const loaderDiv = document.createElement("div");
      loaderDiv.className = "card-loader-overlay";
      loaderDiv.innerHTML = `<h5>Loading...</h5>`;
      Object.assign(loaderDiv.style, {
        position: "absolute",
        top: 0,
        left: 0,
        width: "100%",
        height: "100%",
        background: "rgba(255,255,255,0.7)",
        display: "flex",
        justifyContent: "center",
        alignItems: "center",
        zIndex: 9999
      });
      // card.style.position = "relative";
      card.appendChild(loaderDiv);

      try {
        const formData = new FormData();
        formData.append("id", id);
        formData.append("content", curData);

        const response = await fetch(fetchUrl, { method: "POST", body: formData });

        if (!response.ok) throw new Error("Network response was not ok");

        const data = await response.text(); // যদি JSON হয়: await response.json();

        // alert(data);
        if (cardcontent) cardcontent.innerHTML = data;

        if (alertDiv) {

          alertDiv.innerHTML = `
            <div class="alert alert-success alert-dismissible fade show" role="alert">
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              Content loaded successfully!
            </div>
          `;
        }

      } catch (err) {
        console.error(err);
        if (alertDiv) {
          alertDiv.innerHTML = `
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              Failed to load content!
            </div>
          `;
        }
      } finally {
        loaderDiv.remove();
      }
    });
  });

});