const menuItem = document.querySelector(".menu-item");
const megaMenu = document.querySelector(".mega-menu");
const buttons = document.querySelectorAll(".left-menu button");
const contents = document.querySelectorAll(".right-content");

// Hàm hiển thị menu
function showMegaMenu() {
  megaMenu.style.display = "flex";
}

// Hàm ẩn menu
function hideMegaMenu() {
  megaMenu.style.display = "none";
}

// Hiện menu khi hover vào menuItem hoặc megaMenu

  menuItem.addEventListener("mouseenter", showMegaMenu);
  megaMenu.addEventListener("mouseenter", showMegaMenu);

  menuItem.addEventListener("mouseleave", () => {
    setTimeout(() => {
      if (!menuItem.matches(":hover") && !megaMenu.matches(":hover")) {
        hideMegaMenu();
      }
    }, 100);
  });

  megaMenu.addEventListener("mouseleave", () => {
    setTimeout(() => {
      if (!menuItem.matches(":hover") && !megaMenu.matches(":hover")) {
        hideMegaMenu();
      }
    }, 100);
  });

// Hover từng nút bên trái -> đổi nội dung
buttons.forEach((btn) => {
  btn.addEventListener("mouseenter", () => {
    buttons.forEach((b) => b.classList.remove("active"));
    btn.classList.add("active");

    contents.forEach((content) => content.classList.remove("show"));
    const target = document.getElementById("content-" + btn.dataset.target);
    if (target) {
      target.classList.add("show");
    }
  });
});
