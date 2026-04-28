// ================= MOBILE MENU =================
const menuBtn = document.getElementById("menuBtn");
const navLinks = document.getElementById("navLinks");

menuBtn.addEventListener("click", () => {
  navLinks.classList.toggle("open");

  const icon = menuBtn.querySelector("i");

  if (navLinks.classList.contains("open")) {
    icon.classList.remove("fa-bars");
    icon.classList.add("fa-xmark");
  } else {
    icon.classList.remove("fa-xmark");
    icon.classList.add("fa-bars");
  }
});

// Close mobile menu after clicking nav link
document.querySelectorAll(".nav-links a").forEach((link) => {
  link.addEventListener("click", () => {
    navLinks.classList.remove("open");

    const icon = menuBtn.querySelector("i");
    icon.classList.remove("fa-xmark");
    icon.classList.add("fa-bars");
  });
});

// ================= SCROLL REVEAL ANIMATION =================
const revealElements = document.querySelectorAll(".reveal");

const revealObserver = new IntersectionObserver(
  (entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.classList.add("active");
      }
    });
  },
  {
    threshold: 0.15,
  }
);

revealElements.forEach((element) => {
  revealObserver.observe(element);
});

// ================= ACTIVE NAV LINK ON SCROLL =================
const sections = document.querySelectorAll("section[id]");
const navItems = document.querySelectorAll(".nav-links a");

window.addEventListener("scroll", () => {
  let currentSection = "";

  sections.forEach((section) => {
    const sectionTop = section.offsetTop - 120;
    const sectionHeight = section.offsetHeight;

    if (window.scrollY >= sectionTop && window.scrollY < sectionTop + sectionHeight) {
      currentSection = section.getAttribute("id");
    }
  });

  navItems.forEach((item) => {
    item.classList.remove("active");

    if (item.getAttribute("href") === `#${currentSection}`) {
      item.classList.add("active");
    }
  });
});

// ================= CONTACT FORM FRONTEND ONLY =================
// ================= CONTACT FORM BACKEND =================
// ================= CONTACT FORM BACKEND =================
const contactForm = document.getElementById("contactForm");
const formStatus = document.getElementById("formStatus");

if (contactForm) {
  contactForm.addEventListener("submit", async (event) => {
    event.preventDefault();

    formStatus.textContent = "Sending message...";
    formStatus.className = "form-status";

    const formData = new FormData(contactForm);

    try {
      const response = await fetch("backend/controllers/contactController.php", {
        method: "POST",
        body: formData,
      });

      const result = await response.json();

      if (result.success) {
        formStatus.textContent = result.message;
        formStatus.classList.add("success");
        contactForm.reset();
      } else {
        formStatus.textContent = result.message;
        formStatus.classList.add("error");
      }
    } catch (error) {
      formStatus.textContent = "Unable to send message. Please try again.";
      formStatus.classList.add("error");
      console.error("Contact form error:", error);
    }
  });
}